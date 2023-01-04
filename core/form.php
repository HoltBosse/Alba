<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Form {
	public $fields;
	public $repeatable;

	function __construct($path = CMSPATH . "/testform.json", $repeatable=false) {
		$this->fields = array();
		$this->repeatable = $repeatable;
		$this->load_json($path);
	}

	public function load_json($path = CMSPATH . "/testform.json") {
		$obj;
		if (gettype($path)=="object") {
			$obj = $path;
		} elseif (is_file($path)) {
			$this->json = file_get_contents($path);
			$obj = json_decode($this->json);
		} else {
			echo "<h5>File {$path} not found or invalid data passed</h5>";
			if (Config::debug()) {
				echo "<p class='help'>Called from /core/form.php load_json function</p>";
			}
		}

		if ($obj) {
			$tempfields = $obj->fields;
			$this->id = $obj->id;
			
			foreach ($tempfields as $field_config) {
				$class = "Field_" . $field_config->type;
				$thisfield = new $class();
				$thisfield->load_from_config($field_config);
				if ($this->repeatable) {
					$thisfield->in_repeatable_form = true;
				}
				if (property_exists($field_config,'name')) {
					// not all form fields require name property - only saveable items
					// HTML field / Tab field etc are for rendering only
					$this->fields[$field_config->name] = $thisfield;
				}
				else {
					$this->fields[] = $thisfield;
				}
			}
		}
	}

	public function set_from_submit() {
		foreach ($this->fields as $field) {
			$field->set_from_submit();
		}
	}

	public function get_field_by_name($field_name) {
		if (isset($this->fields[$field_name])) {
			return $this->fields[$field_name];
		}
		else {
			if (Config::debug()) {
				CMS::log('Unable to load form field ' . $field_name);
			}
		}
		return false;
	}

	public function is_submitted() {
		if ($this->id) {
			$form_name = Input::getvar("form_" . $this->id, "TEXT");
			if ($form_name) {
				return true;
			}
		}
		return false;
	}

	public function validate() {
		foreach ($this->fields as $field) {
			if (!$field->validate()) {
				$field_info = print_r ($field,true);
				CMS::Instance()->log('Invalid field: ' . $field_info);
				CMS::Instance()->queue_message('Invalid field: ' . ($field->label ?? $field->name),'danger');
				return false;
			}
		}
		return true;
	}

	public function serialize_json() {
		$name_value_pairs = array();
		foreach ($this->fields as $field) {
			$pair = new stdClass();
			$pair->name = $field->name;
			if ($field->type=="Repeatable") {
				// loop through each repeatable form and each field inside each form
				// creating tuples for each
				$sub_form_value_array=[];
				foreach ($field->forms as $sub_form) {
					$sub_pair = new stdClass();
					$sub_pair->name = $sub_form->id;
					$sub_values = [];
					foreach ($sub_form->fields as $sub_form_field) {
						$sub_field_pair = new stdClass();
						$sub_field_pair->name = $sub_form_field->name;
						$sub_field_pair->value = $sub_form_field->default;
						$sub_values[] = $sub_field_pair;
					}
					$sub_pair->value = $sub_values;
					$sub_form_value_array[] = $sub_pair;
				}
				$pair->value = $sub_form_value_array;
			}
			else {
				$pair->value = $field->default;
			}
			$name_value_pairs[] = $pair;
		}
		return json_encode ($name_value_pairs);
	}

	public function deserialize_json($json) {
		$json_obj = json_decode($json);
		if ($json_obj) {
			foreach ($json_obj as $option) {
				if ($option->name!=='error!!!') {
					$field = $this->get_field_by_name($option->name); 
					if (is_object($field)) {
						$field->default = $option->value;
					}
				}
				else {
					// keep going - other fields exist maybe :)
					continue;
				}
			}
		}
	}

	public function display_front_end($repeatable_template=false) {
		
		// first make sure array added to name if required
		$aftername='';
		if ($this->repeatable) {
			$aftername="[]";
		}

		// todo: move to admin template?
		echo "<style>.logic_hide {display:none;}</style>";

		echo "<div class='form_contain' id='" . $this->id . "'>";

			// loop through fields and call display();
			foreach ($this->fields as $field) {
				if (!property_exists($field,'nowrap')) {
					// wrapped field
					// prepare logic data attribute
					$logic = $field->logic;
					$logic_json = json_encode($logic);
					$wrapclass = $field->wrapclass ?? "";
					if ($logic) {
						$wrapclass .= " haslogic";
					}
					// prepare required data attribute
					// (remember if element is required or not)
					$req = $field->required ?? false;
					$req_data = "";
					if ($req) {
						$req_data = " data-required='true' ";
					}
					echo "<div data-logic='{$logic_json}' $req_data class='{$wrapclass} form_field field field_id_{$field->id}'>";
				}
				$field->display($repeatable_template); // pass repeatable_template so it knows this is called for making js repeatable template
				if (!property_exists($field,'nowrap')) {
					echo "</div><!-- end field -->";
				}
			}
			echo "<input type='hidden' value='1' name='form_" . $this->id . "{$aftername}'>";
		
		echo "</div>";

		// add logic js
		echo "
			<script>
			let form_el = document.getElementById('{$this->id}');
			if (form_el) {
				// create logic function
				function logic_for_{$this->id} () {
					console.log('Doing logic checks');
					let logic_els = form_el.querySelectorAll('.haslogic');
					if (logic_els) {
						logic_els.forEach((e)=>{
							//console.log(e);
							let or_blocks = JSON.parse(e.dataset.logic);
							if (or_blocks) {
								var or_shows = [];
								var show = false; // default to NO SHOW
								console.log(or_blocks);
								// loop over OR blocks
								// single block is normal for single AND
								or_blocks.forEach((and_arr)=>{
									var and_show = true; // default to show
									// b = AND array block
									and_arr.forEach((b)=>{
										// b = single AND obj
										// and_show starts as true
										// any single negative test will set and_show to false
										let logic_target_el = document.getElementById(b.field);
										// todo: make this work for textareas or any other non-value driven field
										if (logic_target_el) {
											let logic_target_value = logic_target_el.value;
											switch(b.test) {
												case '==' :
													local_show = logic_target_value==b.value;
													if (!local_show) {
														and_show = false;
													}
													break;
												default:
													console.warn('Unknown logic test for ',b)
													break;
											}
											
										}
										else {
											console.warn('Unable to find logic target for ',b);
										}
									});
									// push AND final show to or_shows arr
									or_shows.push(and_show);
								});
								// have all ORS (usually only 1 :) )
								// loop over all ORS or until single TRUE is found
								for (var n=0; n<or_shows.length; n++) {
									if (or_shows[n]!==false) {
										show = true;
										break;
									}
								}	
								// set visibility
								// todo: handle 'required'
								// find el inside e that has 'name' attr, target that
								if (show) {
									e.classList.remove('logic_hide');
								}
								else {
									// remove required and hide
									e.classList.add('logic_hide');
								}
							}
							else {
								console.warn('Failed to decode logic for ',e);
							}
						});
						
					}
				}

				// listen for changes on this form container
				form_el.addEventListener('input',function(e){
					let form_wrap_el = e.target.closest('.form_contain');
					// do logic checks
					logic_for_{$this->id}();
				});

				// call logic checks on pageload to ensure correct visibility
				logic_for_{$this->id}();
			}
			else {
				console.warn('Form element not found - validation / visibility logic may not work!');
			}
			</script>
		";
	}
}