<?php
namespace HoltBosse\Alba\Widgets\Html;

Use HoltBosse\Alba\Core\Widget;

class Html extends Widget {
	public function render() {
		$normalizedOptions = array_combine(array_column($this->options, 'name'), array_column($this->options, 'value'));
		echo $normalizedOptions["markup"];
	}
}