<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Repeatable extends Field {

	// $this->default = saved serialized json from repeated form 

	public $form_path;
	public $form;
	public $forms;
	public $repeatable;

	public function display() {
		/* 
			get default saved repeatable form stuff
			specifically loading $saved_data this way because olf how page view options are stored
		*/
		if(gettype($this->default)!="array") {
			$saved_data = json_decode($this->default);
		} elseif(gettype($this->default)=="array") {
			$saved_data = $this->default;
		}

		// get example repeatable for js rendering
		$this->form = new Form(CMSPATH . $this->form_path, true); 
		// loop over existing data and render
		$this->forms = [];

		echo "<div class='field'>";
			echo "<label for='{$this->id}' class='label'>";
			echo $this->label;
			echo "</label>";
			
			echo "<div class='repeated_forms_container' id='repeated_forms_container_{$this->form->id}'>";
		
		//null coalescing here to make page view repeatables work due to how they are saved
		if ($saved_data) {
			foreach ($saved_data as $repeatable_index=>$repeatable_form_data) {
				// load form
				$repeatable_form = new Form(CMSPATH . ($repeatable_form_data->form_path ?? $this->form_path), true); // second parameter is boolean for repeatable or not
				foreach (($repeatable_form_data->fields ?? $repeatable_form_data->value) as $field_info) {
					$field = $repeatable_form->get_field_by_name($field_info->name);
					if ($field) {
						//CMS::pprint_r ($field_info);
						$field->default = $field_info->default ?? $field_info->value;
					}
				}
				?>
				<div class='repeatable'>
					<button type='button' onclick='this.closest(".repeatable").remove();' class='button btn pull-right is-warning remove_repeater'>-</button>
					<button type='button' onclick='move_repeatable_up(this.closest(".repeatable"));' class='button btn pull-right is-info remove_repeater'>^</button>
					<button type='button' onclick='move_repeatable_down(this.closest(".repeatable"));' class='button btn pull-right is-info remove_repeater'>v</button>
					<?php
						ob_start();
						$repeatable_form->display_front_end();
						$rform_contents = ob_get_contents();
						ob_end_clean();
						$rform_contents = str_replace("{{repeatable_id_suffix}}", uniqid(), $rform_contents);
						$rform_contents = str_replace("{{replace_with_index}}", $repeatable_index, $rform_contents);
						echo $rform_contents;
					?>
				</div>
				<?php
			}
		}
		?>
		

		<?php
		
			echo "</div>"; // end repeatable form container
			
			echo "<button type='button' data-repeatable_template_var='repeatable_form_template_{$this->form->id}' id='add_repeater_{$this->form->id}' class='add_new_repeatable button btn is-primary'>+</button>";

			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>"; // end field
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
			$this->form->display_front_end(true); // pass true here to let form know it's for template/repeatable
			echo "</div>";
			$repeatable_template->markup = ob_get_contents();
			ob_end_clean(); // end temp buffering without outputting any of the form to browser / existing buffer
		?>
		window.repeatable_form_template_<?php echo $this->form->id;?> = <?php echo json_encode($repeatable_template); ?>;


		var add_repeater_<?php echo $this->form->id;?> = document.getElementById('add_repeater_<?php echo $this->form->id;?>');
		add_repeater_<?php echo $this->form->id;?>.addEventListener('click',function(e){
			// create new document fragment from template and add to repeater container
			var markup = window['repeatable_form_template_<?php echo $this->form->id;?>'].markup;
			var repeat_count = document.getElementById('repeated_forms_container_<?php echo $this->form->id;?>').querySelectorAll('div.repeatable').length;
			// insert index if required
			markup = markup.replace(/{{replace_with_index}}/g, repeat_count.toString());
			// insert unique id if required (image / slimselect js need unique ids for script)
			var unique_id_suffix = '_' + Math.random().toString(36).substr(2, 9);
			markup = markup.replace(/{{repeatable_id_suffix}}/g, unique_id_suffix);
			// create and insert node with markup
			var new_node = document.createRange().createContextualFragment(markup);
			var this_repeater = document.getElementById('repeated_forms_container_<?php echo $this->form->id;?>');
			this_repeater.appendChild(new_node);
		});

		function move_repeatable_up(el) {
			// todo: check if repeatable? should be, or null
			if (el.previousElementSibling) {
				el.parentNode.insertBefore(el, el.previousElementSibling);
			}
			else {
				alert('Already at top!');
			}
		}

		function move_repeatable_down(el) {
			if (el.nextElementSibling) {
				el.parentNode.insertBefore(el.nextElementSibling, el);
			}
			else {
				alert('Already at bottom!');
			}
		}
		</script>
		<?php

	}

	public function set_from_submit() {
		// create base repeatable form
		$forms=[];
		$repeatable_form = new Form(CMSPATH . $this->form_path, true); // must be true / repeatable
		$form_arr = Input::getvar('form_' . $repeatable_form->id, 'ARRAYRAW');
		if (is_array($form_arr)) {
			$repeat_count = sizeof ($form_arr);
		}
		else {
			$repeat_count=0;
		}
		// loop over this submitted repeatable and make sub-form for each element
		for ($n=0; $n<$repeat_count; $n++) {
			$repeatable_form = new Form(CMSPATH . $this->form_path, true);
			$repeatable_form->form_path = $this->form_path;
			// get info for field
			foreach ($repeatable_form->fields as $field) {
				$field->set_from_submit_repeatable($n);
			}
			$forms[] = $repeatable_form;
		}
		$this->forms = $forms;
		$this->default = json_encode($forms);
	}


	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->type = 'Repeatable';
		$this->form_path = $config->form_path ?? false;
		$this->repeatable = true; // always!!!
	}

	public function validate() {
		// assume $this->forms has been set by set_from_submit
		$all_valid=true;
		foreach ($this->forms as $subform) {
			if (!$subform->validate()) {
				$all_valid=false;
			}
		}
		return true;
		//return $all_valid;
	}
}
