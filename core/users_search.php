<?php

defined('CMSPATH') or die; // prevent unauthorized access

class Users_Search {
	public $order_by;
	public $order_direction;
	public $published_only;
	public $disable_builtin_state_check;
	public $list_fields;
	public $ignore_fields;
	public $page;
	public $searchtext;
	public $page_size;
	public $created_by_cur_user;
	public $tags; // array of tag ids to match 
	public $groups; // array of group_ids to match
	public $filters; // array of assoc arrays where 0=colname and 1=value to match e.g. [['note','test']] - note custom fields need f_ prefix
	private $count; // set after query is exec() shows total potential row count for paginated calls
	private $filter_pdo_params;
	private $custom_search_params;

	public function __construct() {
		$this->order_by = "id";
		$this->order_direction = "DESC";
		$this->published_only = false;
		$this->disable_builtin_state_check = false;
		$this->page=1;
		$this->searchtext="";
		$this->ignore_fields=[];
		$this->list_fields=[];
		$this->count=0;
		$this->filters=[];
		$this->tags=[];
		$this->groups=[];
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
		
		
		$query = "select";
		//$select = " u.id, u.state, u.username, u.email, group_concat(DISTINCT g.display) as `groups`, group_concat(DISTINCT t.title) as tags ";
		$select = " u.id, u.state, u.username, u.email ";
		if ($this->list_fields) {
			foreach ($this->list_fields as $field) {
				$select .= " ,f_{$field}_t.content as f_{$field}";
			}
		}
		$count_select = " count(u.id) as c ";

		$select = Hook::execute_hook_filters('custom_user_search_select', $select);

		$from = " from ( users u ";

		// if custom user field exists as filter - needs to be added in from/where not as left join
		// also save filter value to filter_pdo_params
		foreach ($this->list_fields as $field) {

			if (array_key_exists($field, $this->filters)) {
				$this->filter_pdo_params[] = $this->filters[$field];
				$from .= ", user_fields f_{$field}_t ";
			}
		}

		$from .= " )  ";

		// left join content_type-2 = users
		/* $from .= "Left Join user_groups ug on ug.user_id = u.id 
		Left Join `groups` g on ug.group_id = g.id 
		Left Join tagged tt on tt.content_id = u.id AND content_type_id=-2 
		Left Join tags t on t.id = tt.tag_id AND t.state > 0 "; */

		// left join custom user fields
		// ONLY where not in filters
		if ($this->list_fields) {	
			foreach ($this->list_fields as $field) {
				if (!array_key_exists($field, $this->filters)) {
					$from .= " left join user_fields f_{$field}_t on f_{$field}_t.content_id=u.id and f_{$field}_t.name='{$field}' ";	
				}
			}
		}

		$from = Hook::execute_hook_filters('custom_user_search_from', $from);

		$where = ' where 1=1 ';

		if ($this->published_only) {
			$where .= " AND u.state > 0 ";
		}
		elseif($this->disable_builtin_state_check==false) {
			$where .= " AND u.state >= 0 ";
		}

		if ($this->searchtext) {
			$where .= " AND (u.username like ? or u.email like ?) "; 
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
				$where .= " and u.id in (select content_id from tagged where content_type_id=-2 and tag_id in (" . implode(',', $this->tags) . ")) ";
			}
		}

		// custom fields being filtered
		if ($this->list_fields) {
			foreach ($this->list_fields as $field) {			
				if (array_key_exists('f_' . $field, $this->filters)) {
					//CMS::pprint_r ('Got filter for custom field ' . $field);
					$this->filter_pdo_params[] = $this->filters['f_'.$field];
					$where .= " and f_{$field}_t.user_id=u.id and f_{$field}_t.name='{$field}' ";	
					$where .= " and f_{$field}_t.content = ? ";
				}
			}
		}

		// required fields filter
		foreach ($this->filters as $key => $value) {
			if (strpos($key,'f_')===false) {
				// not custom field
				// check if core field (nb - content type handled elsewhere in class, as more common)
				if (in_array($key,['state','id','name','email'])) {
					// add value to params for safety
					$this->filter_pdo_params[] = $value;
					$where .= " and u." . $key . " = ? " ;
				}
			}
		}

		// groups
		if ($this->groups) {
			// check array of ints
			// guaranteed to be arr of ints in core, but not in the wild...
			$groups_ok = true;
			foreach ($this->groups as $g) {
				if (!is_numeric($g)) {
					$groups_ok = false;
					break;
				}
			}
			if ($groups_ok) {
				$group_csv = implode(",",$this->groups);
				$where .= " and u.id in (select user_id from user_groups where group_id in (" . $group_csv . ")) ";
			}
		}

		$where = Hook::execute_hook_filters('custom_user_search_where', $where); 
		
		$this->custom_search_params = Hook::execute_hook_filters('custom_user_search_params', $this->custom_search_params); 

		$count_query = $query . $count_select . $from . $where; 
		$query = $query . $select . $from . $where;
		
		if ($this->order_by) {
			$query .= " order by " . $this->order_by . " " . $this->order_direction;
		}

		if ($this->page) {
			if (is_numeric($this->page_size) && is_numeric($this->page)) {
				$query .= " LIMIT " . (($this->page-1)*$this->page_size) . "," . $this->page_size;
			}
		}

		if (Config::debugwarnings()) {
			CMS::pprint_r ($query);
		}

		if ($this->searchtext) {
			$like = '%'.$this->searchtext.'%';
			$result = DB::fetchall($query,array_merge([$like,$like],$this->filter_pdo_params ?? [], $this->custom_search_params ?? [])); // title and note
			// set count
			$this->count = DB::fetch($count_query,array_merge([$like,$like],$this->filter_pdo_params ?? [], $this->custom_search_params ?? []))->c ?? 0;
		}
		else {
			$result = DB::fetchall($query,array_merge($this->filter_pdo_params ?? [], $this->custom_search_params ?? []));
			// set count
			$this->count = DB::fetch($count_query,array_merge($this->filter_pdo_params ?? [], $this->custom_search_params ?? []))->c ?? 0;
		}
		return $result;
	}
}