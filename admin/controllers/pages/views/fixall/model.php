<?php
defined('CMSPATH') or die; // prevent unauthorized access

$pages = DB::fetchAll('select * from pages');

function fix_bad_serialization($bad_json) {
	// take bad json
	// return better json
	$bad_obj = json_decode($bad_json);
	$good_obj = new stdClass();
	
	if ( $bad_obj != new stdClass() ) {
		// not empty		
		foreach ($bad_obj as $bad_option) {
			$name = $bad_option->name ?? false;
			if ($name) {
				if (!property_exists($bad_option, "name")) {
					// already fixed
					continue;
				}
				if ($bad_option->name==="error!!!") {
					continue;
				}
				$good_obj->{$bad_option->name} = $bad_option->value;
			}
		}
	}
	return json_encode($good_obj);
}

?>
