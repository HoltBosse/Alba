<?php
namespace HoltBosse\Alba\Widgets\Html;

Use HoltBosse\Alba\Core\Widget;

class Html extends Widget {
	public function render(): void {
		echo $this->objectOptions->markup;
	}
}