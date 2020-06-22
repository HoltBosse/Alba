<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Repeatable extends Field {

	// $this->default = saved serialized json from repeated form 

	public $form_path;

	public function display() {
		?>
		<style>
		.repeatable {
			border:2px solid #aaa;
			margin:1em;
			padding:1em;
		}
		</style>
		<?php
		echo "<div class='field'>";
			echo "<label for='{$this->id}' class='label'>";
			echo $this->name;
			echo "</label>";
			$this->form = new Form(CMSPATH . $this->form_path, true); // second parameter is boolean for repeatable or not
			//CMS::pprint_r ($this->form);
			$forms_array = json_decode($this->default);
			$repeat_count = sizeof($forms_array);
			for ($i=0; $i<$n; $i++) {
				// show repeated forms
			}
			// output empty repeatable input markup
			echo "<div class='repeatable'>";
				$this->form->display_front_end();
			echo "</div>";

			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}


	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->default = $config->default ?? false;
		$this->type = 'Repeatable';
		$this->form_path = $config->form_path ?? false;
		//$this->form = new Form($this->form_path);
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}