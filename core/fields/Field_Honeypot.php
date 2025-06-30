<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Honeypot extends HoltBosse\Form\Fields\Honeypot\Honeypot {
	public $content_type;
	public $fake_thanks_url;

	public function get_rendered_name($multiple=false) {
		return $this->getRenderedName($multiple);
	}

	public function get_rendered_form() {
		return $this->getRenderedForm();
	}

	public function is_missing() {
		return $this->isMissing();
	}

	public function set_from_submit() {
		return $this->setFromSubmit();
	}

	public function set_from_submit_repeatable($index=0) {
		return $this->setFromSubmitRepeatable($index);
	}

	public function get_friendly_value($helpfulInfo) {
		return $this->getFriendlyValue($helpfulInfo);
	}
	
	public function load_from_config($config) {

	}

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);
		$this->load_from_config($config);
		
		$this->fake_thanks_url = $config->fake_thanks_url ?? null;
	}

	public function validate() {
		$status = parent::validate();

		if (($this->fake_thanks_url ?? null) && $status===false) {
			CMS::Instance()->queue_message('Form Submitted!','success',$this->fake_thanks_url);
		}
		
		return $status;
	}
}