<?php
namespace HoltBosse\Alba\Fields\FilePicker;

Use HoltBosse\Form\Fields\Select\Select;

class FilePicker extends Select {

	public mixed $root_folder = null;
	public mixed $mode = null;

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$this->root_folder = $config->root_folder;
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

		return $this;
	}
}