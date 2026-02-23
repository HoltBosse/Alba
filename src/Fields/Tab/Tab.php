<?php
namespace HoltBosse\Alba\Fields\Tab;

Use HoltBosse\Form\Field;
Use HoltBosse\Alba\Core\CMS;

class Tab extends Field {

	public mixed $tabs = null;
	public mixed $mode = null;
	public mixed $tabsid = null;
	public mixed $input_type = null;

	public function display(): void {
		$tabsJs = "<script type='module'>" . file_get_contents(__DIR__ . "/script.js") . "</script>";
		if(!in_array($tabsJs, CMS::Instance()->head_entries)) {
			CMS::Instance()->head_entries[] = $tabsJs;
		}

		//CMS::pprint_r ($this);
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

	public function loadFromConfig(object $config): self {
		parent::loadFromConfig($config);
		
		$this->tabs = $config->tabs ?? $this->tabs;
		$this->mode = $config->mode ?? "tabs";
		$this->nowrap = true;

		return $this;
	}

	public function validate(): bool {
		// not a real field, just displays stuff :)
		return true;
	}
}