<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Tab extends Field {

	public $tabs;
	public $mode;
	public $tabsid;
	public $input_type;
	public $nowrap;

	function __construct($id="") {
		$this->id = $id;
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
				<div id="<?php echo $this->id;?>" class="tabs is-centered is-toggle is-medium">
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
				<div class="<?php $tabidentiferpoint="tabidentiferpoint_" . uniqid(); echo $tabidentiferpoint; ?>"></div>
				</div> <!-- end tabs-wrap -->
				<script>
					//handle page load
					let tabidentiferpoint = document.querySelector(".<?php echo $tabidentiferpoint; ?>").previousElementSibling;
					let tabheader = tabidentiferpoint.previousElementSibling;
					let invalidelement = tabidentiferpoint.querySelector(':invalid');
					if(invalidelement) {
						//set tab content to active
						invalidelement.closest(".tab-content").classList.add("is-active");
						//set tab header to active
						let index = Array.prototype.indexOf.call(tabidentiferpoint.querySelectorAll(".tab-content"), invalidelement.closest(".tab-content"));
						tabheader.querySelectorAll("li")[index].classList.add("is-active");
					}

					//handle users invalid fields they set
					tabidentiferpoint.addEventListener("invalid", (e)=>{
						if(!e.target.closest(".tab-content").classList.contains("is-active")) { //we only care about non visible invalids
							//handle tab
							tabidentiferpoint.querySelector(".is-active").classList.remove("is-active");
							e.target.closest(".tab-content").classList.add("is-active");

							//handle header
							tabheader.querySelector(".is-active").classList.remove("is-active");
							let index = Array.prototype.indexOf.call(tabidentiferpoint.querySelectorAll(".tab-content"), e.target.closest(".tab-content"));
							tabheader.querySelectorAll("li")[index].classList.add("is-active");
						}
					}, true);
				</script>
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