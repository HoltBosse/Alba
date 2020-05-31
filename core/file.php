<?php
defined('CMSPATH') or die; // prevent unauthorized access

class File {
	// id	type 1 = image, 2 = other file	width	height	title	alt	filename	mimetype
	public $id; 
	public $type;
	public $width;
	public $height;
	public $title;
	public $alt;
	public $filename;
	public $mimetype;

	

	function __construct($filepath="") {
		$this->id = null;
		$this->type = null;
		$this->width = null;
		$this->height = null;
		$this->alt = "";
		$this->title = "";
		$this->filename = "";
		if ($filepath && is_file($filepath)) {
			$this->filename = basename ($filepath);
			$this->mimetype = mime_content_type ($filepath);
			if ($this->is_image()) {
				$image_info = getimagesize($filepath);
				if (is_array($image_info)) {
					$this->width = $image_info[0];
					$this->height = $image_info[1];
				}
				else {
					// error getting image info
				}
			}
		}
		// TODO: 
	}

	public function is_image() {
		if ($this->mimetype=="image/jpeg" || $this->mimetype=="image/png") {
			return true;
		}
		else {
			return false;
		}
	}


	public function recalc_height($new_width) {
		$ratio = $new_width / (float)$this->width;
		$new_height = round($this->height * $ratio);
		$this->height = $new_height;
		$this->width = $new_width;
	}

	private function import_into_db() {
	}

}