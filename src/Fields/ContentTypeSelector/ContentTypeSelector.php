<?php
namespace HoltBosse\Alba\Fields\ContentTypeSelector;

Use HoltBosse\Form\{Field, Input};
Use HoltBosse\Alba\Core\Content;

class ContentTypeSelector extends Field {

	public mixed $showmedia = null;
	public mixed $showusers = null;

	public function display(): void {
		$all_contenttypes = Content::get_all_content_types();
		/* CMS::pprint_r ($this); */
		echo "<style>label.checkbox {display:block; margin-bottom:1rem;} label.checkbox input {margin-right:1rem;}</style>";
		if ($this->label) {
			echo "<label class='label '>{$this->label}</label>";
		}
		echo "<hr>";
		echo "<div class='field'>";
		$checked = "";
			// check for special content types - just media/images (-1) for now, but maybe more
			// ok we added users here too 
			if ($this->showmedia) {
				$checked = "";
				if ($this->default && in_array("-1", $this->default)) {
					$checked = " checked ";
				}
				echo "<label class='checkbox' data-contenttype-id='-1'>";
					echo "<input {$checked} type='checkbox' {$this->getRenderedName(true)} {$this->getRenderedForm()} value='-1'>";
				echo "Media/Images</label>";
				echo "<hr>";
			}
			if ($this->showusers) {
				$checked = "";
				if ($this->default && in_array("-2", $this->default)) {
					$checked = " checked ";
				}
				echo "<label class='checkbox' data-contenttype-id='-2'>";
					echo "<input {$checked} type='checkbox' {$this->getRenderedName(true)} {$this->getRenderedForm()} value='-2'>";
				echo "Users</label>";
				echo "<hr>";
			}
			
			foreach ($all_contenttypes as $type) {
				echo "<label class='checkbox' data-contenttype-id='{$type->id}'>";
					$checked = "";
					if ($this->default && in_array($type->id, $this->default)) {
						$checked = " checked ";
					}
					echo "<input {$checked} type='checkbox' {$this->getRenderedName(true)} {$this->getRenderedForm()} value='{$type->id}'>";
					echo $type->title;
				echo "</label>";
			}
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}

	public function setFromSubmit(): void {
		// override default field function
		$filter = Input::isValidatorRule($this->filter) ? Input::buildValidatorFromArray((array) $this->filter) : $this->filter;
		$value = Input::getvar($this->name, $filter);
		if ($value||is_numeric($value)) {
			$this->default = $value;
		}
		else {
			$this->default = [];
		}
	}

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);

		$this->showmedia = $config->showmedia ?? true;
		$this->showusers = $config->showusers ?? false;

		return $this;
	}

	public function validate(): bool {
		// TODO: enhance validation
		if ($this->isMissing()) {
			return false;
		}
		return true;
	}
}
