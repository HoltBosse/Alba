<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_RecaptchaV2 extends Field {

	public $maxlength;

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->default = $default_content;
		$this->content_type="";
	}

	public function display() {
		$rc_sitekey = Configuration::get_configuration_value ('general_options', 'rc_sitekey');
		if ($rc_sitekey) {
			echo "<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
			echo "<div class='g-recaptcha' data-sitekey='{$rc_sitekey}'></div>";
		}
		else {
			echo "<h5><strong>NO RECAPTCHA SITEKEY</strong></h5>";
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
		$this->logic = $config->logic ?? '';
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		// get google response
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$rc_secretkey = Configuration::get_configuration_value ('general_options', 'rc_secretkey');
		if (!$rc_secretkey) {
			CMS::Instance()->queue_message('reCAPTCHA secret key is not set correctly','danger');
			return false;
		}
		$data = [
			'secret' => $rc_secretkey,
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
		if ($captcha_success->success==false) {
			CMS::Instance()->queue_message('Failed reCAPTCHA test','danger');
			return false;
		}
		else {
			// passed reCAPTCHA
			return true;
		}
	}
}