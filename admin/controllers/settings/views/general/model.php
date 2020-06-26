<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;

$general_options_form = new Form(CMSPATH . "/admin/forms/general_options.json");
// create configuration based on form
$general_options_config = new Configuration($general_options_form); 
// load from db
$general_options_config->load_from_db();

$submitted = Input::getvar('form_general_options');

if ($submitted) {
	// update form with submitted values
	$general_options_form->set_from_submit();
	// create new config object based on updated form and save
	$general_options_config = new Configuration($general_options_form); 
	
	$saved = $general_options_config->save();
	if ($saved) {
		CMS::Instance()->queue_message('Options saved','success',Config::$uripath."/admin/settings/general");
	}
	else {
		CMS::Instance()->queue_message('Error saving general options','danger',Config::$uripath."/admin/settings/general");
	}
}
