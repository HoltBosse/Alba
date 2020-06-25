<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Repeatable extends Field {

	// $this->default = saved serialized json from repeated form 

	public $form_path;

	public function display() {
		$this->form = new Form(CMSPATH . $this->form_path, true); // second parameter is boolean for repeatable or not
		?>
		<style>
		.repeatable {
			border:2px solid #aaa;
			margin:1em;
			padding:1em;
		}
		</style>

		<script>
		<?php
			// generate template for form repeatable and store in JS variable
			// render form
			$repeatable_template = new stdClass();
			$repeatable_template->markup="";
			ob_start(); // start new output buffer to escape any backticks / string literals inside form display - image field has LOTS
			?>
			<div class='repeatable'><button type='button' onclick='this.closest(".repeatable").remove();' class='button btn pull-right is-warning remove_repeater'>-</button>
			<?php
			$this->form->display_front_end(); 
			echo "</div>";
			$repeatable_template->markup = ob_get_contents();
			ob_end_clean(); // end temp buffering without outputting any of the form to browser / existing buffer
		?>
		window.repeatable_form_template_<?php echo $this->form->id;?> = <?php echo json_encode($repeatable_template); ?>;
		</script>

		<?php
		echo "<div class='field'>";
			echo "<label for='{$this->id}' class='label'>";
			echo $this->label;
			echo "</label>";
			
			echo "<div class='repeated_forms_container' id='repeated_forms_container_{$this->form->id}'>";
			echo "</div>";
			
			echo "<button type='button' data-repeatable_template_var='repeatable_form_template_{$this->form->id}' id='add_repeater_{$this->form->id}' class='add_new_repeatable button btn is-primary'>+</button>";

			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
		?>
		<script>

		var add_repeater_<?php echo $this->form->id;?> = document.getElementById('add_repeater_<?php echo $this->form->id;?>');
		add_repeater_<?php echo $this->form->id;?>.addEventListener('click',function(e){
			// create new document fragment from template and add to repeater container
			var markup = window['repeatable_form_template_<?php echo $this->form->id;?>'].markup;
			var repeat_count = document.getElementById('repeated_forms_container_<?php echo $this->form->id;?>').querySelectorAll('div.repeatable').length;
			// insert index if required
			markup = markup.replace('{{replace_with_index}}', repeat_count.toString());
			// insert unique id if required (image / slimselect js need unique ids for script)
			var unique_id_suffix = '_' + Math.random().toString(36).substr(2, 9);
			markup = markup.replace(/{{repeatable_id_suffix}}/g, unique_id_suffix);
			// create and insert node with markup
			var new_node = document.createRange().createContextualFragment(markup);
			var this_repeater = document.getElementById('repeated_forms_container_<?php echo $this->form->id;?>');
			this_repeater.appendChild(new_node);
		});
		</script>
		<?php

	}

	public function set_from_submit() {
		// create base repeatable form
		$forms=[];
		$repeatable_form = new Form(CMSPATH . $this->form_path);
		$repeat_count = sizeof (CMS::getvar('form_' . $repeatable_form->id, 'ARRAYRAW'));
		// loop over this submitted repeatable and make sub-form for each element
		for ($n=0; $n<$repeat_count; $n++) {
			$repeatable_form = new Form(CMSPATH . $this->form_path);
			// get info for field
			foreach ($repeatable_form->fields as $field) {
				$field->set_from_submit_repeatable($n);
			}
			$forms[] = $repeatable_form;
		}
		$this->forms = $forms;
		
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
		// assume $this->forms has been set by set_from_submit
		$all_valid=true;
		foreach ($this->forms as $subform) {
			if (!$subform->validate()) {
				$all_valid=false;
			}
		}
		return $all_valid;
	}
}