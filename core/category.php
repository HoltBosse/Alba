<?php

defined('CMSPATH') or die; // prevent unauthorized access

class Category {
	public $id;
	public $title;
	public $state;
	public $content_type;
	public $parent;
	public $content_location = null;

	public function __construct($content_type) {
		$this->id = false;
		$this->title = "";
		$this->state = 1;
		$this->parent = 0;
		$this->content_type = $content_type;
		if ($content_type) {
			$this->content_location = Content::get_content_location($this->content_type);
		}
	}

	public static function get_all_categories_by_depth($content_type, $parent=0, $depth=-1) {
		
		$depth = $depth+1;
		$result=array();
		if ($content_type) {
			$children = DB::fetchall("select * from categories where content_type=? and state>-1 and parent=?", array($content_type, $parent));
		}
		else {
			$children = DB::fetchall("select * from categories where state>-1 and parent=?", array($parent));
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
				return DB::fetch('select count(*) as c from categories where (title like ? ) and state>0',array($like))->c;
			}
			if (!is_numeric($content_type)) {
				// try and get type id
				$content_type = Content::get_content_type_id($content_type);
				if (!$content_type) {
					CMS::Instance()->show_error('Unable to determine content type when retrieving count');
				}
			}
			return DB::fetch('select count(*) as c from categories where (title like ?) and state>0 and content_type=?',array($like,$content_type))->c;
		}
		else {
			if (!$content_type) {
				// return count of all cats
				return DB::fetch('select count(*) as c from categories where state>0',array())->c;
			}
			if (!is_numeric($content_type)) {
				// try and get type id
				$content_type = Content::get_content_type_id($content_type);
				if (!$content_type) {
					CMS::Instance()->show_error('Unable to determine content type when retrieving count');
				}
			}
			return DB::fetch('select count(*) as c from categories where state>0 and content_type=?',array($content_type))->c;
		}
	}



	public function load($id) {
		$info = DB::fetch('select * from categories where id=?',array($id));
		if ($info) {
			$this->id = $info->id;
			$this->title = $info->title;
			$this->state = $info->state;
			$this->content_type = $info->content_type;
			$this->parent = $info->parent;
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
		$this->parent = $required_details_form->get_field_by_name('parent')->default; 
		$this->content_type = $required_details_form->get_field_by_name('content_type')->default; 

		if ($this->id) {
			// update
			$required_result = DB::exec("update categories set state=?,  title=?, parent=? where id=?", [$this->state, $this->title, $this->parent, $this->id]);
		}
		else {
			// new
			$required_result = DB::exec("insert into categories (state,title,content_type, parent) values(?,?,?,?)", [$this->state, $this->title, $this->content_type, $this->parent]);
			if ($required_result) {
				// update object id with inserted id
				$this->id = CMS::Instance()->pdo->lastInsertId();
			}
		}
		if (!$required_result) {
			CMS::Instance()->queue_message('Failed to save category','danger', $_SERVER['HTTP_REFERER']);
		}

		if (!$return_url) {
			if (ADMINPATH) {
				$return_url = Config::$uripath . '/admin/categories/all/' . $this->content_type;
			}
			else {
				$return_url = $_SERVER['HTTP_REFERER'];
			}
		}

		if ($error_text) {
			CMS::Instance()->queue_message($error_text,'danger', $return_url);
		}
		else {
			CMS::Instance()->queue_message('Saved category','success', $return_url);
		}
	}



}
