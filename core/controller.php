<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Controller {
	public $path;
	public $view;

	public function __construct($path, $view) {
		$this->path = $path;
		$this->view = $view;
	}

	public function load_view ($view) {
		// first check folder exists
		// then load model (this then loads the view)
		$view_path = $this->path . "/views/" . $this->view;
		//CMS::pprint_r ($view_path);
		if (file_exists ($view_path)) {
			if (is_dir($view_path)) {
				// TODO: check for included files existing too
				include_once ($view_path . "/model.php");
				include_once ($view_path . "/view.php");
			}
		}
		else {
			CMS::Instance()->show_error ("View folder {$this->view} doesn't exist for controller at " . $this->path, 'error');
		}
	}
}