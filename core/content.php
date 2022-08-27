<?php

defined('CMSPATH') or die; // prevent unauthorized access

class Content {
	public $id;
	public $title;
	public $state;
	public $description;
	public $configuration;
	public $updated;
	public $content_type;
	public $created_by;
	public $updated_by;
	public $note;
	public $tags;
	public $alias;
	public $category;

	public function __construct($content_type=0) {
		$this->id = false;
		$this->title = "";
		$this->description = "";
		$this->state = 1;
		$this->updated = date('Y-m-d H:i:s');
		$this->content_type = $content_type;
		$this->tags = [];
		if ($content_type) {
			$this->content_location = $this->get_content_location($this->content_type);
		}
		$this->created_by = CMS::Instance()->user->id;
		$this->alias="";
		$this->category=0;
	}

	public function get_content_count($content_type, $search="", $state=0) {
		if ($search) {
			$like = '%' . $search . '%';
			if (!$content_type) {
				// return count of all content
				return DB::fetch('select count(*) as c from content where (title like ? OR note like ?) and state>?',array($like,$like,$state))->c;
			}
			if (!is_numeric($content_type)) {
				// try and get type id
				$content_type = Content::get_content_type_id($content_type);
				if (!$content_type) {
					CMS::Instance()->show_error('Unable to determine content type when retrieving count');
				}
			}
			return DB::fetch('select count(*) as c from content where (title like ? OR note like ?) and state>0 and content_type=?',array($like,$like,$content_type))->c;
		}
		else {
			if (!$content_type) {
				// return count of all content
				return DB::fetch('select count(*) as c from content where state>?',array($state))->c;
			}
			if (!is_numeric($content_type)) {
				// try and get type id
				$content_type = Content::get_content_type_id($content_type);
				if (!$content_type) {
					CMS::Instance()->show_error('Unable to determine content type when retrieving count');
				}
			}
			return DB::fetch('select count(*) as c from content where state>? and content_type=?',array($state, $content_type))->c;
		}
	}

	private function make_alias_unique() {
		$is_unique = false;
		while (!$is_unique) {
			$results = DB::fetchall("select * from content where alias=? and content_type=?", array($this->alias, $this->content_type) );
			// if this is an existing content item, make sure we don't count itself as a clashing alias
			$self_clash = false;
			if ($this->id) {
				foreach ($results as $potential_clash) {
					if ($potential_clash->id==$this->id) {
						$self_clash=true;
						break;
					}
				}
			}
			if ( (sizeof($results) > 0 && !$self_clash) || ((sizeof($results) > 1 && $self_clash )) ) {
				// if clash isn't with just itself
				if ($this->id) {
					// add id to alias to make unique for existing content item
					$this->alias = $this->alias . "-" . $this->id;
					CMS::Instance()->queue_message('Added content id as suffix to "URL Friendly" field to ensure uniqueness.','warning');
				}
				else {
					// really awful ugly way of making a somewhat unique alias for new content item
					// if you have 9000 aliases identical except for this random
					// suffix, this will loop infinitely, and even before then 
					// will slow down as it approaches this point waiting to find
					// a unique suffix
					// I leave this is a relatively easily solved gift to future me
					$fourRandomDigit = mt_rand(1000,9999);
					$this->alias = $this->alias . "-" . $fourRandomDigit;
					CMS::Instance()->queue_message('Added random suffix to "URL Friendly" field to ensure uniqueness.','warning');
				}
			}
			else {
				$is_unique=true;
			}
		}
	}

	public function get_field($field_name) {
		$value = DB::fetch("select content from content_fields where content_id=? and name=?", [$this->id, $field_name]);
		if ($value) {
			return $value->content;
		}
		else {
			return null;
		}
	}

