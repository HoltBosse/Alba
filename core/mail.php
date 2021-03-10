<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Mail {
	public $to;
	public $subject;
	public $from;
	public $html;
	public $bcc;
	public $header;
	public $parameters;

	public function __construct() {
		$this->to = false;
		$this->subject = false;
		$this->from = false;
		$this->html = "";
		$this->bcc = false;
		$this->header = array();
		$this->parameters=null;
	}

	public static function is_available() {
		return file_exists(CMSPATH . "/thirdparty/PHPMailer/PHPMailer.php");
	}
}