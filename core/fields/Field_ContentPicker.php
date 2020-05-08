<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_ContentPicker extends Field {
	public function display() {
		echo "<label class='label'>Content Picker</label>";
		echo "<p>Hello, I am a content picker!</p>";
	}

	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_ContentPicker = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_ContentPicker_template = `
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
}