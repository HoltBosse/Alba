<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \Exception;

class Category {
	public $id;
	public $title;
	public $state;
	public $content_type;
	public $parent;
	public $custom_fields;
	public $content_location = null;
	public ?int $domain = null;

	public function __construct($content_type) {
		$this->id = false;
		$this->title = "";
		$this->state = 1;
		$this->parent = 0;
		$this->content_type = $content_type;
		$this->custom_fields = "";
		$this->domain = null;
		if ($content_type) {
			$this->content_location = Content::get_content_location($this->content_type);
		}
	}

	public static function get_all_categories_by_depth($content_type, $parent=0, $depth=-1) {
		
		$depth = $depth+1;
		$result=[];
		if ($content_type) {
			$children = DB::fetchAll("select * from categories where content_type=? and state>-1 and parent=?", [$content_type, $parent]);
		}
		else {
			$children = DB::fetchAll("select * from categories where state>-1 and parent=?", [$parent]);
		}
		foreach ($children as $child) {
			$child->depth = $depth;
			$result[] = $child;
			$result = array_merge ($result, Category::get_all_categories_by_depth($content_type, $child->id, $depth));
		}
		return $result;
	}

	public static function get_category_count($content_type, $search="") {
		if ($search) {
			$like = '%' . $search . '%';
			if (!$content_type) {
				// return count of all content
				return DB::fetch('select count(*) as c from categories where (title like ? ) and state>0',[$like])->c;
			}
			if (!is_numeric($content_type)) {
				// try and get type id
				$content_type = Content::get_content_type_id($content_type);
				if (!$content_type) {
					throw new Exception("Unable to determine content type when retrieving count");
				}
			}
			return DB::fetch('select count(*) as c from categories where (title like ?) and state>0 and content_type=?',[$like,$content_type])->c;
		}
		else {
			if (!$content_type) {
				// return count of all cats
				return DB::fetch('select count(*) as c from categories where state>0',[])->c;
			}
			if (!is_numeric($content_type)) {
				// try and get type id
				$content_type = Content::get_content_type_id($content_type);
				if (!$content_type) {
					throw new Exception('Unable to determine content type when retrieving count');
				}
			}
			return DB::fetch('select count(*) as c from categories where state>0 and content_type=?',[$content_type])->c;
		}
	}



	public function load($id) {
		$info = DB::fetch('select * from categories where id=?',[$id]);
		if ($info) {
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->content_type = $info->content_type;
			$this->parent = $info->parent;
			$this->custom_fields = $info->custom_fields;
			$this->domain = $info->domain;
			return true;
		}
		else {
			return false;
		}
	}

	public function save($required_details_form, $custom_fields_form = "") {
		// update this object with submitted and validated form info
		$this->title = $required_details_form->getFieldByName('title')->default;
		$this->state = $required_details_form->getFieldByName('state')->default;
		$this->parent = $required_details_form->getFieldByName('parent')->default; 
		$this->content_type = $required_details_form->getFieldByName('content_type')->default; 
		$this->custom_fields = $custom_fields_form ? json_encode($custom_fields_form) : "";

		$domain = (CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])) ?? CMS::getDomainIndex($_SERVER["HTTP_HOST"]);

		
		//if shared accross all domains
		if (isset($_ENV["category_custom_fields_form_path"])) {
			$customFieldsFormObject = json_decode(file_get_contents($_ENV["category_custom_fields_form_path"]));
			if(isset($customFieldsFormObject->multi_domain_shared_instances) && $customFieldsFormObject->multi_domain_shared_instances==true) {
				$domain = null;
			}
		}

		//run last
		if($this->id && $this->domain!==null && $this->domain!==$domain) {
			//dont change domain if it already has one
			$domain = $this->domain;
		}

		$this->domain = $domain;

		if ($this->id) {
			Actions::add_action("categoryupdate", (object) [
				"affected_category"=>$this->id,
			]);
			// update
			$required_result = DB::exec("update categories set state=?,  title=?, parent=?, custom_fields=? where id=?", [$this->state, $this->title, $this->parent, $this->custom_fields, $this->id]);
		}
		else {
			// new
			$required_result = DB::exec("insert into categories (state,title,content_type, parent, custom_fields) values(?,?,?,?,?)", [$this->state, $this->title, $this->content_type, $this->parent, $this->custom_fields]);
			if ($required_result) {
				// update object id with inserted id
				$this->id = DB::getLastInsertedId();

				Actions::add_action("categorycreate", (object) [
					"affected_category"=>$this->id,
				]);
			}
		}
		if (!$required_result) {
			CMS::Instance()->log("Failed to save category");
			return false;
		}

		Hook::execute_hook_actions('on_category_save', $this);
		return true;
	}



}