<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Widget_html extends Widget {
	public function render() {
		$normalizedOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
		echo $normalizedOptions["markup"];
	}
}