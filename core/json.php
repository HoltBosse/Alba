<?php
defined('CMSPATH') or die; // prevent unauthorized access

class JSON {
	static public function load_obj_from_file ($file) {
		if (is_file($file)) {
			if (is_readable($file)) {
				$json = file_get_contents($file);
				$obj = json_decode($json);
				if (!$obj) {
					if (Config::debug()) {
						CMS::show_error('Error decoding JSON in file &ldquo;' . $file . '&rdquo;');
					}
					else {
						CMS::show_error('JSON decode error');
					}
				}
				else {
					return $obj;
				}
			}
			if (Config::debug()) {
				CMS::show_error('Cannot read JSON file &ldquo;' . $file . '&rdquo;');
			}
			else {
				CMS::show_error('JSON decode error');
			}
		}
		if (Config::debug()) {
			CMS::show_error('Cannot find JSON file &ldquo;' . $file . '&rdquo;');
		}
		else {
			CMS::show_error('JSON decode error');
		}
	}
}