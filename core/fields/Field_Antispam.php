<?php
defined('CMSPATH') or die; // prevent unauthorized access

/* Note: this field does NOT currently support checking fields/names within a repeatable form section */
class Field_Antispam extends HoltBosse\Form\Fields\Antispam\Antispam {
	public $content_type;
	public $fake_thanks_url;

	public static function ends_with_ru($string) {
		return parent::endsWithRu($string);
	}
	
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

	public function validate() {
		$status = parent::validate();

		// hopefully a fake thanks page has been set up to avoid tipping off bots that they have been foiled
		// if not, just show error as if form failed
		if (($this->fake_thanks_url ?? null) && $status===false) {
			CMS::Instance()->queue_message('Form Submitted!','success',$this->fake_thanks_url);
			return false;
		} else {
			CMS::Instance()->queue_message('Spam detected','warning');
			return false;
		}
	}

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);
		$this->load_from_config($config);

		$this->fake_thanks_url = $config->fake_thanks_url ?? null;
		$this->blacklist_location = $config->blacklist_location ?? "/blacklist.txt";
		$this->blacklist_location = CMSPATH . $this->blacklist_location;
	}
}