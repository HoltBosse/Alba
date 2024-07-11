
<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Widget {
	public $id;
	public $title;
	public $type_id;
	public $type;
	public $state;
	public $options;
	public $note;
	public $ordering;
	public $position_control;
	public $global_position;
	public $page_list;
	public $form;
	public $form_data;

	public function render_edit() {
		if (CMS::Instance()->user->is_member_of(1)) { ?>
			<div class='front_end_edit_wrap' >
				<a style='' target="_blank" href='/admin/widgets/edit/<?php echo $this->id;?>'>EDIT &ldquo;<?= htmlspecialchars($this->title); ?>&rdquo;</a>
			</div>
		<?php
		}
	}

	public function render() {
		echo "<h1>Hello, I'm a base class widget!</h1>";
	}

	public function internal_render() {
		ob_start();
		$this->render();
		$output = ob_get_clean();
		$output = Hook::execute_hook_filters('on_widget_render', $output, $this);
		echo $output;
	}

	public static function get_all_widget_types() {
		return CMS::Instance()->pdo->query('select * from widget_types')->fetchAll();
	}

	public static function get_widgets_for_position($page_id, $position) {
		$query = 'select id,title,type,state 
		from widgets 
		where ((position_control=1 and not find_in_set(?, page_list)) OR (position_control=0 and find_in_set(?, page_list))) 
		and global_position=? and state>=0 ORDER BY ordering,id ASC';
		return DB::fetchAll($query, [$page_id, $page_id, $position]);
	}

	public function get_option_value($option_name) {
		foreach ($this->options as $option) {
			/* if (property_exists($option, $option_name)) {
				return $option->$option_name;
			} */
			if ($option->name==$option_name) {
				return $option->value;
			}
		}
		return false;
	}

	public static function get_widget_title ($id) {
		return DB::fetch("select title from widgets where id=?", [$id])->title;
	}


	public static function get_widget_overrides_csv_for_position ($page, $position) {
		//echo "<h1>checking page {$page} position {$position}</h1>";
		$widget_ids = DB::fetch("select widgets from page_widget_overrides where page_id=? and position=?", [$page, $position])->widgets ?? null; // csv
		return $widget_ids;
	}

	public static function get_widget_overrides_for_position ($page, $position) {
		//echo "<h1>checking page {$page} position {$position}</h1>";
		$widget_ids = DB::fetch("select widgets from page_widget_overrides where page_id=? and position=?", [$page, $position])->widgets ?? null; // csv
		if ($widget_ids) {
			$query = 'select id,title,type,state 
			from widgets 
			where id in ('.$widget_ids.') and state>=0 
			ORDER BY FIELD(id,'.$widget_ids.')';
			return DB::fetchAll($query);
		}
		return false;
	}

	

	public function show_admin_form() {
		$this->form = new Form();
		$this->form->load_json(CMSPATH . "/widgets/");
	}

	public function render_custom_backend() {
		return false;
	}

	public function load($id, $type_id=null) {
		//id of -1 is a new widget
		if($id!=-1) {
			$info = CMS::Instance()->pdo->query('select * from widgets where id=' . $id)->fetch();
			$this->id = $info->id;
			$this->title = $info->title;
			$this->type_id = $info->type;
			$this->state = $info->state;
			$this->note = $info->note;
			$this->ordering = $info->ordering;
			$this->options = json_decode($info->options);
			$this->position_control = $info->position_control;
			$this->global_position = $info->global_position;
			$this->page_list = explode(',', $info->page_list);
		} else {
			$this->type_id = $type_id;
		}

		$this->type = CMS::Instance()->pdo->query('select * from widget_types where id=' . $this->type_id)->fetch();
		$this->form_data = JSON::load_obj_from_file(CMSPATH . '/widgets/' . $this->type->location . '/widget_config.json');

		Hook::execute_hook_actions('on_widget_load', $this, "thinggg");
	}

	public static function get_widget_type_title($widget_type_id) {
		if (is_numeric($widget_type_id)) {
			return CMS::Instance()->pdo->query('select title from widget_types where id=' . $widget_type_id)->fetch()->title;
		}
		else {
			return false;
		}
	}

	public static function get_widget_type($widget_type_id) {
		if (is_numeric($widget_type_id)) {
			return CMS::Instance()->pdo->query('select * from widget_types where id=' . $widget_type_id)->fetch();
		}
		else {
			return false;
		}
	}

	public function save($required_details_form, $widget_options_form, $position_options_form) {
		// update this object with submitted and validated form info
		$redirect_url = Config::uripath() . '/admin/widgets/show';
		if (Input::getvar("http_referer_form") && Input::getvar("http_referer_form") != $_SERVER["HTTP_REFERER"]){
			$redirect_url = Input::getvar("http_referer_form");
		}
		$this->title = $required_details_form->get_field_by_name('title')->default;
		$this->state = $required_details_form->get_field_by_name('state')->default;
		$this->note = $required_details_form->get_field_by_name('note')->default;
		$this->options = array();
		foreach ($widget_options_form->fields as $option) {
			$obj = new stdClass();
			$obj->name = $option->name;
			$obj->value = $option->default;
			//$obj->{$option->name} = $option->default;
			$this->options[] = $obj;
		}
		// get position options fields
		$this->position_control = $position_options_form->get_field_by_name('position_control')->default;
		$this->global_position = $position_options_form->get_field_by_name('global_position')->default;
		$this->page_list = $position_options_form->get_field_by_name('position_pages')->default;

		if (method_exists($this, 'custom_save')) {
			// if child of widget class has custom save (for example it doesn't use cms forms)
			// custom save function is executed here
			// $this->options array can be added to for simple saving - see menu_widget
			$this->custom_save();
		}

		$options_json = json_encode($this->options);

		if ($this->id) {
			// update
			$params = array($this->state, $this->title, $this->note, $options_json, $this->position_control, $this->global_position, implode(',',$this->page_list), $this->id) ;
			$result = DB::exec("update widgets set state=?, title=?, note=?, options=?, position_control=?, global_position=?, page_list=? where id=?", $params);
			
			if ($result) {
				CMS::Instance()->queue_message("Widget <a href='" . Config::uripath() . "/admin/widgets/edit/{$this->id}'>{$this->title}</a> updated", 'success', $redirect_url);
			}
			else {
				CMS::Instance()->queue_message('Widget failed to save','danger',Config::uripath() . $_SERVER['REQUEST_URI']);	
			}
		}
		else {
			// new
			$params = array($this->state, $this->type_id, $this->title, $this->note, $options_json, $this->position_control, $this->global_position, implode(',',$this->page_list)) ;
			$result = DB::exec("insert into widgets (state,type,title,note,options,position_control,global_position,page_list) values(?,?,?,?,?,?,?,?)", $params);
			$new_widget_id = DB::get_last_insert_id();
			if ($result) {
				CMS::Instance()->queue_message("Widget <a href='" . Config::uripath() . "/admin/widgets/edit/{$new_widget_id}'>{$this->title}</a> created", 'success', $redirect_url);	
			}
			else {
				CMS::Instance()->queue_message('New widget failed to save','danger',Config::uripath() . $_SERVER['REQUEST_URI']);	
			}
		}
	}
}