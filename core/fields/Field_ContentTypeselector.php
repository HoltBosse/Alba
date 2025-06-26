<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentTypeselector extends Field {

	public $showmedia;
	public $showusers;
	public $maxlength;
	public $minlength;

	public function display() {
		$all_contenttypes = Content::get_all_content_types();
		/* CMS::pprint_r ($this); */
		echo "<style>label.checkbox {display:block; margin-bottom:1rem;} label.checkbox input {margin-right:1rem;}</style>";
		if ($this->label) {
			echo "<label class='label '>{$this->label}</label>";
		}
		echo "<hr>";
		echo "<div class='field'>";
		$checked = "";
			// check for special content types - just media/images (-1) for now, but maybe more
			// ok we added users here too 
			if ($this->showmedia) {
				$checked = "";
				if ($this->default && in_array("-1", $this->default)) {
					$checked = " checked ";
				}
				echo "<label class='checkbox'>";
					echo "<input {$checked} type='checkbox' {$this->get_rendered_name(true)} {$this->get_rendered_form()} value='-1'>";
				echo "Media/Images</label>";
				echo "<hr>";
			}
			if ($this->showusers) {
				$checked = "";
				if ($this->default && in_array("-2", $this->default)) {
					$checked = " checked ";
				}
				echo "<label class='checkbox'>";
					echo "<input {$checked} type='checkbox' {$this->get_rendered_name(true)} {$this->get_rendered_form()} value='-2'>";
				echo "Users</label>";
				echo "<hr>";
			}
			
			foreach ($all_contenttypes as $type) {
				echo "<label class='checkbox'>";
					$checked = "";
					if ($this->default && in_array($type->id, $this->default)) {
						$checked = " checked ";
					}
					echo "<input {$checked} type='checkbox' {$this->get_rendered_name(true)} {$this->get_rendered_form()} value='{$type->id}'>";
					echo $type->title;
				echo "</label>";
			}
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}

	public function set_from_submit() {
		// override default field function
		$value = Input::getvar($this->name, $this->filter);
		if ($value||is_numeric($value)) {
			$this->default = $value;
		}
		else {
			$this->default = [];
		}
	}

	public function set_value($value) {
		if (is_array($value)) {
			$this->default = $value;
		}
		else {
			$this->default = explode(',',$value);
		}
	}

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->showmedia = $config->showmedia ?? true;
		$this->showusers = $config->showusers ?? false;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
