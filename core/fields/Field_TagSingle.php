<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_TagSingle extends Field {

	public $select_options;

	function __construct($tagid="") {
		$this->id = "";
		$this->name = "";
		$this->select_options=[];
		$this->default = $tagid;
		$this->content_type="";
	}

	

	public function display() {
		$required="";
		if ($this->content_type) {
			$tags = Content::get_applicable_tags ($this->content_type);
		}
		else {
			$tags = Tag::get_all_tags ();
		}
		if ($this->required) {$required=" required ";}
		echo "<div class='field'>";
			echo "<label class='label'>" . $this->label . "</label>";
			echo "<div class='control'>";
				echo "<div class='select'>";
					echo "<select {$required} id='{$this->id}' {$this->get_rendered_name()}>";
						if ($this->required) {
							echo "<option value='' >{$this->label}</option>";
						}
						foreach ($tags as $tag) {
							if ($tag->state==1) {
								$selected = "";
								if ($tag->id == $this->default) { $selected="selected";}
								echo "<option {$selected} value='{$tag->id}'>{$tag->title}</option>";
							}
						}
					echo "</select>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_TagSingle = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_TagSingle.designer_template = `
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

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'NUMBER';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->select_options = $config->select_options ?? [];
		$this->default = $config->default ?? '';
		$this->type = $config->type ?? 'error!!!';
		$this->content_type = $config->content_type ?? false;
		$this->logic = $config->logic ?? '';
	}

	public function get_friendly_value() {
		return DB::fetch('select title from tags where id=?', [$this->default])->title;
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}