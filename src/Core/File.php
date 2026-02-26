<?php
namespace HoltBosse\Alba\Core;

use \Exception;

class File {
	// id	type 1 = image, 2 = other file	width	height	title	alt	filename	mimetype
	public mixed $id; 
	public mixed $type;
	public ?float $width;
	public ?float $height;
	public string $title;
	public string $alt;
	public string $filename;
	public string $filepath;
	public string $mimetype;
	public float $original_width;
	
	/*
		state 0: invalid
		state 1: valid+thumbnails
		state 2: valid+no thumbnails
	*/
	// @phpstan-ignore missingType.iterableValue
	public static array $image_types = [
		"image/jpeg" => 1,
		"image/webp" => 1,
		"image/png" => 1,
		"image/svg+xml" => 2,
		"image/svg" => 2,
		"image/gif" => 2
	];

	public static function get_mimetype_by_format(string $format): ?string {
		// return mimetype when passed partial match
		// such as webp, jpeg or png
		foreach (File::$image_types as $key => $value) {
			if (explode("/",$key)[1]==$format) {
				return $key;
			}
		}
		return null;
	}

	function __construct(string $filepath="") {
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
			$this->mimetype = (string) mime_content_type ($filepath);
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

	//wrapper to do modern php exceptions rather than old php strings|false
	public static function getContents(string $filename, bool $use_include_path = false, mixed $context = null, int $offset = 0, ?int $length = null): string {
		if($length!==null) {
			$length = max(0, $length);
		}
		$output = file_get_contents($filename, $use_include_path, $context, $offset, $length);
		if($output===false) {
			throw new Exception("Failed to get contents of file: $filename");
		}
		return $output;
	}

	//wrapper to do modern php exceptions rather than old php strings|false
	public static function realpath(string $filename): string {
		$output = realpath($filename);
		if($output===false) {
			throw new Exception("Failed to get realpath of file: $filename");
		}
		return $output;
	}

	// @phpstan-ignore missingType.iterableValue
	public static function glob(string $pattern, int $flags = 0): array {
		$output = glob($pattern, $flags);
		if($output===false) {
			throw new Exception("Failed to glob files with pattern: $pattern");
		}
		return $output;
	}

	public function is_image(): bool {
		if ($this::$image_types[$this->mimetype] >= 0 ) {
			return true;
		}
		else {
			return false;
		}
	}


	public function recalc_height(int $new_width): void {
		$ratio = $new_width / (float)$this->width;
		$new_height = round($this->height * $ratio);
		$this->height = $new_height;
		$this->width = $new_width;
	}


	public static function get_max_upload_size(): int {  
		return (int) min((ini_get('post_max_size')), (ini_get('upload_max_filesize')));  
	}  

	public static function get_max_upload_size_bytes(): int  {  
		return (int) min(File::php_size_to_bytes((string) ini_get('post_max_size')), File::php_size_to_bytes((string) ini_get('upload_max_filesize')));    
	}

	public static function php_size_to_bytes(string $php_size): int {
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