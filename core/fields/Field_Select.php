<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Select extends Field {

	public $select_options;
	public $config;
	public $slimselect;
	public $multiple;
	public $placeholder;
	public $slimselect_ajax;
	public $slimselect_ajax_minchar;
	public $slimselect_ajax_maxchar;
	public $slimselect_ajax_url;
	public $empty_string;

	public function display() {
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
				echo "<div class='" . ($this->slimselect ? "slimselect_select" : ($this->multiple ? " is-multiple select" : " select")) . "'>";
					echo "<select {$required} id='{$this->id}' {$this->get_rendered_name($this->multiple)} " . ($this->multiple ? "multiple" : false) . ">";
						if ($this->required || $this->placeholder) {
							$placeholder = $this->placeholder ?? $this->label;
							echo "<option value='' >{$placeholder}</option>";
						}
						elseif ($this->empty_string) {
							// not required, but we need a 0 value top option to signify nothing
							echo "<option value='0' >{$this->empty_string}</option>";
						}
						foreach ($this->select_options as $select_option) {
							$disabled = $select_option->disabled ?? false ? " disabled " : "";
							/** @var object{text: mixed, value: mixed} $select_option */
							$selected = "";
							if ($this->multiple && $this->default != "" && in_array($select_option->value, json_decode($this->default))) {
								$selected="selected";
							/*
								this is due to legacy, and how types in php are handled.
								we get for example number values as both strings and ints, so cant use === operator here
								thus we have the issue where if we have a select item with a value of 0 it will equal null in php
								thus we add an additional check for this
							*/
							} elseif ($select_option->value == $this->default && !($select_option->value==0 && $this->default===null)) {
								$selected="selected";
							}
							echo "<option {$disabled} {$selected} value='{$select_option->value}'>" . Input::stringHtmlSafe($select_option->text) . "</option>";
						}
						if ($this->slimselect_ajax) {
							if($this->multiple && $this->default && $this->default != "" && $this->default != '[""]') {
								foreach(json_decode($this->default) as $item) {
									echo "<option selected value='$item'>$item</option>";
								}
							} elseif(!$this->multiple && $this->default && $this->default != "") {
								echo "<option selected value='$this->default'>$this->default</option>";
							}
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help {$hidden}'>" . $this->description . "</p>";
		}
		if($this->slimselect):
		?>
			<script>
				try {
					document.currentScript.parentNode.querySelector(`#<?php echo $this->id; ?>`).slimselect = new SlimSelect({
						select: document.currentScript.parentNode.querySelector(`#<?php echo $this->id; ?>`),
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
				} catch(e) {
					console.log(e);
					alert("SlimSelect is not present!");
				}
			</script>
		<?php
		endif;
	}

	public function get_friendly_value($helpful_info) {
		return $this->default;
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->select_options = $config->select_options ?? [];
		$this->empty_string = $config->empty_string ?? '';
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
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}