	public function load($id) {
		$info = DB::fetch('select * from content where id=?',array($id));
		if ($info) {
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->note = $info->note;
			$this->alias = $info->alias;
			$this->start = $info->start;
			$this->end = $info->end;
			$this->content_type = $info->content_type;
			$this->content_location = $this->get_content_location($this->content_type);
			$this->created_by = $info->created_by;
			$this->updated_by = $info->updated_by;
			$this->tags = Tag::get_tags_for_content($this->id, $this->content_type);
			$this->category = $info->category;
			return true;
		}
		else {
			return false;
		}
	}

	public function load_from_alias($alias) {
		$info = DB::fetch('select * from content where alias=?',array($alias));
		if ($info) {
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->note = $info->note;
			$this->alias = $info->alias;
			$this->start = $info->start;
			$this->end = $info->end;
			$this->content_type = $info->content_type;
			$this->content_location = $this->get_content_location($this->content_type);
			$this->created_by = $info->created_by;
			$this->tags = Tag::get_tags_for_content($this->id, $this->content_type);
			$this->category = $info->category;
			return true;
		}
		else {
			return false;
		}
	}

	public function duplicate() {
		// remember original id
		$original_id = $this->id;
		// make current duplicate id blank
		$this->id = false;
		// add copy to title
		$this->title = $this->title . " - Copy";
		$this->make_alias_unique();
		// ordering
		$ordering = DB::fetch("select (max(ordering)+1) as ordering from content")->ordering;
		if (!$ordering) {
			$ordering=1;
		}
		$params = array($this->state, $ordering, $this->title, $this->alias, $this->content_type, $this->updated_by, $this->updated_by, $this->note, $this->start, $this->end, $this->category);
		$required_result = DB::exec("insert into content (state,ordering,title,alias,content_type, created_by, updated_by, note, start, end, category) values(?,?,?,?,?,?,?,?,?,?,?)", $params);
		if ($required_result) {
			$this->id = CMS::Instance()->pdo->lastInsertId();
			// set tags
			// note - $this->tags is already array - unlike code in save() function below
			$tag_id_array=[];
			foreach ($this->tags as $curtag) {
				$tag_id_array[] = $curtag->id;
			}
			Tag::set_tags_for_content($this->id, $tag_id_array, $this->content_type);
			// copy content fields
			$cur_content_fields = DB::fetchAll('select * from content_fields where content_id=?',$original_id);
			//echo "<p>origin content:</p>"; CMS::pprint_r ($cur_content_fields);
			foreach ($cur_content_fields as $f) {
				DB::exec('insert into content_fields (content_id, name, field_type, content) values(?,?,?,?)', array($this->id, $f->name, $f->field_type, $f->content));
			}
			CMS::Instance()->queue_message('Content duplicated','success', Config::$uripath . "/admin/content/all");
		}
		else {
			CMS::Instance()->queue_message('Error duplicating content','danger', Config::$uripath . "/admin/content/all");
		}
	}

