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

	public function set_to($email, $name) {
        $this->to[] = $this->format_header((string) $email, (string) $name);
        return $this;
    }

	public function set_subject($subject) {
        $this->subject = (string) $subject;
        return $this;
    }

	public function set_message($markup) {
        $this->html = str_replace("\n.", "\n..", (string) $markup);
        return $this;
	}
	
	public function set_from($email, $name) {
        $this->add_mail_header('From', (string) $email, (string) $name);
        return $this;
	}
	
	public function set_bcc(array $bcc_list) {
		// bcclist array of tuples email/name
        return $this->add_mail_headers('Bcc', $bcc_list);
	}
	
	public function set_reply_to ($email, $name = null) {
        return $this->add_mail_header('Reply-To', $email, $name);
	}

	public function format_header ($email, $name = null) {
        if (empty($name)) {
            return $email;
        }
        return sprintf('"%s" <%s>', $name, $email);
    }
	
	public function add_header($header, $value) {
        $this->headers[] = sprintf(
            '%s: %s',
            (string) $header,
            (string) $value
        );
        return $this;
	}
	
	public function add_mail_headers($header, array $pairs) {
		// mail list
        if (sizeof($pairs) === 0) {
			return $this;
        }
        $addresses = array();
        foreach ($pairs as $name => $email) {
            $addresses[] = $this->format_header($email, $name);
        }
        $this->add_header($header, implode(',', $addresses));
        return $this;
	}
	
	public function add_mail_header ($header, $email, $name = null) {
		// single mail
        $address = $this->format_header((string) $email, (string) $name);
        $this->headers[] = sprintf('%s: %s', (string) $header, $address);
        return $this;
	}
	
	public function send() {
		
		// to
		if (empty($this->to)) {
            $to='';
		}
		$to = implode(', ', $this->to);
		
		// headers
		// set to html by default TODO: plain text/attachments etc
		$this->add_header('MIME-Version', '1.0');
		$this->add_header('Content-Type', 'text/html; charset="utf-8"');
		$headers = implode(PHP_EOL, $this->headers);
		
		return mail($to, $this->subject, $this->html, $headers, $this->params);
	}
}