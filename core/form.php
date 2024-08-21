<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Form {
	public $id;
	public $fields;
	public $json;
	public $repeatable;
	public $form_path;

	function __construct($path = CMSPATH . "/testform.json", $repeatable=false) {
		$this->fields = [];
		$this->repeatable = $repeatable;
		$this->load_json($path);
	}

	public function load_json($path = CMSPATH . "/testform.json") {
		if (gettype($path)=="object") {
			$obj = $path;
		} elseif (is_file($path)) {
			$this->json = file_get_contents($path);
			$obj = json_decode($this->json);
		} else {
			echo "<h5>File {$path} not found or invalid data passed</h5>";
			if (Config::debugwarnings()) {
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

	private function set_field_required_based_on_logic($field) {
		// logic here mirrors that of js section in 'display_front_end' function in this class
		// algorithm is essentially:
		// loop over logic arrays
		// outarray is ORs
		// each inner array is ANDs
		// note result of each set of AND checks - set a true/false accordingly in an array of OR results
		// check if any OR result is true, if so, we are required
		if ($field->required) {
			$logic = $field->logic ?? false;
			$new_required = false;
			if ($logic) {
				$or_arr = []; // array of AND test results - if ANY are true, we are required
				foreach ($logic as $or) {
					$and_arr = [];
					foreach ($or as $and) {
						// check logic
						$logic_target_field = $this->get_field_by_name($and->field);
						$logic_target_value = $logic_target_field->default; // already set by set_from_submit loop in form class
						switch($and->test) {
							case '==':
								$and_arr[] = $logic_target_value == $and->value;
								break;
							default:
								// unknown test
								$and_arr[] = false;
								break;
						}
					}
					$or_arr[] = !in_array(false, $and_arr, true); // if false is in our AND array, set this OR to false;
				}
				$field->logic_checks_done = true;
				$field->required = in_array(true, $or_arr, true); // if true is anywhere in our or_arr, we're required
				if (!$field->required) {
					$field->required_ignore_by_logic = true;
				}
			}
			// else no logic available, carry on
		}
		// else - we weren't required in the first place!
	}

	public function set_from_submit() {
		foreach ($this->fields as $field) {
			$field->set_from_submit();
		}
		// have all field values do 'required' logic
		foreach ($this->fields as $field) {
			$this->set_field_required_based_on_logic($field);
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
		$name_value_pairs = [];
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
				$nowrap_bool = $field->nowrap ?? false;
				if (!property_exists($field,'nowrap') || !$nowrap_bool) {
					// wrapped field
					// prepare logic data attribute
					$logic = $field->logic;
					if ($logic) {
						
						$logic_json = json_encode($logic);
					}
					else {
						$logic_json = "";
					}
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
					echo "<div data-field_id='{$field->id}' data-logic='{$logic_json}' $req_data class='{$wrapclass} form_field field field_id_{$field->id}'>";
				}
				$field->display($repeatable_template); // pass repeatable_template so it knows this is called for making js repeatable template
				if (!property_exists($field,'nowrap') || !$nowrap_bool) {
					echo "</div><!-- end field -->";
				}
			}
			echo "<input type='hidden' value='1' name='form_" . $this->id . "{$aftername}'>";
		
		echo "</div>";

		// @phpstan-ignore-next-line
		$jsSafeVariableId = preg_replace("^[^a-zA-Z_$]|[^\\w$]", "_safety_", $this->id);

		// add logic js
		echo "
			<script>
			var form_el_{$jsSafeVariableId} = document.getElementById('{$this->id}');
			if (form_el_{$jsSafeVariableId}) {
				// create logic function
				function logic_for_{$jsSafeVariableId} () {
					//console.log('Doing logic checks');
					var logic_els = form_el_{$jsSafeVariableId}.querySelectorAll('.haslogic');
					if (logic_els) {
						//console.log('LOGIC ELS',logic_els);
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
										// todo: make this work for any other 
										// non-value driven field - e.g. checkbox (done!)
										if (logic_target_el) {
											// set to value of el by default
											var logic_target_value = logic_target_el.value;
											if (logic_target_el.nodeName=='INPUT') {
												if (logic_target_el.type=='checkbox') {
													logic_target_value = logic_target_el.checked;
												}
											}
											switch(b.test) {
												case '==' :
													local_show = logic_target_value==b.value;
													if (!local_show) {
														and_show = false;
													}
													break;
												case '!=' :
														local_show = logic_target_value!=b.value;
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
								let actual_named_el = document.getElementById(e.dataset.field_id);
								let is_required = e.dataset.required=='true' ? true : false; 
								if (show) {
									// restore required from json default
									if (actual_named_el) {
										actual_named_el.required = is_required;
									}
									e.classList.remove('logic_hide');
								}
								else {
									// remove required and hide
									// cannot be required, hidden
									if (actual_named_el) {
										actual_named_el.required = false; 
									}
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
				form_el_{$jsSafeVariableId}.addEventListener('input',function(e){
					let form_wrap_el = e.target.closest('.form_contain');
					// do logic checks
					logic_for_{$jsSafeVariableId}();
				});

				// call logic checks on pageload to ensure correct visibility
				logic_for_{$jsSafeVariableId}();
			}
			else {
				console.warn('Form element not found - validation / visibility logic may not work!');
			}
			</script>
		";
	}
}