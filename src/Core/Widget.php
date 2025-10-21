<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Core\{Hook, JSON};
Use HoltBosse\Form\{Form, Input};
Use \stdClass;

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

    private static $widgetRegistry = [
		"Html" => [
			"path" => __DIR__ . '/../Widgets/Html',
			"class" => "HoltBosse\\Alba\\Widgets\\Html\\Html",
		],
        "Menu" => [
			"path" => __DIR__ . '/../Widgets/Menu',
			"class" => "HoltBosse\\Alba\\Widgets\\Menu\\Menu",
		],
		"FormInstance" => [
			"path" => __DIR__ . '/../Widgets/FormInstance',
			"class" => "HoltBosse\\Alba\\Widgets\\FormInstance\\FormInstance",
		],
	];

    public static function registerWidget(string $widgetName, string $widgetPath, string $widgetClass): bool {
		if (!isset(self::$widgetRegistry[$widgetName])) {
			self::$widgetRegistry[$widgetName] = [
				"path" => $widgetPath,
				"class" => $widgetClass
			];

			return true;
		}

		return false;
	}

	public static function registerWidgetDir(string $widgetDirPath, string $widgetClassBase): void {
		foreach(glob($widgetDirPath . '/*') as $file) {
			Widget::registerWidget(
				basename($file, '.php'),
				$file,
				$widgetClassBase . basename($file, '.php') . "\\" . basename($file, '.php')
			);
		}
	}

	public static function getWidgetPath(string $widgetName): ?string {
		if (isset(self::$widgetRegistry[$widgetName])) {
			return realpath(self::$widgetRegistry[$widgetName]['path']);
		}

		return null;
	}

	public static function getWidgetClass(string $widgetName): ?string {
		if (isset(self::$widgetRegistry[$widgetName])) {
			return self::$widgetRegistry[$widgetName]['class'];
		}

		return null;
	}

	public static function getWidgetNames(): array {
		return array_keys(self::$widgetRegistry);
	}

	public function render_edit() {
		if (CMS::Instance()->user->is_member_of(1) && !CMS::Instance()->isAdmin()) { ?>
			<div class='front_end_edit_wrap' >
				<a style='' target="_blank" href='/admin/widgets/edit/<?php echo $this->id;?>'>EDIT &ldquo;<?= Input::stringHtmlSafe($this->title); ?>&rdquo;</a>
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
		return DB::fetchAll('SELECT * FROM widget_types');
	}

	public static function get_widgets_for_position($page_id, $position) {
		$query = 'SELECT id,title,type,state 
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
		$widget_ids = DB::fetch("SELECT widgets from page_widget_overrides where page_id=? and position=?", [$page, $position])->widgets ?? null; // csv
		return $widget_ids;
	}

	public static function get_widget_overrides_for_position ($page, $position) {
		//echo "<h1>checking page {$page} position {$position}</h1>";
		$widget_ids = DB::fetch("SELECT widgets from page_widget_overrides where page_id=? and position=?", [$page, $position])->widgets ?? null; // csv
		if ($widget_ids) {
			$query = 'SELECT id,title,type,state 
			from widgets 
			where id in ('.$widget_ids.') and state>=0 
			ORDER BY FIELD(id,'.$widget_ids.')';
			return DB::fetchAll($query);
		}
		return false;
	}

	public function hasCustomBackend(): bool {
		return false;
	}

	public function render_custom_backend() {
		return false;
	}

	public function load($id, $type_id=null) {
		//id of -1 is a new widget
		if($id!=-1) {
			$info = DB::fetch('SELECT * FROM widgets WHERE id=?', $id);
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

		$this->type = DB::fetch('SELECT * FROM widget_types WHERE id=?', $this->type_id);
		$this->form_data = JSON::load_obj_from_file(Widget::getWidgetPath($this->type->location) . '/widget_config.json');

		Hook::execute_hook_actions('on_widget_load', $this, "thinggg");
	}

	public static function get_widget_type_title($widget_type_id) {
		if (is_numeric($widget_type_id)) {
			return DB::fetch('SELECT title FROM widget_types WHERE id=?', $widget_type_id)->title;
		}
		else {
			return false;
		}
	}

	public static function get_widget_type($widget_type_id) {
		if (is_numeric($widget_type_id)) {
			return DB::fetch('SELECT * FROM widget_types WHERE id=?', $widget_type_id);
		}
		else {
			return false;
		}
	}

	public function save($required_details_form, $widget_options_form, $position_options_form) {
		// update this object with submitted and validated form info
		$redirect_url = $_ENV["uripath"] . '/admin/widgets/show';
		if (Input::getvar("http_referer_form") && Input::getvar("http_referer_form") != $_SERVER["HTTP_REFERER"]){
			$redirect_url = Input::getvar("http_referer_form");
		}
		$this->title = $required_details_form->getFieldByName('title')->default;
		$this->state = $required_details_form->getFieldByName('state')->default;
		$this->note = $required_details_form->getFieldByName('note')->default;
		$this->options = [];
		foreach ($widget_options_form->fields as $option) {
			$obj = new stdClass();
			$obj->name = $option->name;
			$obj->value = $option->default;
			//$obj->{$option->name} = $option->default;
			$this->options[] = $obj;
		}
		// get position options fields
		$this->position_control = $position_options_form->getFieldByName('position_control')->default;
		$this->global_position = $position_options_form->getFieldByName('global_position')->default;
		$this->page_list = $position_options_form->getFieldByName('position_pages')->default;

		if (method_exists($this, 'custom_save')) {
			// if child of widget class has custom save (for example it doesn't use cms forms)
			// custom save function is executed here
			// $this->options array can be added to for simple saving - see menu_widget
			$this->custom_save();
		}

		$options_json = json_encode($this->options);

		if ($this->id) {
			// update
			$params = [$this->state, $this->title, $this->note, $options_json, $this->position_control, $this->global_position, implode(',',$this->page_list), $this->id] ;
			$result = DB::exec("update widgets set state=?, title=?, note=?, options=?, position_control=?, global_position=?, page_list=? where id=?", $params);
			
			if ($result) {
				CMS::Instance()->queue_message("Widget <a href='" . $_ENV["uripath"] . "/admin/widgets/edit/{$this->id}'>{$this->title}</a> updated", 'success', $redirect_url);
			}
			else {
				CMS::Instance()->queue_message('Widget failed to save','danger',$_ENV["uripath"] . $_SERVER['REQUEST_URI']);	
			}
		} else {
			// new
			$params = [$this->state, $this->type_id, $this->title, $this->note, $options_json, $this->position_control, $this->global_position, implode(',',$this->page_list)] ;
			$result = DB::exec("INSERT into widgets (state,type,title,note,options,position_control,global_position,page_list) values(?,?,?,?,?,?,?,?)", $params);
			$new_widget_id = DB::getLastInsertedId();
			if ($result) {
				CMS::Instance()->queue_message("Widget <a href='" . $_ENV["uripath"] . "/admin/widgets/edit/{$new_widget_id}'>{$this->title}</a> created", 'success', $redirect_url);	
			}
			else {
				CMS::Instance()->queue_message('New widget failed to save','danger',$_ENV["uripath"] . $_SERVER['REQUEST_URI']);	
			}
		}
	}
}