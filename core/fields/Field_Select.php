<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Select extends Field {

	public $select_options;

	function __construct($id="") {
		$this->id = $id;
		$this->name = $id;
		$this->select_options=[];
		$this->config = false;
	}

	public function display() {
		// if id needs to be unique for scripting purposes, make sure replacement text inserted
		// this will be replaced during repeatable template literal js injection when adding new
		// repeatable form item
		if ($this->in_repeatable_form===null) {
			$repeatable_id_suffix='';
		}
		else {
			$repeatable_id_suffix='{{repeatable_id_suffix}}'; // injected via JS at repeatable addition time
			$this->id = $this->id . $repeatable_id_suffix;
		}
		$UpdateSelect = [];
		$required="";
		if ($this->required) {$required=" required ";}
		if (property_exists($this,'attribute_list')) {
			$attributes = implode(' ',$this->attribute_list);
			if (in_array('hidden',$this->attribute_list)) {
				$hidden = "hidden";
			}
		}
		echo "<div class='field {$hidden} {$required}'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='" . ($this->slimselect ? "slimselect_select" : "select") . "'>";
					echo "<select {$required} id='{$this->id}' {$this->get_rendered_name($this->multiple)} " . ($this->multiple ? "multiple" : false) . ">";
						if ($this->required) {
							$placeholder = $this->placeholder ?? $this->label;
							echo "<option value='' >{$placeholder}</option>";
						}
						foreach ($this->select_options as $select_option) {
							$selected = "";
							if ($this->multiple && $this->default != "" && in_array($select_option->value, json_decode($this->default))) {
								$selected="selected";
							} elseif ($select_option->value == $this->default) {
								$selected="selected";
							}
							if (isset($select_option->UpdateSelect)) { $UpdateSelect[$select_option->value] = $select_option->UpdateSelect;}
							echo "<option {$selected} value='{$select_option->value}'>{$select_option->text}</option>";
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help {$hidden}'>" . $this->description . "</p>";
		}
		if (sizeof($UpdateSelect) >=1):
		?>
			<script>	
				function <?php echo "FieldUpdate" . $this->id; ?>() {	
					var <?php echo $this->id . "_UpdateSelect"?> = <?php echo json_encode($UpdateSelect, JSON_UNESCAPED_SLASHES) ?>;	
					var sel = document.getElementById("<?php echo $this->id; ?>");	
					var oel = sel.options[sel.selectedIndex].value;	
					if(<?php echo $this->id . "_UpdateSelect"?>[oel] && document.getElementById(<?php echo $this->id . "_UpdateSelect"?>[oel].id)){	
						var markup="";	
						var ojson = <?php echo $this->id . "_UpdateSelect"?>[oel].select_options	
						for (const key in ojson) {	
							if (ojson[key].text != "") {	
								markup+=`<option value="${ojson[key].value}">${ojson[key].text}</option>`	
							}	
						}	
						document.getElementById(<?php echo $this->id . "_UpdateSelect"?>[oel].id).innerHTML=markup;	
					}	
				}	
				document.getElementById("<?php echo $this->id; ?>").addEventListener('change', (e) => {	
					<?php echo "FieldUpdate" . $this->id; ?>();	
				});	
				window.addEventListener('load', function () {	
					<?php echo "FieldUpdate" . $this->id; ?>();	
				});	
			</script>
		<?php
		endif;
		if($this->slimselect):
		?>
			<script>
				try {
					document.getElementById('<?php echo $this->id;?>').slimselect = new SlimSelect({
						select: '#<?php echo $this->id;?>',
						<?php if($this->slimselect_ajax): ?>
						searchingText: 'Searching...',
						ajax: function (search, callback) {
							if (search.length < <?php echo $this->slimselect_ajax_minchar; ?>) {
								callback('Please enter at least <?php echo $this->slimselect_ajax_minchar; ?> characters')
								return
							}

							fetch('<?php echo Config::uripath() . $this->slimselect_ajax_url; ?>?searchterm=' + encodeURI(search))
							.then(function (response) {
								return response.json()
							})
							.then(function (json) {
								let data = [];
								json.data.forEach((item)=>{
									data.push({text: item.text, value: item.value})
								});

								//console.log(data);
								callback(data);
							})
							.catch(function(error) {
								callback(false)
							})
						}
						<?php endif; ?>
					});
				} catch {
					alert("SlimSelect is not present!");
				}
			</script>
		<?php
		endif;
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Select = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Select.designer_template = `
			<div class="field">
				<h2 class='heading title'>Text Field</h2>	

				<label class="label">Label</label>
				<div class="control has-icons-left has-icons-right">
					<input required name="label" class="input iss-success" type="label" placeholder="Label" value="">
				</div>

				<label class="label">Required</label>
				<div class="control has-icons-left has-icons-right">
					<input name="required" class="checkbox iss-success" type="checkbox"  value="">
				</div>
			</div>`;
		</script>
		<?php 
	}

	public function designer_display() {

	}

	public function get_friendly_value() {
		// TODO: select fields need to store config somewhere to retrieve friendly values
		// for now return id
		return $this->id;
	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		
		$this->missingconfig = $config->missingconfig ?? false;
		$this->select_options = $config->select_options ?? [];
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
		$this->config = $config;
		$this->empty_string = $config->empty_string ?? '';
		$this->placeholder = $config->placeholder ?? '';
		$this->slimselect = $config->slimselect ?: false;
		$this->multiple = $config->multiple ?: false;
		$this->slimselect_ajax = $config->slimselect_ajax ?? false;
		$this->slimselect_ajax_url = $config->slimselect_ajax_url ?? "";
		$this->slimselect_ajax_minchar = $config->slimselect_ajax_minchar ?? 3;
		if ($this->multiple) {
			$this->filter = $config->filter ?? 'ARRAYOFSTRING';
		}
		else {
			$this->filter = $config->filter ?? 'STRING';
		}
		$this->logic = $config->logic ?? '';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