	public function save($required_details_form, $content_form, $return_url='') {
		// return url will be used as passed, if left blank will use referral
		// unless in ADMIN section, in which case admin content all page will be used
		
		// update this object with submitted and validated form info
		$this->title = $required_details_form->get_field_by_name('title')->default;
		$this->state = $required_details_form->get_field_by_name('state')->default;
		$this->note = $required_details_form->get_field_by_name('note')->default;
		$this->alias = $required_details_form->get_field_by_name('alias')->default;
		if (!$this->alias) {
			$this->alias = Input::stringURLSafe($this->title);
		}
		$this->start = $required_details_form->get_field_by_name('start')->default;
		$this->end = $required_details_form->get_field_by_name('end')->default;
		$this->updated_by = CMS::Instance()->user->id;
		$this->tags = $required_details_form->get_field_by_name('tags')->default; 
		$this->category = $required_details_form->get_field_by_name('category')->default; 

		// ensure alias is unique for content_type - will use id for existing content, random 4 digit number otherwise
		$this->make_alias_unique();

		if (!$this->start) {
			$this->start = time();
		}
		else {
			//$this->start = date("Y-m-d H:i:s", strtotime($this->start));
			$this->start = strtotime($this->start);
		}
		if (!$this->end) {
			$this->end = null;
		}
		else {
			//$this->end = date("Y-m-d H:i:s", strtotime($this->end));
			$this->end = strtotime($this->end);
		}
		
		Hook::execute_hook_actions('before_content_save', $this, $content_form);

		if ($this->id) {
			// update
			$params = array($this->state, $this->title, $this->alias, $this->note, $this->start, $this->end, $this->updated_by, $this->category, $this->id) ;
			$required_result = DB::exec("update content set state=?,  title=?, alias=?, note=?, start=FROM_UNIXTIME(?), end=FROM_UNIXTIME(?), updated_by=?, category=? where id=?", $params);
		}
		else {
			// new
			//CMS::pprint_r ($this);
			// get next order value
			$ordering = DB::fetch("select (max(ordering)+1) as ordering from content")->ordering;
			if (!$ordering) {
				$ordering=1;
			}
			$query = "insert into content (state,ordering,title,alias,content_type, created_by, updated_by, note, start, end, category) values(?,?,?,?,?,?,?,?,FROM_UNIXTIME(?),FROM_UNIXTIME(?),?)";
			$params = array($this->state, $ordering, $this->title, $this->alias, $this->content_type, $this->updated_by, $this->updated_by, $this->note, $this->start, $this->end, $this->category);
			$required_result = DB::exec($query, $params);
			if ($required_result) {
				// update object id with inserted id
				$this->id = CMS::Instance()->pdo->lastInsertId();
			}
		}
		if (!$required_result) {
			// TODO: specific message for new/edit etc
			CMS::Instance()->queue_message('Failed to save content','danger', $_SERVER['HTTP_REFERER']);
		}

		// set tags
		Tag::set_tags_for_content($this->id, json_decode($this->tags), $this->content_type);

		// now save fields
		/* CMS::pprint_r ($this);
		CMS::pprint_r ($content_form); */
		// first remove old field data if any exists
		DB::exec("delete from content_fields where content_id=?", array($this->id));
		$error_text="";
		foreach ($content_form->fields as $field) {
			// insert field info
			if (isset($field->save)) {
				if ($field->save===false) {
					// field have save property set explicitly to false - SKIP saving
					// this may be a field such as an SQL statement from a non-cms table, or just markup etc
					continue;
				}
			}
			// TODO: handle other arrays 
			/* CMS::pprint_r ($field);  */
			if ($field->filter=="ARRAYOFINT") {
				// convert array of int to string
				if (is_array($field->default)) {
					$field->default = implode(",",$field->default);
				}
			}
			CMS::Instance()->log("Saving: " . $field->default);
			$result = DB::exec("insert into content_fields (content_id, name, field_type, content) values (?,?,?,?)", [$this->id, $field->name, $field->type, $field->default]);
			if (!$result) {
				$error_text .= "Error saving: " . $field->name . " ";
				CMS::Instance()->log("Error saving: " . $field->name);
			}
		}

		if (!$return_url) {
			if (ADMINPATH) {
				$return_url = Config::$uripath . '/admin/content/all/' . $this->content_type;
			}
			else {
				$return_url = $_SERVER['HTTP_REFERER'];
			}
		}

		if ($error_text) {
			CMS::Instance()->queue_message($error_text,'danger', $return_url);
		}
		else {
			Hook::execute_hook_actions('on_content_save', $this);
			CMS::Instance()->queue_message('Saved content','success', $return_url);
		}
	}

	


	// $pdo->prepare($sql)->execute([$name, $id]);
	public static function get_all_content_types() {
		//echo "<p>Getting all users...</p>";
		//$db = new db();
		//$db = CMS::$pdo;
		//$result = $db->pdo->query("select * from users")->fetchAll();
		//$result = CMS::Instance()->pdo->query("select * from content_types where state > 0 order by id ASC")->fetchAll();
		$result = DB::fetchall('select * from content_types where state > 0 order by id ASC');
		return $result;
	}
	
