<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Tab extends Field {

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->tabs = [];
		$this->default = [];
		$this->mode = "tabs"; // can be tabs, start, end, tabstart, tabend
	}

	public function display() {
		//CMS::pprint_r ($this);
		if (property_exists($this,'mode')) {
			if ($this->mode=="tabs"):?>
			<div id="<?php echo $this->id;?>_tabs_wrap" class='tabs-wrap'>
				<div id="<?php echo $this->id;?>" class="tabs is-boxed">
					<ul>
						<?php foreach ($this->tabs as $tab): ?>
							<li><a><?php echo $tab; ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php elseif ($this->mode=="tabscontentstart"):?>
				<div  class='tab-content-start'>
			<?php elseif ($this->mode=="tabscontentend"):?>
				</div> <!-- end tabs -->
				</div> <!-- end tabs-wrap -->
			<?php elseif ($this->mode=="tabstart"):?>
				<div  class='<?php echo $this->tabsid;?>-tab-content tab-content'>
			<?php elseif ($this->mode=="tabend"):?>
				</div> <!-- end tab -->
			<?php endif; 
		}
		else {
			CMS::show_error('Unknown display mode for Tab form field');
		}
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
		$this->input_type = $config->input_type ?? 'text';
		$this->default = $config->default ?? $this->default;
		$this->tabs = $config->tabs ?? $this->tabs;
		$this->mode = $config->mode ?? "tabs";
		$this->nowrap = true; // don't wrap each tab field element individually
	}

	public function validate() {
		// not a real field, just displays stuff :)
		return true;
	}
}