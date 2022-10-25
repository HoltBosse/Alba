<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;

$user_options_form = new Form(CMSPATH . "/admin/forms/user_options.json");
// create configuration based on form
$user_options_config = new Configuration($user_options_form); 
// load from db
$user_options_config->load_from_db();

$submitted = Input::getvar('form_user_options');

if ($submitted) {
	// update form with submitted values
	$user_options_form->set_from_submit();
	// create new config object based on updated form and save
	$user_options_config = new Configuration($user_options_form); 
	
	$saved = $user_options_config->save();
	if ($saved) {
		CMS::Instance()->queue_message('Options saved','success',Config::uripath()."/admin/users/options");
	}
	else {
		CMS::Instance()->queue_message('Error saving user options','danger',Config::uripath()."/admin/users/options");
	}
}
