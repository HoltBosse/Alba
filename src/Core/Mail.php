<?php
namespace HoltBosse\Alba\Core;

use Exception;
use stdClass;

/**
 * Mail - WordPress wp_mail() wrapper
 * Provides wrapper methods for WordPress email functions
 */
class Mail {
	public ?string $subject = null;
	public ?string $html = null;
	public ?string $text = null;
	// @phpstan-ignore missingType.iterableValue
	private array $to = [];
	// @phpstan-ignore missingType.iterableValue
	private array $headers = [];
	// @phpstan-ignore missingType.iterableValue
	private array $attachments = [];

	public function __construct($exceptions = true) {
		// WordPress handles exceptions internally
		$this->headers[] = 'Content-Type: text/html; charset=UTF-8';
	}

	/**
	 * Add email address
	 * Compatible with PHPMailer API but uses WordPress wp_mail
	 */
	public function addAddress($address, $name='') {
		if ($name) {
			$this->to[] = "$name <$address>";
		} else {
			$this->to[] = $address;
		}
		return true;
	}

	/**
	 * Add CC address
	 */
	public function addCC($address, $name='') {
		$cc = $name ? "$name <$address>" : $address;
		$this->headers[] = "Cc: $cc";
		return true;
	}

	/**
	 * Add BCC address
	 */
	public function addBCC($address, $name='') {
		$bcc = $name ? "$name <$address>" : $address;
		$this->headers[] = "Bcc: $bcc";
		return true;
	}

	/**
	 * Add attachment
	 */
	public function addAttachment($path, $name = '') {
		$this->attachments[] = $path;
		return true;
	}

	/**
	 * Set From address (uses WordPress filter)
	 */
	public function setFrom($address, $name = '') {
		add_filter('wp_mail_from', function() use ($address) {
			return $address;
		});
		
		if ($name) {
			add_filter('wp_mail_from_name', function() use ($name) {
				return $name;
			});
		}
		
		return true;
	}

	/**
	 * Add reply-to address
	 */
	public function addReplyTo($address, $name = '') {
		$reply = $name ? "$name <$address>" : $address;
		$this->headers[] = "Reply-To: $reply";
		return true;
	}

	/**
	 * Legacy API: send email using WordPress wp_mail()
	 * Wrapper for WordPress wp_mail()
	 */
	public function send() {
		if (empty($this->to) || !$this->subject || !$this->html) {
			throw new Exception('No to, subject, or content provided to send email');
		}

		// Get SMTP configuration from Alba if available
		$smtp_from = Configuration::get_configuration_value('general_options', 'smtp_from');
		$smtp_name = Configuration::get_configuration_value('general_options', 'smtp_name');
		$smtp_replyto = Configuration::get_configuration_value('general_options', 'smtp_replyto');

		// Set from and reply-to if configured
		if ($smtp_from) {
			$this->setFrom($smtp_from, $smtp_name ?: '');
		}
		if ($smtp_replyto) {
			$this->addReplyTo($smtp_replyto, $smtp_name ?: '');
		}

		// Prepare message body
		$message = $this->html;
		
		// Use WordPress wp_mail()
		$sent = wp_mail(
			$this->to,
			$this->subject,
			$message,
			$this->headers,
			$this->attachments
		);

		if (!$sent) {
			CMS::log('Could not send email via wp_mail()');
			return false;
		}

		return true;
	}

	#[\Deprecated(message: "stop using this please", since: "3.0.0")]
	public static function is_available(): bool {
		return function_exists('wp_mail');
	}

	/**
	 * Simple static wrapper for quick emails
	 * Wrapper for WordPress wp_mail()
	 */
	public static function quick_send(string $to, string $subject, string $message, array $headers = []): bool {
		if (empty($headers)) {
			$headers = ['Content-Type: text/html; charset=UTF-8'];
		}
		
		return wp_mail($to, $subject, $message, $headers);
	}
}