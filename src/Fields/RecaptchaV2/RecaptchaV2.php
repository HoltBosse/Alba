<?php
namespace HoltBosse\Alba\Fields\RecaptchaV2;

Use HoltBosse\Form\Field;
use HoltBosse\Alba\Core\{CMS, Configuration};

class RecaptchaV2 extends Field {

	public $maxlength;

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

	public function loadFromConfig($config) {
		parent::loadFromConfig($config);
		
		$this->filter = $config->filter ?? 'STRING';
	}

	public function validate() {
		if ($this->isMissing()) {
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