<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \Exception;
use HoltBosse\Form\{Input, Form, Field};

class ContentSearch {
	// TODO
	// make sure that filters cols are added to list_fields ? only relevant for code/admin backend views
	// 
	// $order_by="id", $type_filter=false, $id=null, $tag=null, $published_only=null, $list_fields=[], 
	// $ignore_fields=[], $filter_field=null, $filter_val=null, $page=0, $search="", $custom_pagination_size=null
	public $order_by;
	public $order_direction;
	public $type_filter;
	public $published_only;
	public $disable_builtin_state_check;
	public $list_fields;
	public $ignore_fields;
	public $created_by_cur_user;
	public $page;
	public $searchtext;
	public $fetch_all; // boolean - if list_fields not set, get all not just 'list' items from json
	public $page_size;
	public $custom_field_name;
	public $category; // category id to match
	public $tags; // array of tag ids to match 
	public $filters; // array of assoc arrays where 0=colname and 1=value to match e.g. [['note','test']] 
	private $count; // set after query is exec() shows total potential row count for paginated calls
	private $filter_pdo_params;
	private $custom_search_params;

	public function __construct() {
		$this->order_by = "id";
		$this->order_direction = "DESC";
		$this->type_filter = 1;
		$this->published_only = false;
		$this->disable_builtin_state_check = false;
		$this->page=1;
		$this->searchtext="";
		$this->ignore_fields=[];
		$this->list_fields=[];
		$this->count=0;
		$this->filters=[];
		$this->fetch_all = false;
		$this->category = null;
		$this->tags=[];
		$this->filter_pdo_params = [];
		$this->custom_search_params = [];
		$this->created_by_cur_user = false; // restrict to created by currently logged in user. 
		$this->page_size=Configuration::get_configuration_value ('general_options', 'pagination_size'); // default to system default
	}

	public function get_count() {
		return $this->count;
	}

