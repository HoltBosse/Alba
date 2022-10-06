<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Turnstile extends Field {

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->default = $default_content;
		$this->content_type="";
	}

	public function display() {
		$cf_sitekey = Configuration::get_configuration_value ('general_options', 'cf_sitekey');
		if ($cf_sitekey) {
			echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
			echo "<div class='cf-turnstile' data-sitekey='{$cf_sitekey}'></div>";
		}
		else {
			echo "<h5><strong>NO TURNSTILE SITEKEY</strong></h5>";
		}
	}



	public function designer_display() {

	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->maxlength = $config->maxlength ?? 999;
		$this->filter = $config->filter ?? 'STRING';
		$this->type = $config->type ?? 'error!!!';
		$this->default = $config->default ?? $this->default;
	}

	public function validate() {
		
		$this->default = Input::getvar('cf-turnstile-response',"STRING",false);
		if ($this->is_missing()) {
			return false;
		}
		// get cloudflare response
		$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
		$cf_secretkey = Configuration::get_configuration_value ('general_options', 'cf_secretkey');
		if (!$cf_secretkey) {
			CMS::Instance()->queue_message('Cloudflare secret key is not set correctly','danger');
			return false;
		}
		$data = array(
			'secret' => $cf_secretkey,
			'response' => $this->default
		);
		$options = array(
			'http' => array (
				'method' => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success=json_decode($verify);
		//CMS::log(print_r($captcha_success, true));
		if ($captcha_success->success==false) {
			CMS::Instance()->queue_message('Failed Cloudflare test','danger');
			return false;
		}
		else {
			// passed reCAPTCHA
			return true;
		}
	}
}