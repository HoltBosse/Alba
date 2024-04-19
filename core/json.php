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
						CMS::pprint_r('Error decoding JSON in file &ldquo;' . $file . '&rdquo;');
						CMS::pprint_r(debug_backtrace());
						die();
					} else {
						CMS::Instance()->show_error('JSON decode error');
					}
				}
				else {
					return $obj;
				}
			}
			if (Config::debug()) {
				CMS::pprint_r('Cannot read JSON file &ldquo;' . $file . '&rdquo;');
				CMS::pprint_r(debug_backtrace());
				die();
			} else {
				CMS::Instance()->show_error('JSON decode error');
			}
		}
		if (Config::debug()) {
			CMS::pprint_r('Cannot find JSON file &ldquo;' . $file . '&rdquo;');
			CMS::pprint_r(debug_backtrace());
			die();
		} else {
			CMS::Instance()->show_error('JSON decode error');
		}
	}
}