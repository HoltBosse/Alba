<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Turnstile extends Field {

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
		$data = [
			'secret' => $cf_secretkey,
			'response' => $this->default
		];
		$options = [
			'http' => [
				'method' => 'POST',
				'content' => http_build_query($data)
			]
		];
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

	public function load_from_config($config) {
		parent::load_from_config($config);

		$this->filter = $config->filter ?? 'STRING';
	}
}