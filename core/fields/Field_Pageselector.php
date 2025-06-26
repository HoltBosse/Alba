<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Pageselector extends Field {

	public $maxlength;
	public $minlength;
	public $multiple;

	public function display() {
		$all_pages = Page::get_all_pages_by_depth();
		//CMS::pprint_r ($this);
		if ($this->multiple) {
			echo "<style>label.checkbox {display:block; margin-bottom:1rem;} label.checkbox input {margin-right:1rem;}</style>";
			echo "<div class='field'>";
				foreach ($all_pages as $page) {
					echo "<label class='checkbox'>";
						$checked = "";
						if (is_array($this->default)) {
							if (in_array($page->id, $this->default)) {
								$checked = " checked ";
							}
						}
						echo "<input {$checked} type='checkbox' {$this->get_rendered_name(true)} {$this->get_rendered_form()} value='{$page->id}'>";
						for ($n=0; $n<$page->depth; $n++) {
							echo " - ";
						}
						echo Input::stringHtmlSafe($page->title);
					echo "</label>";
				}
				if ($this->description) {
					echo "<p class='help'>" . $this->description . "</p>";
				}
			echo "</div>";
		}
		else {?>
			<div class='field'>
				<label class='label' for='<?php echo $this->id;?>'><?php echo $this->label; ?></label>
				<div class="control">
    				<div class="select">
						<select class='select' <?php echo $this->get_rendered_name(true);?> <?php echo $this->get_rendered_form(); ?>>
							<?php foreach ($all_pages as $page):?>
								<?php 
								$selected = "";
								$selected = "";
								if (is_array($this->default)) {
									if (in_array($page->id, $this->default)) {
										$selected = " selected ";
									}
								}
								else {
									if ($page->id==$this->default) {
										$selected = " selected ";
									}
								}
								for ($n=0; $n<$page->depth; $n++) {
									$page->title = " - " . Input::stringHtmlSafe($page->title);
								}
								?>
								<option <?php echo $selected;?> value="<?php echo $page->id;?>"><?php echo Input::stringHtmlSafe($page->title);?></option>
							<?php endforeach;?>
						</select>
					</div>
				</div>
			</div>
		<?php
		}
	}

	public function set_from_submit() {
		// override default field function
		$value = Input::getvar($this->name, $this->filter);
		if (is_array($value)) {
			$this->default = $value;
		}
		else {
			$this->default = [$value];
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

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		$this->filter = $config->filter ?? 'ARRAYOFINT';
		$this->multiple = $config->multiple ?? true;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}