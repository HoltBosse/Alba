<?php
namespace HoltBosse\Alba\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use stdClass;

class Mail extends PHPMailer {
	private $legacy_to = false;
	public $subject = false;
	public $html = "";
	public $text = "";

	public function __construct($exceptions = true) {
		parent::__construct($exceptions);
	}

	// Legacy API: store addresses for backwards compatibility
	public function addAddress($address, $name='') {
		$this->legacy_to[] = true;
		return parent::addAddress($address, $name);
	}

	// Keep legacy send() behaviour while leveraging PHPMailer internals
	public function send() {
		if (!$this->legacy_to || !$this->subject || !$this->html) {
			throw new Exception('No to, subject, or content provided to send email');
		}

		$smtp_name = Configuration::get_configuration_value ('general_options', 'smtp_name');
		$smtp_password = Configuration::get_configuration_value ('general_options', 'smtp_password');
		$smtp_username = Configuration::get_configuration_value ('general_options', 'smtp_username');
		$smtp_from = Configuration::get_configuration_value ('general_options', 'smtp_from');
		$smtp_replyto = Configuration::get_configuration_value ('general_options', 'smtp_replyto');
		$smtp_server = Configuration::get_configuration_value ('general_options', 'smtp_server');
		$encryption = Configuration::get_configuration_value ('general_options', 'encryption');
		$authenticate = Configuration::get_configuration_value ('general_options', 'authenticate');
		if ($encryption=="none") {
			$encryption=false;
			$port = false;
		}
		if ($encryption=="tls") {
			$port=587;
		}
		if ($encryption=="ssl") {
			$port=465;
		}

		try {
			// Configure PHPMailer (this)
			$this->SMTPDebug = 0;
			$this->isSMTP();
			$this->Host = $smtp_server;
			$this->SMTPAuth = $authenticate==true;
			$this->Username = $smtp_username;
			$this->Password = $smtp_password;
			$this->SMTPSecure = $encryption;
			$this->Port = $port;
			$this->setFrom($smtp_from, $smtp_name);
			$this->addReplyTo($smtp_replyto, $smtp_name);

			$this->isHTML(true);
			$this->Subject = $this->subject;
			$this->Body = $this->html;
			$this->AltBody = $this->text ? $this->text : strip_tags($this->html);

			$sent = parent::send();

			return (bool) $sent;
		} catch (Exception $e) {
			CMS::log('Could not send email: ' . $this->ErrorInfo);
			return false;
		}
	}

	#[\Deprecated(message: "stop using this please", since: "3.0.0")]
	public static function is_available() {
		return true;
	}
}