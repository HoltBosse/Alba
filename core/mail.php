<?php
defined('CMSPATH') or die; // prevent unauthorized access

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail {
	private $to;
	public $subject;
	private $from;
	public $html;
	public $text;
	private $bcc;
	public $attachments;

	public function __construct() {
		$this->to = [];
		$this->subject = false;
		$this->html = "";
		$this->text = "";
		$this->cc = [];
		$this->bcc = [];
		$this->attachments = [];
	}

	public function addAddress($address, $name=false) {
		$add = new stdClass();
		$add->address = $address;
		$add->name = $name;
		$this->to[] = $add;
	}

	public function addBCC($address, $name=false) {
		$add = new stdClass();
		$add->address = $address;
		$add->name = $name;
		$this->bcc[] = $add;
	}

	public function addCC($address, $name=false) {
		$add = new stdClass();
		$add->address = $address;
		$add->name = $name;
		$this->cc[] = $add;
	}

	public function addAttachment($attachment, $name=false) {
		$add = new stdClass();
		$add->attachment = $attachment;
		$add->name = $name;
		$this->attachments[] = $add;
	}

	public function send() {

		if (!$this->to || !$this->subject || !$this->html) {
			CMS::show_error('No to, subject, or content provided to send email');
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
			// ssl/tls already match constants in PHPMailer
			$encryption=false; // set to false to $mail->SMTPSecure;
			$port = false;
		}
		if ($encryption=="tls") {
			$port=587;
		}
		if ($encryption=="ssl") {
			$port=465;
		}

					

		
		// setup PHPMailer
		require_once CMSPATH . "/core/thirdparty/PHPMailer/Exception.php";
		require_once CMSPATH . "/core/thirdparty/PHPMailer/PHPMailer.php";
		require_once CMSPATH . "/core/thirdparty/PHPMailer/SMTP.php";
		//Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);


		try {
			//Server settings
			$mail->SMTPDebug = false;                      //Enable verbose debug output with SMTP::DEBUG_SERVER
			$mail->isSMTP();                                            //Send using SMTP
			$mail->Host       = $smtp_server;                     //Set the SMTP server to send through
			$mail->SMTPAuth   = $authenticate==true;                                   //Enable SMTP authentication if required
			$mail->Username   = $smtp_username;                     //SMTP username
			$mail->Password   = $smtp_password;  	 //SMTP password
			$mail->SMTPSecure = $encryption;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port       = $port;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
			//Recipients
			$mail->setFrom($smtp_from, $smtp_name);
			$mail->addReplyTo($smtp_replyto, $smtp_name);
			// To
			foreach ($this->to as $add) {
				if ($add->name) {
					$mail->addAddress($add->address, $add->name);
				}
				else {
					$mail->addAddress($add->address);
				}
			}
			// CC
			foreach ($this->cc as $add) {
				if ($add->name) {
					$mail->addCC($add->address, $add->name);
				}
				else {
					$mail->addCC($add->address);
				}
			}
			// BCC
			foreach ($this->bcc as $add) {
				if ($add->name) {
					$mail->addBCC($add->address, $add->name);
				}
				else {
					$mail->addBCC($add->address);
				}
			}
			// attachments
			foreach ($this->attachments as $add) {
				if ($add->name) {
					$mail->addAttachment($add->attachment, $add->name);
				}
				else {
					$mail->addAttachment($add->attachment);
				}
			}
		
			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			$mail->Subject = $this->subject;
			$mail->Body    = $this->html;
			$mail->AltBody = $this->text ? $this->text : strip_tags($this->html);

			$sent = $mail->send();

			if ($sent) {
				return true;
			} 
			else {
				return false;
			}

		} 
		catch (Exception $e) {
			CMS::log('Could not send email: ' . $mail->ErrorInfo);
			return false;
		}
	}

	public static function is_available() {
		return file_exists(CMSPATH . "/thirdparty/PHPMailer/PHPMailer.php");
	}
}