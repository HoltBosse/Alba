<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$widget_id = $segments[2];
	$widget = new Widget();
	$widget->load($widget_id);
	$widget->get_type_object();
	$new_widget = false;
}
elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	$widget = new Widget();
	$widget->type_id = $segments[3];
	$new_widget = true;
	$widget->get_type_object();
}
else {
	CMS::Instance()->queue_message('Unkown widget operation','danger',Config::$uripath.'/admin/widgets/show');
	exit(0);
}

// prep forms
$required_details_form = new Form(CMSPATH . '/widgets/required_fields_form.json');
$position_options_form = new Form(CMSPATH . '/widgets/position_options_form.json');
$widget_options_form = new Form(CMSPATH . '/widgets/' . $widget->type->location . '/widget_config.json');

// old way - TODO: delete?
//$required_details_form->load_json(CMSPATH . '/widgets/required_fields_form.json');
//$widget_options_form->load_json(CMSPATH . '/widgets/' . $widget->type->location . '/widget_config.json');

// check if submitted or show defaults/data from db
if ($required_details_form->is_submitted()) {

	// update forms with submitted values
	$required_details_form->set_from_submit();
	$widget_options_form->set_from_submit();
	$position_options_form->set_from_submit();

	/* CMS::pprint_r ($_POST);
	CMS::pprint_r ($position_options_form);
	exit(0); */

	// validate
	if ($required_details_form->validate() && $widget_options_form->validate() && $position_options_form->validate()) {
		// forms are valid, save info
		$widget->save($required_details_form, $widget_options_form, $position_options_form);
	}
	else {
		CMS::Instance()->queue_message('Error saving widget','danger',$_SERVER['REQUEST_URI']);	
	}
	//CMS::Instance()->queue_message('Widget saved','success',Config::$uripath . '/admin/widgets/show');
}
else {
	// set defaults if needed
	if (!$new_widget) {
		$required_details_form->get_field_by_name('state')->default = $widget->state;
		$required_details_form->get_field_by_name('title')->default = $widget->title;
		$required_details_form->get_field_by_name('note')->default = $widget->note;
		// set options from json in db
		
		foreach ($widget->options as $option) {
			//echo "$key => $value\n";
			$field = $widget_options_form->get_field_by_name($option->name);
			if ($field) {
				$field->default = $option->value;
			}
			else {
				// do nothing, leave default from form json
			}
		}
		// set position defaults
		$position_options_form->get_field_by_name('position_control')->default = $widget->position_control;
		$position_options_form->get_field_by_name('global_position')->default = $widget->global_position;
		$position_options_form->get_field_by_name('position_pages')->set_value( $widget->page_list);
	}
	
}
