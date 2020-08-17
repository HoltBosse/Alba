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
	
	static public function execute_hook_actions ($hook_label, ...$args) {
		if (isset(CMS::Instance()->hooks[$hook_label])) {
			foreach (CMS::Instance()->hooks[$hook_label]->actions as $action) {
				$function_name = $action->function_name;
				$function_name($args);
			}
		}
	}

	static public function execute_hook_filters ($hook_label, $data, ...$args) {
		// same as action, but performs work on data and returns data
		if (isset(CMS::Instance()->hooks[$hook_label])) {
			foreach (CMS::Instance()->hooks[$hook_label]->actions as $action) {
				$function_name = $action->function_name;
				$data = $function_name($data, $args);
			}
		}
		return $data;
	}
}