<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$test_form = new Form();
//$test_form->load_json(); // load test form

if ($test_form->is_submitted()) {
	//CMS::pprint_r ($test_form);
	$test_form->set_from_submit();
	if ($test_form->validate()) {
		//CMS::pprint_r ($test_form);
		//echo "<h2>It's valid!</h2>";
		$json = $test_form->serialize_json();
		CMS::pprint_r ($json);
	}
	else {
		echo "<h2>It's INVALID!</h2>";
	}
}
else {
	echo "<h1>no form submitted</h1>";
}