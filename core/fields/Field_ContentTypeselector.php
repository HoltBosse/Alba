<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentTypeselector extends Field {

	public function display() {
		$all_contenttypes = Content::get_all_content_types();
		/* CMS::pprint_r ($this); */
		echo "<style>label.checkbox {display:block; margin-bottom:1rem;} label.checkbox input {margin-right:1rem;}</style>";
		if ($this->label) {
			echo "<label class='label'>{$this->label}</label>";
		}
		echo "<div class='field'>";
			$checked = "";
			// check for special content types - just media/images (-1) for now, but maybe more
			if ($this->showmedia) {
				if (in_array("-1", $this->default)) {
					$checked = " checked ";
				}
				echo "<label class='checkbox'>";
					echo "<input {$checked} type='checkbox' name='{$this->name}[]' value='-1'>";
				echo "Media/Images</label>";
				echo "<hr>";
			}
			foreach ($all_contenttypes as $type) {
				echo "<label class='checkbox'>";
					$checked = "";
					if (in_array($type->id, $this->default)) {
						$checked = " checked ";
					}
					echo "<input {$checked} type='checkbox' name='{$this->name}[]' value='{$type->id}'>";
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
		$value = CMS::getvar($this->name, $this->filter);
		if ($value||is_numeric($value)) {
			$this->default = $value;
		}
		else {
			$this->default = array();
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

	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_ContentTypeselector = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_ContentTypeselector.designer_template = `
			<div class="field">
				<h2 class='heading title'>Rich/HTML Field</h2>	

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

	public function load_from_config($config) {
		//CMS::pprint_r ($config);
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 999;
		$this->filter = $config->filter ?? 'RAW';
		$this->minlength = $config->minlength ?? 0;
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->showmedia = $config->showmedia ?? true;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}