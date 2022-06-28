<?php
defined('CMSPATH') or die; // prevent unauthorized access

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
		$template_folder = $CMS->page->template->folder;
		$controller_folder = $CMS->page->controller;
		$potential_override_model = CMSPATH . "/templates/" . $template_folder . "/overrides/" . $controller_folder . "/" . $this->view . "/model.php";
		$potential_override_view = CMSPATH . "/templates/" . $template_folder . "/overrides/" . $controller_folder . "/" . $this->view . "/view.php";
		if (file_exists($potential_override_model) && file_exists($potential_override_view)) {
			// override files exist, use those
			require ($potential_override_model);
			require ($potential_override_view);
		}
		else {
			// no override model/view, load default
			$view_path = $this->path . "/views/" . $this->view;
			//CMS::pprint_r ($view_path);
			if (file_exists ($view_path)) {
				if (is_dir($view_path)) {
					// TODO: check for included files existing too
					require ($view_path . "/model.php");
					require ($view_path . "/view.php");
				}
			}
			else {
				CMS::Instance()->show_error ("View folder {$this->view} doesn't exist for controller at " . $this->path, 'error');
			}
		}
	}
}