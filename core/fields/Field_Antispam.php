<?php
defined('CMSPATH') or die; // prevent unauthorized access

/* Note: this field does NOT currently support checking fields/names within a repeatable form section */

class Field_Antispam extends Field {

	function __construct($default_content="") {
		$this->id = "";
		$this->name = "";
		$this->default = $default_content;
		$this->content_type="";
		$this->nowrap = true;
		$this->save=false;
		$this->blacklist_location = null; // relative to CMS root
		$this->use_blacklist;
		$this->fieldname;
		$this->block_urls;
		$this->charset_check;
		$this->fake_thanks_url;
	}

	public function display() {
		echo "<!-- https://giphy.com/gifs/artists-on-tumblr-foxadhd-xLhloTgdu7i92 -->";
	}

	public function load_from_config($config) {
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? true;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'STRING';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->type = $config->type ?? 'error!!!';
		$this->default = $config->default ?? $this->default;
		$this->save = $config->save ?? false;
		$this->fieldname = $config->fieldname ?? null;
		$this->use_blacklist = $config->use_blacklist ?? false;
		$this->block_urls =  $config->block_urls ?? false;
		$this->blacklist_location = $config->blacklist_location ?? "/blacklist.txt";
		$this->charset_check = $config->charset_check ?? false;
		$this->fake_thanks_url = $config->fake_thanks_url ?? null;
	}

	private function in_blacklist ($value) {
		// check blacklist file for value - case insensitive 
		$in_blacklist = false;
		if ($this->use_blacklist) {
			if ($this->blacklist_location) {
				if (is_file(CMSPATH . $this->blacklist_location)) {
					// check for exact match of lower case value in each line
					$file = fopen(CMSPATH . $this->blacklist_location, "r");

					$search_string = strtolower($value);

					while (($line = fgets($file)) !== false) {
						$words = explode(" ", $search_string);
						foreach ($words as $word) {
							if (strpos($line, $word) !== false) {
								// found match in blacklist, no need to look more
								$in_blacklist = true;
								break 2; // break both loops
							}
						}
					}
				}
			}
		}
		return $in_blacklist;
	}

	public function validate() {
		// safety net for repeatables
		if ($this->in_repeatable_form ?? null) {
			return true; // cannot determine if invalid for now, assume good
		}
		$valid = true; // assume good to start
		if ($this->fieldname) {
			$val = Input::getvar($this->fieldname);
			$val = strtolower($val);

			// blacklist check if needed
			if ($this->use_blacklist) {
				if ( $this->in_blacklist($val) ) {
					$valid = false;
				}
			}
			// if charset check required
			if ($this->charset_check) {
				/*
				This snippet uses a regular expression to search for Unicode characters in the Cyrillic range (\x{0400}-\x{04FF}). The u modifier at the end of the pattern makes the regex engine treat the string as UTF-8.
				Note: This code will detect any Cyrillic characters, not just Russian. If you need to detect specifically Russian characters, you may need to adjust the regex pattern to exclude other Cyrillic characters.
				*/
				$contains_cyrillic = preg_match('/[\x{0400}-\x{04FF}]/u', $val);
				if ($contains_cyrillic) {
					// not valid
					$valid = false;
				}
			}
			// check if url check required 
			if ($this->block_urls) {
				$contains_url = preg_match('/https?:\/\/[^\s]+/i', $val);
				if ($contains_url) {
					$valid = false;
				}
			}
		}
		// hopefully a fake thanks page has been set up to avoid tipping off bots that they have been foiled
		// if not, just show error as if form failed
		if (!$valid) {
			// our default value of space has been altered, invalid form
			if ($this->fake_thanks_url ?? null) {
				CMS::Instance()->queue_message('Form Submitted!','success',$this->fake_thanks_url);
				return false;
			}
			else {
				CMS::Instance()->queue_message('Spam detected','warning');
				return false;
			}
		}
		else {
			return true;
		}
	}
}