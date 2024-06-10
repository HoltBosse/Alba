<?php
defined('CMSPATH') or die; // prevent unauthorized access


$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$plugin_id = $segments[2];
	$plugin_info = CMS::Instance()->pdo->query('select * from plugins where id=' . $plugin_id)->fetch();
	$plugin_class_name = "Plugin_" . $plugin_info->location;
	$plugin = new $plugin_class_name($plugin_info);
}
else {
	CMS::Instance()->queue_message('Unknown plugin  operation','danger',Config::uripath().'/admin/plugins/show');
	exit(0);
}

// prep forms
$plugin_options_form = new Form(CMSPATH . '/plugins/' . $plugin->location . '/plugin_config.json');

// check if submitted or show defaults/data from db
if ($plugin_options_form->is_submitted()) {

	// update forms with submitted values
	$plugin_options_form->set_from_submit();

	/* CMS::pprint_r ($_POST);
	CMS::pprint_r ($position_options_form);
	exit(0); */

	// validate
	if ($plugin_options_form->validate()) {
		// forms are valid, save info
		$plugin->save($plugin_options_form);
	}
	else {
		CMS::Instance()->queue_message('Error saving plugin','danger',$_SERVER['REQUEST_URI']);	
	}
}
else {
	// set defaults if needed
	foreach ($plugin->options as $option) {
		//echo "$key => $value\n";
		$field = $plugin_options_form->get_field_by_name($option->name);
		if ($field) {
			$field->default = $option->value;
		}
		else {
			// do nothing, leave default from form json
		}
	}
	
}
