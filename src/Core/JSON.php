<?php
namespace HoltBosse\Alba\Core;

Use \Exception;

class JSON {
	static public function load_obj_from_file ($file) {
		if (is_file($file)) {
			if (is_readable($file)) {
				$json = file_get_contents($file);
				$obj = json_decode($json);
				if (!$obj) {
					$message = "JSON decode error";
					if ($_ENV["debug"]) {
						$message = 'Error decoding JSON in file &ldquo;' . $file . '&rdquo;';
					}

					throw new Exception($message);
				}
				else {
					return $obj;
				}
			}
			$message = "JSON decode error";
			if ($_ENV["debug"]) {
				$message = 'Cannot read JSON file &ldquo;' . $file . '&rdquo;';
			}

			throw new Exception($message);
		}

		$message = "JSON decode error";
		if ($_ENV["debug"]) {
			$message = 'Cannot find JSON file &ldquo;' . $file . '&rdquo;';
		}
		
		throw new Exception($message);
	}
}