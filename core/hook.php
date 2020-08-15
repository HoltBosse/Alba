<?php
// guest, registered, author, editor, admin
defined('CMSPATH') or die; // prevent unauthorized access

class Hook {
	public $label;
    public $actions;
    public $arg_count;

	public function __construct($label, $arg_count=0) {
		$this->label = false;
		$this->actions = [];
		$this->arg_count = $arg_count;
    }
}