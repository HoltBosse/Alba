<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Image extends Field {

	public $select_options;

	function __construct($id="") {
		$this->id = $id;
		$this->name = $id;
		$this->image_id = null;
		$this->default = null;
	}

	public function display() {

		//CMS::pprint_r ($this);

		$required="";
		if ($this->required) {$required=" required ";}
		?>
		<?php 
		echo "<hr class='image_field_hr image_field_top'>";

		echo "<label class='label'>" . $this->label . "</label>";

		echo "<p>Selected Image</p>";
		if ($this->default) {
			$active = ' active ';
		}
		else {
			$active = '';
		}
		echo "<div class='selected_image_wrap {$active}' id='selected_image_{$this->id}'><p>No Image Selected</p><img  src='".Config::$uripath . '/image/' . $this->default ."/thumb' id='image_selector_chosen_preview_{$this->id}'?></div>";
		

		echo "<button type='button' id='trigger_image_selector_{$this->id}' class='button btn is-primary'>Choose New Image</button>";
		echo "<div class='field'>";
			
			echo "<div class='control'>";
					// TODO replace with api call / allow filtering / typing for title etc
					$query = "select * from media where mimetype='image/jpeg' or mimetype='image/png'";
					$stmt = CMS::Instance()->pdo->query($query);
					$all_images = $stmt->fetchAll();
					echo "<div id='image_selector_{$this->id}' class='hidden image_selector'>";
					foreach ($all_images as $image) {
						$src = Config::$uripath . "/image/" . $image->id . "/thumb";
						$aspect = $image->height / $image->width;
						echo "<img style='padding-bottom:{$aspect}%;' class='lazy' data-src='{$src}' data-width='{$image->width}' width='{$image->width}' data-height='{$image->height}' height='{$image->height}' data-id='{$image->id}' class='image_selector_thumb' src=''>";
					}
					echo "</div>";
					
				
			echo "</div>";
		echo "</div>";

		
		
		
		echo "<input type='hidden' value='{$this->default}' {$required} id='{$this->id}' name='{$this->name}'>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}


		echo "<hr class='image_field_hr image_field_bottom'>";


		?>

		<script>
		// selector window event listener
		var image_selector_<?php echo $this->id; ?> = document.getElementById('image_selector_<?php echo $this->id;?>');
		image_selector_<?php echo $this->id; ?>.addEventListener('click',function(e){
			var image_id = e.target.dataset.id;
			var preview = document.getElementById('image_selector_chosen_preview_<?php echo $this->id; ?>');
			preview.src = '<?php echo Config::$uripath . '/image/';?>' + image_id + '/thumb/';
			preview.closest('.selected_image_wrap').classList.add('active');

			hidden_input = document.getElementById('<?php echo $this->id;?>');
			hidden_input.value = image_id;
			//console.log('selected image id ',image_id);
			//console.log(preview);
			this.classList.toggle('hidden');
		});

		// choose new image button event listener
		var trigger_image_selector_<?php echo $this->id; ?> = document.getElementById('trigger_image_selector_<?php echo $this->id;?>');
		trigger_image_selector_<?php echo $this->id; ?>.addEventListener('click',function(e){
			console.log('boo');
			image_selector_<?php echo $this->id; ?>.classList.toggle('hidden');
		});
		</script>
		<?php
	}


	public function inject_designer_javascript() {
		?>
		<script>
			window.Field_Image = {};
			// template is what gets injected when the field 'insert new' button gets clicked
			window.Field_Image.designer_template = `
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
		//CMS::pprint_r ($config);
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'NUMBER';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->default = $config->default ?? null;
		$this->type = $config->type ?? 'error!!!';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}