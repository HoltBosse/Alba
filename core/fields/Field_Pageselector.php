<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Pageselector extends Field {

	public function display() {
		$all_pages = Page::get_all_pages_by_depth();
		/* CMS::pprint_r ($this); */
		echo "<style>label.checkbox {display:block; margin-bottom:1rem;} label.checkbox input {margin-right:1rem;}</style>";
		echo "<div class='field'>";
			foreach ($all_pages as $page) {
				echo "<label class='checkbox'>";
					$checked = "";
					if (in_array($page->id, $this->default)) {
						$checked = " checked ";
					}
					echo "<input {$checked} type='checkbox' name='{$this->name}[]' value='{$page->id}'>";
					for ($n=0; $n<$page->depth; $n++) {
						echo "&nbsp;-&nbsp;";
					}
					echo $page->title;
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
			window.Field_Pageselector = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Pageselector.designer_template = `
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
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}