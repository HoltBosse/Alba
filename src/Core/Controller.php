<?php
namespace HoltBosse\Alba\Core;

Use HoltBosse\DB\DB;
Use \Exception;

class Controller {
	public $path;
	public $view;

	public function __construct($path, $view) {
		$this->path = $path;
		$this->view = $view;
	}

	public function load_view ($view, $controllervars=null) {
		// first check folder exists
		// then load model (this then loads the view)
		$CMS = CMS::Instance();
		// check for view template overrides first!
		// note - overrides must include view AND model otherwise it will be ignored
        if(!CMS::Instance()->isAdmin()) {
            $controller_path = Template::getTemplatePath($CMS->page->template->folder);
			$controller_folder = $CMS->page->controller;
            $potential_override_model = $controller_path . "/overrides/" . $controller_folder . "/" . $this->view . "/model.php";
            $potential_override_view = $controller_path . "/overrides/" . $controller_folder . "/" . $this->view . "/view.php";
        }
		if(isset($potential_override_model) && isset($potential_override_view) && file_exists($potential_override_model) && file_exists($potential_override_view)) {
			// override files exist, use those
			require ($potential_override_model);
			require ($potential_override_view);
		} else {
			// no override model/view, load default
			$view_path = $this->path . "/views/" . $this->view;
			
			if(!file_exists($view_path) && !CMS::Instance()->isAdmin()) {
				$controller_path = Template::getTemplatePath($CMS->page->template->folder);
				$view_path = $controller_path . "/overrides/" . $CMS->page->controller . "/" . $this->view;
			}

			if (file_exists ($view_path) && is_dir($view_path)) {
				// TODO: check for included files existing too
				require ($view_path . "/model.php");
				require ($view_path . "/view.php");
			} else {
				throw new Exception("View folder {$this->view} doesn't exist for controller at " . $this->path);
			}
		}
	}

	public function get_controller_config() {
		$ePath = explode("/", $this->path);
		$controllerLocation = $ePath[sizeof($ePath)-1];

		return DB::fetch("SELECT * FROM content_types WHERE controller_location = ?", $controllerLocation);
	}
}