<?php
// guest, registered, author, editor, admin
defined('CMSPATH') or die; // prevent unauthorized access

class Hook {
	public $label;
    public $actions;
    public $arg_count;

	public function __construct($label) {
		$this->label = false;
		$this->actions = [];
	}

	static public function count_actions_for_hook ($hook_label) {
		if (isset($GLOBALS['hooks'][$hook_label])) {
			return sizeof($GLOBALS['hooks'][$hook_label]->actions);
		}
		return (0);
	}

	
	static public function execute_hook_actions ($hook_label, ...$args) {
		if (isset($GLOBALS['hooks'][$hook_label])) {
			foreach ($GLOBALS['hooks'][$hook_label]->actions as $action) {
				/* $function_name = $action->function_name;
				$function_name($args); */
				//$action->plugin_object_function($args);
				$function_name = $action->function_name;
				$action->plugin_object->$function_name($args);
			}
		}
		return true; // for now assume all good and that plugin(s) will handle execution termination if error happens
	}

	static public function execute_hook_filters ($hook_label, $data, ...$args) {
		// same as action, but performs work on data and returns data
		if (isset($GLOBALS['hooks'][$hook_label])) {
			foreach ($GLOBALS['hooks'][$hook_label]->actions as $action) {
				//CMS::pprint_r ($action);
				//$data = $function_name($data, $args);
				//$data = $action->plugin_object_function($data,$args);
				$function_name = $action->function_name;
				$data = $action->plugin_object->$function_name($data, $args);
			}
		}
		return $data;
	}
}