	public static function get_content_type_title($content_type) {
		if (!$content_type) {
			return false;
		}
		if ($content_type=="-1") {
			return "User";
		}
		if ($content_type=="-2") {
			return "Image/Media";
		}
		if ($content_type=="-3") {
			return "Tag";
		}
		$result = DB::fetch("select title from content_types where id=?", array($content_type));
		if ($result) {
			return $result->title;
		}
		else {
			return false;
		}
	}

	public static function get_content_type_fields($content_type) {
		if (!$content_type) {
			return false;
		}
		$result = DB::fetch("select * from content_types where id=?", array($content_type));
		if ($result) {
			return $result;
		}
		else {
			return false;
		}
	}

	public static function get_content_type_id($controller_location) {
		if (!$controller_location) {
			return false;
		}
		$result = DB::fetch("select id from content_types where controller_location=?", [$controller_location]);
		if ($result) {
			return $result->id;
		}
		else {
			//CMS::Instance()->queue_message('Failed to determine content type id for controller_location: ' . $controller_location, "error");
			return false;
		}
	}

	public static function get_config_value ($config, $key) {
		// $config is array of {name:"",value:""} pairs
		foreach ($config as $config_pair) {
			if ($config_pair->name==$key) {
				return $config_pair->value;
			}
		}
		return null;
	}

	public static function get_applicable_tags ($content_type_id) {
		$query = "select * from tags where (filter=2 and id in (select tag_id from tag_content_type where content_type_id=?)) ";
		$query.= "or (filter=1 and id not in (select tag_id from tag_content_type where content_type_id=?)) ";
		$tags = DB::fetchall($query, array($content_type_id, $content_type_id));
		return $tags;
	}

	public static function get_applicable_categories ($content_type_id) {
		$query = "select * from categories where content_type=?";
		$cats = DB::fetchall($query, array($content_type_id));
		return $cats;
	}

	public static function get_content_location($content_type_id) {
		$result = DB::fetch("select controller_location from content_types where id=?", array($content_type_id));
		return $result->controller_location;
	}

	public static function get_view_location($view_id) {
		$result = DB::fetch("select location from content_views where id=?", array($view_id));
		return $result->location;
	}

	public static function get_content_type_for_view ($view_id) {
		$result = DB::fetch("select content_type_id from content_views where id=?", array($view_id));
		return $result->content_type_id;
	}

	public static function get_view_title($view_id) {
		if (!$view_id) {
			return false;
		}
		$result = DB::fetch("select title from content_views where id=?", array($view_id));
		if ($result) {
			return $result->title;
		}
		else {
			return false;
		}
	}


	public static function save_version($old_content) {
		$location = Content::get_content_location($old_content->content_type);
		//CMS::pprint_r ("Loading: " . CMSPATH . '/controllers/' . $location . "/custom_fields.json");
		$content_form = new Form (CMSPATH . '/controllers/' . $location . "/custom_fields.json");
		//CMS::pprint_r ($content_form);exit(0);
		foreach ($content_form->fields as $field) {
			// insert field info
			if (isset($field->save)) {
				if ($field->save===false) {
					// field have save property set explicitly to false - SKIP saving
					// this may be a field such as an SQL statement from a non-cms table, or just markup etc
					// remove from saved version form data and skip
					unset($content_form->fields[$field->name]);
					/* CMS::pprint_r ('skipping false field');
					CMS::pprint_r ($field);
					exit(0); */
					continue;
				}
			}
			if ($field->filter=="ARRAYOFINT") {
				// convert array of int to string
				$field->default = implode($field->default);
			}
			$field_variable = "f_" . $field->name;
			$field->default = $old_content->{$field_variable};
		}
		$version_json = json_encode($content_form->fields); // only saveable fields encoded :)
		
		$content_versions = Configuration::get_configuration_value ('general_options', 'content_versions');
		if (is_numeric($content_versions) && $content_versions>0 && !$new_content) {
			$params = [$old_content->id, CMS::Instance()->user->id, $version_json];
			$query = "insert into content_versions (content_id, created_by, fields_json) VALUES (?,?,?)";
			$ok = DB::exec($query, $params);
			if ($ok) {
				// remove old versions if required (could be more/fewer than 1, depending on config value change since last save)
				// there's undoubtedly a more efficient way to make this happen in case multiple deletions are required, but
				// most of the time there'll be just 1 or none
				$old_versions = DB::fetchAll('select id from content_versions where content_id=? order by created DESC',array($old_content->id));
				for ($n=$content_versions; $n<sizeof($old_versions); $n++) {
					DB::exec("delete IGNORE from content_versions where id=?",array($old_versions[$n]->id));
				}
			}
			else {
				CMS::Instance()->queue_message('Unable to save version','warning',$_SERVER['REQUEST_URI']);
			}
		}
	}

