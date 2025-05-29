<?php
defined('CMSPATH') or die; // prevent unauthorized access

class JSON {
	static public function load_obj_from_file ($file) {
		if (is_file($file)) {
			if (is_readable($file)) {
				$json = file_get_contents($file);
				$obj = json_decode($json);
				if (!$obj) {
					$messsage = "JSON decode error";
					if (Config::debug()) {
						$mssage = 'Error decoding JSON in file &ldquo;' . $file . '&rdquo;';
					}

					throw new Exception($message);
				}
				else {
					return $obj;
				}
			}
			$message = "JSON decode error";
			if (Config::debug()) {
				$message = 'Cannot read JSON file &ldquo;' . $file . '&rdquo;';
			}

			throw new Exception($message);
		}

		$message = "JSON decode error";
		if (Config::debug()) {
			$message = 'Cannot find JSON file &ldquo;' . $file . '&rdquo;';
		}
		
		throw new Exception($message);
	}
}