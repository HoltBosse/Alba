<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==3 && is_numeric($segments[2])) {
	// edit existing widget id is segment 2
	$widget_id = $segments[2];
	// create temp base class widget to get type
	$temp_widget = new Widget();
	$temp_widget->load($widget_id);
	$type_info = Widget::get_widget_type($temp_widget->type_id);
	// create actual new widget of class determined by type
	$widget_class_name = "Widget_" . $type_info->location;
	$widget = new $widget_class_name();
	$widget->load($widget_id);
	$new_widget = false;
}
elseif(sizeof($segments)==4 && $segments[2]=='new' && is_numeric($segments[3])) {
	// get widget type and create object of correct class
	$type_info = Widget::get_widget_type($segments[3]);
	$widget_class_name = "Widget_" . $type_info->location;
	$widget = new $widget_class_name();
	$new_widget = true;
	$widget->load(-1, $segments[3]);
}
else {
	CMS::Instance()->queue_message('Unknown widget operation','danger',Config::uripath().'/admin/widgets/show');
	exit(0);
}

// prep forms
$required_details_form = new Form(CMSPATH . '/admin/controllers/widgets/views/edit/required_fields_form.json');
$position_options_form = new Form(CMSPATH . '/admin/controllers/widgets/views/edit/position_options_form.json');
$widget_options_form = new Form($widget->form_data);

// old way - TODO: delete?
//$required_details_form->load_json(CMSPATH . '/widgets/required_fields_form.json');
//$widget_options_form->load_json(CMSPATH . '/widgets/' . $widget->type->location . '/widget_config.json');

// check if submitted or show defaults/data from db
if ($required_details_form->isSubmitted()) {

	// update forms with submitted values
	$required_details_form->setFromSubmit();
	$widget_options_form->setFromSubmit();
	$position_options_form->setFromSubmit();

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
	//CMS::Instance()->queue_message('Widget saved','success',Config::uripath() . '/admin/widgets/show');
}
else {
	// set defaults if needed
	if (!$new_widget) {
		$required_details_form->getFieldByName('state')->default = $widget->state;
		$required_details_form->getFieldByName('title')->default = $widget->title;
		$required_details_form->getFieldByName('note')->default = $widget->note;
		// set options from json in db
		
		foreach ($widget->options as $option) {
			//echo "$key => $value\n";
			$field = $widget_options_form->getFieldByName($option->name);
			if ($field) {
				$field->default = $option->value;
			}
			else {
				// do nothing, leave default from form json
			}
		}
		// set position defaults
		$position_options_form->getFieldByName('position_control')->default = $widget->position_control;
		$position_options_form->getFieldByName('global_position')->default = $widget->global_position;

		$position_page_field_default = $widget->page_list;
		if (!is_array($widget->page_list)) {
			$position_page_field_default = explode(',',$widget->page_list);
		}
		$position_options_form->getFieldByName('position_pages')->default = $position_page_field_default;
	}
	
}
