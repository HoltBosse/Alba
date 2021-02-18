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
	public $tags;
	public $alias;

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
	}

	private function make_alias_unique() {
		$is_unique = false;
		while (!$is_unique) {
			// make alias unique for specific content type - it's fine to have same alias for multiple contents of different types
			/* $query = "select * from content where alias=? and content_type=?";
			$stmt = CMS::Instance()->pdo->prepare($query);
			$stmt->execute(array($this->alias, $this->content_type));
			$results = $stmt->fetchAll();  */
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
					$this->alias = $this->alias . "_" . $this->id;
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
					$this->alias = $this->alias . "_" . $fourRandomDigit;
					CMS::Instance()->queue_message('Added random suffix to "URL Friendly" field to ensure uniqueness.','warning');
				}
			}
			else {
				$is_unique=true;
			}
		}
	}

	public function get_field($field_name) {
		$query = "select content from content_fields where content_id=? and name=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($this->id, $field_name));
		$value = $stmt->fetch();
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
			$this->tags = Tag::get_tags_for_content($this->id, $this->content_type);
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
			return true;
		}
		else {
			return false;
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

		// ensure alias is unique for content_type - will use id for existing content, random 4 digit number otherwise
		$this->make_alias_unique();

		if ($this->id) {
			// update
			$query = "update content set state=?,  title=?, alias=?, note=?, start=?, end=?, updated_by=? where id=?";
			$stmt = CMS::Instance()->pdo->prepare($query);
			if (!$this->start) {
				$this->start = null;
			}
			if (!$this->end) {
				$this->end = null;
			}
			$params = array($this->state, $this->title, $this->alias, $this->note, $this->start, $this->end, $this->updated_by, $this->id) ;
			$required_result = $stmt->execute( $params );
		}
		else {
			// new
			//CMS::pprint_r ($this);
			// get next order value
			$query = "select (max(ordering)+1) as ordering from content";
			$stmt = CMS::Instance()->pdo->prepare($query);
			$stmt->execute(array());
			$ordering = $stmt->fetch()->ordering;
			if (!$ordering) {
				$ordering=1;
			}
			$query = "insert into content (state,ordering,title,alias,content_type, created_by, updated_by, note, start, end) values(?,?,?,?,?,?,?,?,FROM_UNIXTIME(?),?)";
			$stmt = CMS::Instance()->pdo->prepare($query);
			if (!$this->start) {
				//$this->start = date('Y-m-d H:i:s');
				//$this->start = time();
				$this->start = null;
			}
			if (!$this->end) {
				$this->end = null;
			}
			$params = array($this->state, $ordering, $this->title, $this->alias, $this->content_type, $this->updated_by, $this->updated_by, $this->note, $this->start, $this->end);
			$required_result = $stmt->execute( $params );
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
		$query = "delete from content_fields where content_id=?";
		$stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($this->id));
		$error_text="";
		foreach ($content_form->fields as $field) {
			// insert field info
			// TODO: handle other arrays 
			/* CMS::pprint_r ($field);  */
			if ($field->filter=="ARRAYOFINT") {
				// convert array of int to string
				$field->default = implode($field->default);
			}
			CMS::Instance()->log("Saving: " . $field->default);
			$query = "insert into content_fields (content_id, name, field_type, content) values (?,?,?,?)";
			$stmt = CMS::Instance()->pdo->prepare($query);
			$field_data = array($this->id, $field->name, $field->type, $field->default);
			$result = $stmt->execute($field_data);
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
		/* $stmt = CMS::Instance()->pdo->prepare("select title from content_types where id=?");
		$stmt->execute(array($content_type));
		$result = $stmt->fetch(); */
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
		/* $stmt = CMS::Instance()->pdo->prepare("select title from content_types where id=?");
		$stmt->execute(array($content_type));
		$result = $stmt->fetch(); */
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
		$stmt = CMS::Instance()->pdo->prepare("select id from content_types where controller_location=?");
		$stmt->execute(array($controller_location));
		$result = $stmt->fetch();
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
		//$query.= "and state>-1";
		/* $stmt = CMS::Instance()->pdo->prepare($query);
		$stmt->execute(array($content_type_id, $content_type_id)); */
		$tags = DB::fetchall($query, array($content_type_id, $content_type_id));
		//return $stmt->fetchAll();
		return $tags;
	}

	public static function get_content_location($content_type_id) {
		/* $stmt = CMS::Instance()->pdo->prepare("select controller_location from content_types where id=?");
		$stmt->execute(array($content_type_id));
		$result = $stmt->fetch(); */
		$result = DB::fetch("select controller_location from content_types where id=?", array($content_type_id));
		return $result->controller_location;
	}

	

	public static function get_content_count($content_type_id) {
		/* $stmt = CMS::Instance()->pdo->prepare("select count(*) as c from content where content_type=?");
		$stmt->execute(array($content_type_id));
		$result = $stmt->fetch(); */
		$result = DB::fetch("select count(*) as c from content where content_type=?", array($content_type_id));
		return $result->c;
	}

	public static function get_view_location($view_id) {
		/* $stmt = CMS::Instance()->pdo->prepare("select location from content_views where id=?");
		$stmt->execute(array($view_id));
		$result = $stmt->fetch(); */
		$result = DB::fetch("select location from content_views where id=?", array($view_id));
		return $result->location;
	}

	public static function get_content_type_for_view ($view_id) {
		/* $stmt = CMS::Instance()->pdo->prepare("select content_type_id from content_views where id=?");
		$stmt->execute(array($view_id));
		$result = $stmt->fetch(); */
		//echo "trying to get content type for view {$view_id}";
		//exit (0);
		$result = DB::fetch("select content_type_id from content_views where id=?", array($view_id));
		return $result->content_type_id;
	}

	public static function get_view_title($view_id) {
		if (!$view_id) {
			return false;
		}
		/* $stmt = CMS::Instance()->pdo->prepare("select title from content_views where id=?");
		$stmt->execute(array($view_id));
		$result = $stmt->fetch(); */
		$result = DB::fetch("select title from content_views where id=?", array($view_id));
		if ($result) {
			return $result->title;
		}
		else {
			return false;
		}
	}
	
	public static function get_all_content($order_by="id", $type_filter=false, $id=null, $tag=null, $published_only=null) {
		// order by id by default
		// type filter for back-end curation if set and no id/tag passed, will return only content fields in custom_fields.json 'list' property
		// id / tag if either set will get ALL content fields for matching content id or content tagged with tag id

		$list_fields = [];

		if ($type_filter) {
			// get list fields from custom_fields.json file
			$location = Content::get_content_location($type_filter);
			//$custom_fields = json_decode(file_get_contents (CMSPATH . '/controllers/' . $location . '/custom_fields.json'));
			$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
			if ($id !== null || $tag !== null) {
				// get all fields
				foreach ($custom_fields->fields as $custom_field) {
					$list_fields[] = $custom_field->name;
				}
			}
			else {
				// id not passed, just get fields in 'list' property from custom_fields.json
				if (property_exists($custom_fields,'list')) {
					foreach ($custom_fields->list as $custom_field_name) {
						$list_fields[] = $custom_field_name;
					}
				}
			}
		}
		$query = "select";
		$select = " c.id, c.state, c.content_type, c.title, c.alias, c.ordering, c.start, c.end, c.created_by, c.updated_by, c.note ";
		if ($list_fields) {
			foreach ($list_fields as $field) {
				$select .= " ,f_{$field}.content as f_{$field}";
			}
		}

		$from = " from content c ";
		if ($list_fields) {
			foreach ($list_fields as $field) {
				$from .= " ,content_fields f_{$field}";
			}
		}
		
		$where = " where ";
		if ($published_only) {
			$where .= " c.state > 0 ";
		}
		else {
			$where .= " c.state >= 0 ";
		}
		
		if ($list_fields) {
			foreach ($list_fields as $field) {
				$where .= " and f_{$field}.content_id=c.id and f_{$field}.name='{$field}' ";
			}
		}
		//$query = "select  c.id, c.state, c.content_type, c.title, c.alias, c.start, c.end, c.created_by, c.updated_by, c.note from content c where state>0";
		
		if ($type_filter && is_numeric($type_filter)) {
			$where .= " and c.content_type={$type_filter} ";
		}

		if ($tag) {
			if (is_numeric($tag)) {
				$where .= " and c.id in (select content_id from tagged where tag_id={$tag} and content_type_id=c.content_type) ";
			}
			else {
				CMS::show_error('Content Blog Error - Numerical tag id expected');
			}
		}

		if ($id !== null) {
			if (is_numeric($id)) {
				$where .= " and c.id={$id} ";
			}
		}

		$query = $query . $select . $from . $where;
		//CMS::pprint_r ($query);
		if (Config::$debug) {
			CMS::pprint_r ($query);
		}

		if ($order_by=="ordering"||$order_by=="id"||$order_by=="start") {
			//$result = CMS::Instance()->pdo->query($query . " order by " . $order_by . " ASC")->fetchAll();
			$query .= " order by " . $order_by . " DESC";
			//return $result;
		}
		else {
			//CMS::Instance()->queue_message('Unknown ordering method: ' . $order_by ,'danger', $_SERVER["HTTP_REFERER"]);
			//$result = CMS::Instance()->pdo->query($query . " order by id ASC")->fetchAll();
			$query .= " order by id DESC";
			//return $result;
		}
		$result = DB::fetchall($query);
		return $result;
	}

}