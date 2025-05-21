<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Form implements JsonSerializable {
	public $id;
	public $display_name;
	public $fields;
	public $json;
	public $repeatable;
	public $form_path;

	function __construct($path = CMSPATH . "/testform.json", $repeatable=false) {
		$this->fields = [];
		$this->repeatable = $repeatable;
		$this->form_path = $path;
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
			$this->display_name = isset($obj->display_name) ? $obj->display_name : $this->id;
			
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

	public function set_field_required_based_on_logic($field) {
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

	public function jsonSerialize(): mixed {
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
			if($field->save!==false) {
				$name_value_pairs[] = $pair;
			}
		}
		return $name_value_pairs;
	}

	public function serialize_json() {
		return json_encode($this);
	}

	public function deserialize_json($json): mixed {
		$json_obj = json_decode($json);
		if ($json_obj) {
			foreach ($json_obj as $option) {
				/*
					this is a legacy check that exists due to:
					1. the page form having fields without save
					2. serialization logic not handling save field
					3. serialization handling of fields without names
				*/
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

	public function save_to_db() {
		//if the form was loaded from an object and the path not set afterwords.....
		if(gettype($this->form_path)!="string") {
			CMS::show_error("Failed to save form submission, bad form path!", 500);
		}

		DB::exec(
			"INSERT INTO form_submissions (form_id, form_path, data) values (?,?,?)",
			[$this->id, str_replace(CMSPATH, "", $this->form_path), $this->serialize_json()]
		);
	}

	public static function create_email_html_wrapper($body) {
		ob_start();

		$admin_logo_id = Configuration::get_configuration_value('general_options','admin_logo');
		$site_domain = "https://" . $_SERVER['SERVER_NAME'];
		$logo_src = $site_domain . "/image/" . $admin_logo_id;
		?>
			<div style='font-family: BlinkMacSystemFont, -apple-system, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 16px; padding: 0; margin: 0;'>
            <table style="padding: 0; margin: 0; border-spacing: 0px; background-color: lightgrey;">
                <tbody>
                    <tr style="height: 25px;"></tr>
                    <tr>
                        <td style="width: 50px;"></th>
                        <td style="width: 500px; padding: 25px; background-color: white; border-radius: 0px;">
                            <br>
                            <div style="text-align: center;">
                                <a href="<?php echo $site_domain ?>">
                                    <img src="<?php echo $logo_src ?>" alt="<?php echo Config::sitename(); ?> Logo" style="width: 300px;">
                                </a>
                            </div>
                            <br>
							<div style="text-align: left; font-weight: normal;">
                            	<?php echo $body; ?>
							</div>
                        </th>
                        <td style="width: 50px; "></th>
                    </tr>
                    <tr style="height: 25px;"></tr>
                </tbody>
            </table>
        </div>    
		<?php
		return ob_get_clean();
	}

	public function create_email_html() {
		ob_start();
			echo "<h1 style='text-align: center;  font-size: 24px;'>$this->display_name submission</h1>";
			foreach($this->fields as $field) {
				if($field->name!="error!!!" && $field->save!==false) {
					echo "<p>" . $field->label . ": " . $field->default . "<p>";
				}
			}
		return $this->create_email_html_wrapper(ob_get_clean());
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

		$jsSafeVariableId = preg_replace("/[^a-zA-Z_$]|[^\\w$]/", "_safety_", $this->id);

		// add logic js
		?>
			<script>
				if(!window.evaluateFieldLogic) {
					function evaluateFieldLogic(form, logic, element) {
						return logic.some(andConditions => 
							andConditions.every(condition => 
								evaluateFieldCondition(form, condition, element)
							)
						);
					}

					function evaluateFieldCondition(form, condition, element) {
						const { field, test, value } = condition;
						let sectionRoot = form;
						let name = field;

						//if we are in a repeatable, then adjust root and fieldname
						if(element.closest(".repeatable")) {
							sectionRoot = element.closest(".repeatable");
							name = `${field}[]`;
						}

						// get first un-ignored named field - primarily used to ignore checkbox default hidden values
						const target = sectionRoot.querySelector(`[name="${name}"]:not([data-logicignore])`); 
						let targetValue = target.value;

						if (target.nodeName=='INPUT' && target.type=='checkbox') {
							targetValue = target.checked ? 1 : 0;
						}

						switch (test) {
							case '==':
								return targetValue == value;
							case '===':
								return targetValue === value;
							case '!=':
								return targetValue != value;
							case '!==':
								return targetValue !== value;
							case '>':
								return targetValue > value;
							case '>=':
								return targetValue >= value;
							case '<':
								return targetValue < value;
							case '<=':
								return targetValue <= value;
							default:
								throw new Error(`Unsupported test: ${test}`);
						}
					}

					function updateAllFieldLogic(form) {
						form.querySelectorAll(`[data-logic]:not([data-logic=""]`).forEach(el=>{
							/* console.log(el);
							console.log(el.querySelector("label").innerText);
							console.log(evaluateFieldLogic(form, JSON.parse(el.dataset.logic), el)===true ? "true" : "false"); */

							const isRequired = el.dataset.required=='true' ? true : false;
							const actualNamedEl = el.querySelector(`#${el.dataset.field_id}`);

							if(evaluateFieldLogic(form, JSON.parse(el.dataset.logic), el)) {
								actualNamedEl.required = isRequired;
								el.classList.remove("logic_hide");
							} else {
								actualNamedEl.required = false;
								el.classList.add("logic_hide");
							}
						});
					}
				}

				if(typeof formEl_<?php echo $jsSafeVariableId; ?> === 'undefined') {
					const formEl_<?php echo $jsSafeVariableId; ?> = document.getElementById('<?php echo $this->id ?>'); //wrapping form

					formEl_<?php echo $jsSafeVariableId; ?>.addEventListener('input', (e)=>{
						updateAllFieldLogic(formEl_<?php echo $jsSafeVariableId; ?>); //run when a form element changes value
					});
					formEl_<?php echo $jsSafeVariableId; ?>.addEventListener('change', (e)=>{ //a normal select does an input+change event. a slimselect only does a change
						updateAllFieldLogic(formEl_<?php echo $jsSafeVariableId; ?>); //run when a form element changes value
					});

					updateAllFieldLogic(formEl_<?php echo $jsSafeVariableId; ?>); //run on init
				}
			</script>
		<?php
	}
}