	public static function get_all_content_for_id ($id) {
		// accept content id, return object with all named required fields (title, state) etc
		// determine content type and parse content type custom_fields json
		// obtain all content_fields for content and store in custom_fields object property
		// loop over parsed json and create object properties starting with f_ (same as returned by get_all_content function)
		// if no matched data from content_fields found, populate with default value from form
		// - function is useful for complex content types that have not been 'fixed' (may have missing content_fields in DB)
		// - todo: decide if it's worthwhile to also include in returned object all fields in db, not just those required by json form?
		$result = DB::fetch('select * from content where id=?',$id);
		if ($result) {
			$location = Content::get_content_location($result->content_type);
			$content_fields = DB::fetchAll('select * from content_fields where content_id=?',$result->id);
			$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
			// convert content fields from indexed array to assoc array with 'name' as key
			$content_fields_assoc = array_column($content_fields, null, 'name');
			foreach ($custom_fields->fields as $f) {
				if (property_exists($f,'save')) {
					if ($f->save===false) {
						// skip if save property is false - assume any other value or save property missing indicates saveable value
						continue;
					}
				}
				// saveable field
				// check if field content already found in db
				$keyname = "f_" . $f->name;
				if (array_key_exists($f->name, $content_fields_assoc)) {
					$result->{$keyname} = $content_fields_assoc[$f->name]->content;
				}
				else {
					// not found, use default from form if exists
					if (property_exists($f,'default')) {
						$result->{$keyname} = $f->default;
					}
					else {
						$result->{$keyname} = null; // hey, at least we have the object property :)
					}
				}
			}
			return $result;
		}
		return false;
	}


	//exists for legacy compat, please use new content_search for new code instead of this
	public static function get_all_content($order_by="id", $type_filter=false, $id=null, $tag=null, $published_only=null, $list_fields=[], $ignore_fields=[], $filter_field=null, $filter_val=null, $page=0, $search="", $custom_pagination_size=null) {
		//add inputed filters, and then if id is present, add that to filters as well
		$filters = [$filter_field=>$filter_val];
		$id ? $filters["id"] = $id : "";

		if(!$list_fields && $type_filter) {
			$location = Content::get_content_location($type_filter);
			if(!is_numeric($type_filter)) {
				$location = $type_filter;
			}
			$form = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
			foreach($form->fields as $field) {
				$list_fields[] = $field->name;
			}
		}

		$content_search = new Content_Search();
		$content_search->order_by = $order_by;
		$content_search->type_filter = $type_filter;
		$content_search->tags = [$tag];
		$content_search->published_only = $published_only;
        $content_search->list_fields = $list_fields;
		$content_search->ignore_fields = $ignore_fields;
        $content_search->filters = $filters;
		$content_search->page = $page;
		$content_search->searchtext = $search;
		$content_search->$page_size = $custom_pagination_size;

		return $content_search->exec();
	}

}
