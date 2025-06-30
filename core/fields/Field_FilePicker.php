<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_FilePicker extends Field_Select {

	public $root_folder;
	public $mode;

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);

		$this->root_folder = $config->root_folder ? CMSPATH . "/" . $config->root_folder : CMSPATH;
		$this->mode = $config->mode ?: "file";
        if($this->mode != "file" && $this->mode!="folder") {
            $this->mode = "file";
        }
		$dir_data = [];
		if ($this->mode == "file") {
			$dir_data = array_filter(glob($this->root_folder . "/*", GLOB_MARK), function($path){return $path[-1] != '/';});
		} elseif ($this->mode == "folder") {
			$dir_data = glob($this->root_folder . "/*", GLOB_ONLYDIR);
		}

		foreach($dir_data as $file) {
			$this->select_options[] = (object) [
				'text' => basename($file),
				'value' => basename($file),
			];
		}
	}
}