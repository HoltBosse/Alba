<?php
// guest, registered, author, editor, admin
defined('CMSPATH') or die; // prevent unauthorized access

class Page {
	public $id;
	public $state;
	public $title;
	public $alias;
	public $template_id;
	public $template;
	public $parent;
	public $content_type;
	public $view;
	public $updated;
	public $view_configuration;
	public $page_options; // json string from db / or serialized from form submission
	public $page_options_form;

	public function __construct() {
		$this->id = 0;
		$this->state = 1;
		$this->title = "";
		$this->alias = "";
		$this->template_id = 1;
		$this->template = null;
		$this->parent = false;
		$this->updated = date('Y-m-d H:i:s');
		$this->content_type = null;
		$this->view = null;
		$this->view_configuration = false;
		$this->page_options_form = new Form(CMSPATH . "/admin/" . "/controllers/pages/page_options.json"); // cmspath + admin, as ADMINPATH not available on front-end
		$this->page_options = null;
	}

	public function get_url() {
		// TODO: save url in new column on page save/update
		$segments = array($this->alias);
		$parent = $this->parent;
		if ($this->alias=='home' && $parent<0) {
			return Config::uripath() . "/"; 
		}
		while ($parent>=0) {
			$result = DB::fetch("select parent,alias from pages where id=?", array($parent));
			$parent = $result->parent;
			array_unshift ($segments, $result->alias);
			//$segments[] = $result->alias;
		}
		$url = Config::uripath() . '/' . implode('/',$segments);
		return $url;
	}

	public function get_page_option_value($option_name) {
		$field = $this->page_options_form->get_field_by_name($option_name);
		if ($field) {
			return $field->default;
		}
		else {
			return false;
		}
	}

	public function set_page_option_value($option_name, $value) {
		$field = $this->page_options_form->get_field_by_name($option_name);
		if ($field) {
			$field->default = $value;
			return true;
		}
		else {
			return false;
		}
	}
	

	public static function get_page_depth($id) {
		$parent_root = false;
		$parent=$id;
		$depth = 0;
		while (!$parent_root) {
			$result = DB::fetch("select parent,alias from pages where id=?", array($parent));
			$parent = $result->parent;
			$depth++;
			if ($parent=="-1") {
				$parent_root=true;
			}
		}
		return $depth;
	}

	// $pdo->prepare($sql)->execute([$name, $id]);
	public static function get_all_pages() {
		//echo "<p>Getting all users...</p>";
		//$db = new db();
		//$db = CMS::$pdo;
		//$result = $db->pdo->query("select * from users")->fetchAll();
		//$result = CMS::Instance()->pdo->query("select * from pages where state>-1")->fetchAll();
		$result = DB::fetchall("select * from pages where state>-1");
		return $result;
	}

	public static function get_all_pages_by_depth($parent=-1, $depth=-1) {
		$depth = $depth+1;
		$result=array();
		$children = DB::fetchall("select * from pages where state>-1 and parent=?", array($parent));
		foreach ($children as $child) {
			$child->depth = $depth;
			$result[] = $child;
			$result = array_merge ($result, Page::get_all_pages_by_depth($child->id, $depth));
		}
		return $result;
	}


	public static function get_pages_from_id_array ($id_array) {
		if (is_array($id_array)) {
			$in_string = implode(',',$id_array);
			$query = "select * from pages where id in ({$in_string})";
			$result = DB::fetchall($query);
			return  $result;
		}
		else {
			CMS::Instance()->queue_message('Expected array in function get_pages_from_id_array', 'danger', Config::uripath() . "/admin");
		}
	}

	public static function has_overrides ($page) {
		$w = DB::fetchall("select widgets from page_widget_overrides where page_id=? and (widgets is not null and widgets <> '')", array($page));
		//CMS::pprint_r ($w);
		return $w;
	}

