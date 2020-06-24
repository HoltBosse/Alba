<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$test_form = new Form();
//$test_form->load_json(); // load test form

if ($test_form->is_submitted()) {
	echo "<h1>got data!</h1>";
	CMS::pprint_r ($_POST);
	CMS::pprint_r ($test_form);
	if ($test_form->validate()) {
		echo "<h2>It's valid!</h2>";
	}
	else {
		echo "<h2>It's INVALID!</h2>";
	}
}
else {
	echo "<h1>no form submitted</h1>";
}