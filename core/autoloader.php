<?php
defined('CMSPATH') or die; // prevent unauthorized access

spl_autoload_register(function($class_name) {
	// get path to potential class file
	$is_field_class = strpos($class_name, "Field_");
	$is_widget_class = strpos($class_name, "Widget_");
	$is_user_class = strpos($class_name, "User_");
	$is_plugin_class = strpos($class_name, "Plugin_");
	$is_action_class = strpos($class_name, "Action_");

	if ($is_field_class===0) {
		$path = CMSPATH . "/core/fields/" . $class_name . ".php";
	}
	elseif ($is_widget_class===0) {
		$widget_class_type = str_replace('Widget_','',$class_name);
		$path = CMSPATH . "/widgets/" . $widget_class_type . "/widget_class.php";
	}
	elseif ($is_user_class===0) {
		$path = CMSPATH . "/user_classes/" . $class_name . ".php";
	}
	elseif ($is_plugin_class===0) {
		$plugin_class_location = str_replace('Plugin_','',$class_name);
		$path = CMSPATH . "/plugins/" . $plugin_class_location . "/plugin_class.php";
	}
	elseif($is_action_class===0) {
		$path = CMSPATH . "/core/actions/" . $class_name . ".php";
	}
	else {
		$path = CMSPATH . "/core/" . strtolower($class_name) . ".php";
	}
	/* if (!file_exists($path)) {
		// last ditch check if class in user_classes
		$path = CMSPATH . "/user_classes/" . $class_name . ".php";
		if (!file_exists($path)) {
			CMS::Instance()->show_error('Failed to autoload class: ' . $class_name);
		}
	} */
    if(file_exists($path)) {
        require_once($path);
    }
});