	public function load_from_post() {
		$this->title = Input::getvar('title','TEXT');
		$this->state = Input::getvar('state','NUM');
		if (!$this->state) {
			$this->state = 1;
		}
		$this->template_id = Input::getvar('template','NUM');
		$this->alias = Input::getvar('alias','TEXT');
		if (!$this->alias) {
			$this->alias = Input::stringURLSafe($this->title);
		}
		$this->parent = Input::getvar('parent','NUM');
		$this->content_type = Input::getvar('content_type','NUM');
		$this->view = Input::getvar('content_type_controller_view','NUM');

		// OLD: view_options now handles by options_form.json in view
		$this->view_configuration = Input::getvar('view_options','ARRAYTOJSON');
		// TODO: load from options_form
		// e.g. $options_form = new Form(form location);
		// $options_form->set_from_submit();
		// validate
		// jsonify
		// save as $this->view_configuration
		
		$this->id = Input::getvar('id','NUM');

		$this->page_options_form->set_from_submit();

		return true;
	}

	public function load_from_id($id) {
		$result = DB::fetch("select * from pages where id=?", array($id) );
		if ($result) {
			$this->id = $result->id;
			$this->state = $result->state;
			$this->title = $result->title;
			$this->alias = $result->alias;
			$this->template_id = $result->template;
			$this->template = new Template($this->template_id);
			$this->parent = $result->parent;
			$this->updated = $result->updated;
			$this->content_type = $result->content_type;
			$this->view = $result->content_view;
			$this->view_configuration = $result->content_view_configuration;
			$this->page_options = $result->page_options;
			$this->page_options_form->deserialize_json($this->page_options); // json from db pulled into form object in page
			return true;
		}
		else {
			return false;
		}
	}


	public function load_from_alias($alias) {
		$result = DB::fetch("select * from pages where alias=?", array($alias));
		if ($result) {
			$this->id = $result->id;
			$this->state = $result->state;
			$this->title = $result->title;
			$this->alias = $result->alias;
			$this->template_id = $result->template;
			$this->template = new Template($this->template_id);
			$this->parent = $result->parent;
			$this->updated = $result->updated;
			$this->content_type = $result->content_type;
			$this->view = $result->content_view;
			$this->view_configuration = $result->content_view_configuration;
			$this->page_options = $result->page_options;
			$this->page_options_form->deserialize_json($this->page_options); // json from db pulled into form object in page
			return true;
		}
		else {
			return false;
		}
	}



	public function save() {
		if ($this->id) {
			Actions::add_action("pagecreate", (object) [
				"affected_page"=>$this->id,
			]);
			// update
			$query = "update pages set state=?, title=?, alias=?, content_type=?, content_view=?, parent=?, template=?, page_options=?, content_view_configuration=? where id=?";
			$result = CMS::Instance()->pdo->prepare($query)->execute(array(
				$this->state, 
				$this->title, 
				$this->alias, 
				$this->content_type,
				is_numeric($this->view) ? $this->view : NULL,
				$this->parent,
				$this->template_id,
				$this->page_options,
				$this->view_configuration,
				$this->id
			));
			if ($result) {
				// saved ok
				return true;
			}
			else {
				return false;
			}
		}
		else {
			// insert new
			$query = "insert into pages (state, title, alias, content_type, content_view, parent, template, page_options, content_view_configuration) values(?,?,?,?,?,?,?,?,?)";
			try {
				$stmt = CMS::Instance()->pdo->prepare($query);
				$result = $stmt->execute(array(
					$this->state, 
					$this->title, 
					$this->alias, 
					$this->content_type,
					is_numeric($this->view) ? $this->view : NULL,
					$this->parent,
					$this->template_id,
					$this->page_options,
					$this->view_configuration
				));	
			}
			catch (PDOException $e) {
				//CMS::Instance()->queue_message('Error saving page','danger',Config::uripath().'/admin/pages/');
				if (Config::debug()) {
					CMS::Instance()->queue_message('Error saving page: ' . $e->getMessage(),'danger',Config::uripath().'/admin/pages/');
					//echo "<code>" . $e->getMessage() . "</code>";
				}
				$result = false;
				exit(0);
			}
			if ($result) {
				// update page id with last pdo insert
				$this->id = CMS::Instance()->pdo->lastInsertId();
				Actions::add_action("pagecreate", (object) [
					"affected_page"=>$this->id,
				]);
				return true;
			}
			else {
				// todo - check for username/email already existing and clarify
				CMS::Instance()->queue_message('Unable to create page.' . $query ,'danger',Config::uripath().'/admin/pages');
				return false;
			}
		}
	}



	






}
