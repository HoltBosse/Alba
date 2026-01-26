<?php
namespace HoltBosse\Alba\Core;

class File {
	// id	type 1 = image, 2 = other file	width	height	title	alt	filename	mimetype
	public $id; 
	public $type;
	public $width;
	public $height;
	public $title;
	public $alt;
	public $filename;
	public $filepath;
	public $mimetype;
	public $original_width;
	
	/*
		state 0: invalid
		state 1: valid+thumbnails
		state 2: valid+no thumbnails
	*/
	public static $image_types = [
		"image/jpeg" => 1,
		"image/webp" => 1,
		"image/png" => 1,
		"image/svg+xml" => 2,
		"image/svg" => 2,
		"image/gif" => 2
	];

	public static function get_mimetype_by_format($format) {
		// return mimetype when passed partial match
		// such as webp, jpeg or png
		foreach (File::$image_types as $key => $value) {
			if (explode("/",$key)[1]==$format) {
				return $key;
			}
		}
		return false;
	}

	function __construct($filepath="") {
		$this->id = null;
		$this->type = null;
		$this->width = null;
		$this->height = null;
		$this->alt = "";
		$this->title = "";
		$this->filename = "";
		$this->filepath = "";
		if ($filepath && is_file($filepath)) {
			$this->filepath = $filepath;
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
		if ($this::$image_types[$this->mimetype] >= 0 ) {
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
		if (!in_array($suffix,['P','T','G','M','K'])){
			return (int)$php_size;  
		} 
		$val = (int) substr($php_size, 0, -1);
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

}