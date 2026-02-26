<?php
namespace HoltBosse\Alba\Fields\FilePicker;

Use HoltBosse\Form\Fields\Select\Select;
Use HoltBosse\Alba\Core\File;
Use \stdClass;
Use \Exception;

class FilePicker extends Select {

	public mixed $root_folder = null;
	public mixed $mode = null;

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$this->root_folder = $config->root_folder ?? throw new Exception("root folder for filepicker is required");
		$this->mode = isset($config->mode) ? $config->mode : "file";
        if($this->mode != "file" && $this->mode!="folder") {
            $this->mode = "file";
        }
		$dir_data = [];
		if ($this->mode == "file") {
			$dir_data = array_filter(File::glob($this->root_folder . "/*", GLOB_MARK), function($path){return $path[-1] != '/';});
		} elseif ($this->mode == "folder") {
			$dir_data = File::glob($this->root_folder . "/*", GLOB_ONLYDIR);
		}

		foreach($dir_data as $file) {
			$this->select_options[] = (object) [
				'text' => basename($file),
				'value' => basename($file),
			];
		}

		return $this;
	}
}