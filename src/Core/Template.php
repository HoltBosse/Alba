<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \Exception;

class Template {
	public $id;
	public $title;
	public $folder;
	public $description;
	public $layout;
	public $page_id;
	public $positions; // array of tuples - position alias, position title

	private static $templateRegistry = [
		"basic" => __DIR__ . '/../templates/basic',
	];
	private static $adminTemplateRegistry = [
		"clean" => __DIR__ . '/../admin/templates/clean',
	];

	public function __construct($id=1) {
		if ($id===0) {
			$id = $this->get_default_template()->id;
		}
		$template = DB::fetch("SELECT * FROM templates WHERE id=?", $id);
		$this->id = $template->id;
		$this->title = $template->title;
		$this->folder = $template->folder;
		$this->description = $template->description;
		$this->positions = JSON::load_obj_from_file(realpath(self::$templateRegistry[$this->folder] . '/positions.json'))->positions;
		$this->page_id = null; // nominally used in back-end to inform template of page being edited for layout/position purposes
	}

	public static function registerTemplate(string $templateName, string $templatePath, bool $adminTemplate=false): bool {
		if($adminTemplate) {
			if (!isset(self::$adminTemplateRegistry[$templateName])) {
				self::$adminTemplateRegistry[$templateName] = $templatePath;

				return true;
			}
		} else {
			if (!isset(self::$templateRegistry[$templateName])) {
				self::$templateRegistry[$templateName] = $templatePath;
	
				return true;
			}
		}
		return false;
	}

	public static function registerTemplateDir(string $templateDirPath): void {
		foreach(glob($templateDirPath . '/*') as $file) {
			Template::registerTemplate(
				basename($file, '.php'),
				$file
			);
		}
	}

	public static function getTemplatePath(string $templateName, bool $adminTemplate=false): ?string {
		if ($adminTemplate) {
			if (isset(self::$adminTemplateRegistry[$templateName])) {
				return realpath(self::$adminTemplateRegistry[$templateName]);
			}
		} else {
			if (isset(self::$templateRegistry[$templateName])) {
				return realpath(self::$templateRegistry[$templateName]);
			}
		}
		return null;
	}

	public function get_position_title($position_alias) {
		for ($n=0; $n<sizeof($this->positions); $n++) {
			if ($this->positions[$n][0]==$position_alias) {
				return $this->positions[$n][1];
			}
		}
		return "";
	}

	static public function get_all_templates() {
		return DB::fetchAll(
			"SELECT * FROM templates WHERE (domain IS NULL OR domain=?)",
			[CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])]
		);
	}

	static public function get_default_template() {
		$result = DB::fetch(
			"SELECT *
			FROM templates
			WHERE is_default=1
			AND (domain IS NULL OR domain=?)
			LIMIT 1",
			[CMS::Instance()->isAdmin() ? $_SESSION["current_domain"] : CMS::getDomainIndex($_SERVER["HTTP_HOST"])]
		);
		if ($result) {
			return new Template($result->id);
		} else {
			throw new Exception('Failed to determine default template');
		}
	}

	public function output_widget_admin($position, $page_id) {
		// takes a position name (ostensibly given in a layout.php file within a template)
		// and lists/details widgets that are valid for current page/layout combination
		// if $this->page_id is null - probably creating a new page, so return any widgets 
		// that also have ALL pages and default position matching this one.
		// if page_id is set, and widget override in place, check page_id + position combo in override 
		// using ordering field in main widget table do determine position.

		// TODO: move to page controller? view?
		
		$all_global_widgets = Widget::get_widgets_for_position($page_id, $position);
		//CMS::pprint_r ($all_global_widgets);

		// return csv string of widget ids 
		$override_widgets = Widget::get_widget_overrides_csv_for_position ($page_id, $position);

		// TESTING pretend this position and page has override
		// specials page
		/* if ($page_id==19 && $position=="Above Content") {
			$override_widgets = "19,20,21,22";
		} */

		$default_active = " active ";
		$override_active = "";
		if ($override_widgets) {
			$default_active = "";
			$override_active = " active ";
		}

		echo "<div class='{$override_active} template_layout_widget_wrap'>";
		
			echo "<button type='button' class='addoverride button btn is-small is-light is-warning'>Override Position</button>";
			echo "<button type='button' class='removeoverride button btn is-small is-warning'>Remove Overrides</button>";
			echo "<h2><span class='position_name_label'>Position Name:</span><span class='position_name'>{$position}</span> <span class='widget_count'>(" . sizeof($all_global_widgets) . ")</span></h2>";
			echo "<div class='{$default_active} widget_controlled_tags tags position_tag_wrap'>";
			foreach ($all_global_widgets as $widget) {
				$state_class='is-info';
				$publish_note = "";
				if ($widget->state==0) {
					$state_class='is-danger is-light';
					$publish_note="<span class='lighter_note'> (unpublished)</span></a>";
				}
				echo "<a data-widgetid='$widget->id' href='".$_ENV["uripath"]."/admin/widgets/edit/".$widget->id."'><span class='tag {$state_class}'>" . $widget->title .  $publish_note . "</span></a>"  ;
			}
			echo "</div>";
			echo "<div class='{$override_active} position_tag_wrap override_tags_wrap'>";
				echo "<div class=' page_controller_tags tags '>";
					echo "<input type='hidden' name='override_positions[]' class='position_input' value='{$position}'>";
					echo "<input type='hidden' name='override_positions_widgets[]' class='position_widgets_input' value='{$override_widgets}'>"; // TODO: csv of positions in order
					$widget_list_array = explode(",",$override_widgets);
					if ($widget_list_array[0]=="") {
						$widget_list_array = false;
					}
					foreach ($widget_list_array as $widget_id) {
						echo "<span data-tagid='{$widget_id}' draggable='true' ondragover='dragover_tag_handler(event)' ondragend='dragend_tag_handler(event)' ondragstart='dragstart_tag_handler(event)' class='draggable_widget  is-warning tag'>".Widget::get_widget_title ($widget_id)."<span class='delete is-delete'>X</span></span>";	
					}
					
				echo "</div>";
				echo "<button class='add_override_widget button is-outline is-small' type='button'>Add Widget</button>";
			echo "</div>";
		echo "</div>";
	}
}