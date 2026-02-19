<?php

Use HoltBosse\Alba\Core\{CMS, Plugin};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Input, Form};

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$plugin_id = $segments[2];
	$plugin_info = DB::fetch('SELECT * FROM plugins WHERE id=?', $plugin_id);

	if(!$plugin_info) {
		CMS::Instance()->queue_message('Failed to load plugin id: ' . $plugin_id, 'danger',$_ENV["uripath"].'/admin/plugins/show');
	}

	$plugin_class_name = Plugin::getPluginClass($plugin_info->location);
	$plugin = new $plugin_class_name($plugin_info);
}
else {
	CMS::Instance()->queue_message('Unknown plugin  operation','danger',$_ENV["uripath"].'/admin/plugins/show');
	exit(0);
}

// prep forms
$plugin_options_form = new Form(Plugin::getPluginPath($plugin->location) . '/plugin_config.json');

// check if submitted or show defaults/data from db
if ($plugin_options_form->isSubmitted()) {

	// update forms with submitted values
	$plugin_options_form->setFromSubmit();

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
	foreach (($plugin->options ?? []) as $option) {
		//echo "$key => $value\n";
		$field = $plugin_options_form->getFieldByName($option->name);
		if ($field) {
			$field->default = $option->value;
		}
		else {
			// do nothing, leave default from form json
		}
	}
	
}