	public function exec() {
		// Create and run query based on criteria in object properties
		// Return DB fetchAll array
		// Set $this->count to number of rows returned WITHOUT LIMITS IN PACE
		
		if ($this->type_filter) {
			// get list fields from custom_fields.json file
			if (!is_numeric($this->type_filter)) {
				$this->type_filter= Content::get_content_type_id($this->type_filter);
				if (!$this->type_filter) {
					throw new Exception('Unable to determine content type');
				}
			}
			$location = Content::get_content_location($this->type_filter);
            $custom_fields = JSON::load_obj_from_file(Content::getContentControllerPath($location) . '/custom_fields.json');
			$table_name = "controller_" . $custom_fields->id ;
			if (!$this->list_fields || $this->fetch_all) {
				// no fields request, so see if we need to get all or just get list items from json
				if ($this->fetch_all) {
					// get all saveable, not explicitly ignored fields
					foreach ($custom_fields->fields as $custom_field) {
						/** @var Field $custom_field */
						if (!in_array($custom_field->name,$this->ignore_fields)) {
							if (isset($custom_field->save)) {
								if ($custom_field->save===true) {
									$this->list_fields[] = $custom_field->name;
								}
							}
							else {
								// assume saveable, add to query list
								$this->list_fields[] = $custom_field->name;
							} 
						}
					}
				}
				else {
					// don't want all, but....
					// mimimally get all fields in 'list' property in custom_fields
					// checking it's not ignored and is an actual saveable field
					if (property_exists($custom_fields,'list')) {
						foreach ($custom_fields->list as $list_name) {
							if (!in_array($custom_field->name,$this->ignore_fields)) {
								// check if field is explicitly saveable or no saveable option set
								foreach ($custom_fields->fields as $custom_field) {
									if ($custom_field->name==$list_name) {
										if (isset($custom_field->save)) {
											if ($custom_field->save===true) {
												$this->list_fields[] = $custom_field->name;
											}
										}
										else {
											// assume saveable, add to query list
											$this->list_fields[] = $custom_field->name;
										} 
									}
								}
							}
						}
					} 
				}
			}
		} 
		else {
			throw new Exception('No content type filter provided for content search');
		}
		$query = "select";
		$select = " c.id, c.state, c.content_type, c.title, c.alias, c.ordering, c.start, c.end, c.created_by, c.updated_by, c.note, c.category, cat.title  as catname";
		if ($this->list_fields) {
			foreach ($this->list_fields as $field) {
				//$select .= " ,f_{$field}_t.content as f_{$field}";
				$select .= " ,c.{$field} as `{$field}`"; 
			}
		}
		$count_select = " count(*) as c ";

		$select = Hook::execute_hook_filters('custom_content_search_select', $select, $this->type_filter); 

		$from = " from ( `" . $table_name . "` c ";

		// if custom field exists as filter - needs to be added in from/where not as left join
		// also save filter value to filter_pdo_params
		foreach ($this->list_fields as $field) {

			if (array_key_exists($field, $this->filters ?? [])) {
				$this->filter_pdo_params[] = $this->filters[$field];
				//$from .= ", content_fields f_{$field}_t "; // no longer needs all in one table now
			}
		}

		$from .= " ) left join categories cat on c.category=cat.id ";

		$from = Hook::execute_hook_filters('custom_content_search_from', $from, $this->type_filter); 

		// left join custom field fields
		// ONLY where not in filters

		// no longer needed in flag tables
		/* if ($this->list_fields) {	
			foreach ($this->list_fields as $field) {
				if (!array_key_exists($field, $this->filters)) {
					$from .= " left join content_fields f_{$field}_t on f_{$field}_t.content_id=c.id and f_{$field}_t.name='{$field}' ";	
				}
			}
		} */

		$where = ' where 1=1 ';

		if ($this->published_only) {
			$where .= " AND c.state > 0 ";
		}
		elseif($this->disable_builtin_state_check==false) {
			$where .= " AND c.state >= 0 ";
		}

		if ($this->searchtext) {
			$where .= " AND (c.title like ? or c.note like ?) "; 
		}

		if ($this->tags) {
			// check array of ints
			// guaranteed to be arr of ints in core, but not in the wild...
			$tags_ok = true;
			foreach ($this->tags as $t) {
				if (!is_numeric($t)) {
					$tags_ok = false;
					break;
				}
			}
			if ($tags_ok) {
				// safe to implode without param injection
				$where .= " and c.id in (select content_id from tagged where tag_id in (" . implode(',', $this->tags) . ") and content_type_id=?) ";
				$this->filter_pdo_params[] = $this->type_filter;
			}
		}

		// custom fields being filtered
		// no longer care - treat all fields the same
		/* if ($this->list_fields) {
			foreach ($this->list_fields as $field) {			
				if (array_key_exists('f_' . $field, $this->filters)) {
					//CMS::pprint_r ('Got filter for custom field ' . $field);
					$this->filter_pdo_params[] = $this->filters['f_'.$field];
					/* $where .= " and f_{$field}_t.content_id=c.id and f_{$field}_t.name='{$field}' ";	
					$where .= " and f_{$field}_t.content = ? "; 
				}
			}
		} */

		// required fields filter
		foreach ($this->filters as $key => $value) {
			// old
			/* if (strpos($key,'f_')===false) {
				// not custom field
				// check if core field (nb - content type handled elsewhere in class, as more common)
				if (in_array($key,['state','id','alias','title','category','created_by','created','updated_by','updated','note','start','end'])) {
					// add value to params for safety
					$this->filter_pdo_params[] = $value;
					$where .= " and c." . $key . " = ? " ;
				}
			} */
			// new flat table
			$this->filter_pdo_params[] = $value;
			$where .= " and c." . $key . " = ? " ;
		}

		if ($this->category && is_numeric($this->category)) {
			$where .= " AND c.category=" . $this->category . " "; // safe to inject - checked for number
		}

		if ($this->created_by_cur_user) {
			$where .= " AND c.created_by=" . CMS::Instance()->user->id . " "; // safe to inject - will be int 100%
		}

		$where = Hook::execute_hook_filters('custom_content_search_where', $where, $this->type_filter); 
		
		$this->custom_search_params = Hook::execute_hook_filters('custom_content_search_params', $this->custom_search_params, $this->type_filter); 

		$count_query = $query . $count_select . $from . $where;
		$query = $query . $select . $from . $where;

		
		

		if ($this->order_by) {
			$query .= " order by `" . $this->order_by . "` " . $this->order_direction;
		}
		if ($this->page) {
			if (is_numeric($this->page_size) && is_numeric($this->page)) {
				$query .= " LIMIT " . (($this->page-1)*$this->page_size) . "," . $this->page_size;
			}
		}
		/* CMS::pprint_r ($this->filters);*/
		//CMS::pprint_r ($this->filter_pdo_params);
		//CMS::pprint_r ($query); die(); 

		/* echo "<p>custom search param</p>";
		CMS::pprint_r ($this->custom_search_params);
		echo "<p>filters</p>";
		CMS::pprint_r ($this->filters);
		echo "<p>list fields</p>";
		CMS::pprint_r ($this->list_fields);
		echo "<p>query</p>";
		CMS::pprint_r ($query); die();  */
		

		if ($this->searchtext) {
			$like = '%'.$this->searchtext.'%';
			$result = DB::fetchAll($query,array_merge([$like,$like], $this->filter_pdo_params ?? [], $this->custom_search_params ?? [])); // title and note
			// set count
			$this->count = DB::fetch($count_query,array_merge([$like,$like], $this->filter_pdo_params ?? [], $this->custom_search_params ?? []))->c ?? 0;
		}
		else {
			$result = DB::fetchAll($query, array_merge($this->filter_pdo_params ?? [], $this->custom_search_params ?? []) );
			// set count
			$this->count = DB::fetch($count_query, array_merge($this->filter_pdo_params ?? [], $this->custom_search_params ?? []) )->c ?? 0;
		}
		return $result;
	}
}