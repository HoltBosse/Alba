<?php
namespace HoltBosse\Alba\Fields\PageSelector;

Use HoltBosse\Form\{Field, Input};
Use HoltBosse\Alba\Core\{CMS, Page};
Use Respect\Validation\Validator as v;

class PageSelector extends Field {

	public $maxlength;
	public $minlength;
	public $multiple;
	public $domain;

	public function display() {
		$all_pages = Page::get_all_pages_by_depth();
		//CMS::pprint_r ($this);
		if ($this->multiple) {
			echo "<style>label.checkbox {display:block; margin-bottom:1rem;} label.checkbox input {margin-right:1rem;}</style>";
			echo "<div class='field'>";
				foreach ($all_pages as $page) {
					if($this->domain !== null && $page->domain != $this->domain) {
						continue;
					}
					echo "<label class='checkbox'>";
						$checked = "";
						if (is_array($this->default)) {
							if (in_array($page->id, $this->default)) {
								$checked = " checked ";
							}
						}
						echo "<input {$checked} type='checkbox' {$this->getRenderedName(true)} {$this->getRenderedForm()} value='{$page->id}'>";
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
						<select class='select' <?php echo $this->getRenderedName(true);?> <?php echo $this->getRenderedForm(); ?>>
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

	public function setFromSubmit() {
		// override default field function
		$filter = Input::isValidatorRule($this->filter) ? Input::buildValidatorFromArray((array) $this->filter) : $this->filter;
		$value = Input::getvar($this->name, $filter);
		if (is_array($value)) {
			$this->default = $value;
		}
		else {
			$this->default = [$value];
		}
	}

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);
		
		$this->filter = $config->filter ?? v::arrayType()->each(v::intVal());
		$this->multiple = $config->multiple ?? true;
		$this->domain = $config->domain ?? null;
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->isMissing()) {
			return false;
		}
		return true;
	}
}