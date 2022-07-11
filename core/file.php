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
		if (File::get_image_types()[$this->mimetype] >= 0 ) {
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


	public static function get_max_upload_size()  {  
		return min((ini_get('post_max_size')), (ini_get('upload_max_filesize')));  
	}  

	public static function get_max_upload_size_bytes()  {  
		return min(File::php_size_to_bytes(ini_get('post_max_size')), File::php_size_to_bytes(ini_get('upload_max_filesize')));    
	}

	
	public static function php_size_to_bytes($php_size) {
		$suffix = strtoupper(substr($php_size, -1));
		if (!in_array($suffix,array('P','T','G','M','K'))){
			return (int)$php_size;  
		} 
		$val = substr($php_size, 0, -1);
		switch ($suffix) {
			case 'P':
				$val *= 1024;
				// Fallthrough intended
			case 'T':
				$val *= 1024;
				// Fallthrough intended
			case 'G':
				$val *= 1024;
				// Fallthrough intended
			case 'M':
				$val *= 1024;
				// Fallthrough intended
			case 'K':
				$val *= 1024;
				break;
		}
		return (int)$val;
	}

	private function import_into_db() {
	}

	public static function get_image_types() {
		/*
			state 0: invalid
			state 1: valid+thumbnails
			state 2: valid+no thumbnails
		*/
		$image_types = [
			"image/jpeg" => 1,
			"image/webp" => 1,
			"image/png" => 1,
			"image/svg+xml" => 2,
			"image/svg" => 2,
			"image/gif" => 2
		];

		return $image_types;
	}
}