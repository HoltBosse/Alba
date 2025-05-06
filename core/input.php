<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Input {

	static public function tuples_to_assoc($arr) {
		if (is_array($arr)) {
			$result = [];
			foreach ($arr as $i) {
				if ($i['value'] !== false && $i['value'] !== null && $i['value'] !== '') {
					$result[$i['key']] = $i['value'];
				}
			}
			return $result;
		}
		else {
			return [];
		}
	}

	static public function stringURLSafe($string) {
        //remove any '-' from the string they will be used as concatonater
        $str = str_replace('-', ' ', $string);
		$str = str_replace('_', ' ', $string);
		
        // remove any duplicate whitespace, and ensure all characters are alphanumeric
        $str = preg_replace(['/\s+/','/[^A-Za-z0-9\-]/'], ['-',''], $str);

        // lowercase and trim
        $str = trim(strtolower($str));
        return $str;
	}

	//this method exists so that if any future improvements are to be made, it is easy to do in one place
	static public function stringHtmlSafe($string) {
		//for older php versions that convert only double quotes, we want to match modern php
		return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
	}

	static public function make_alias($string) {
		$string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
		$string = Input::stringURLSafe($string);
		return $string;
	}

	public static function getvar ($input, $filter='RAW', $default=NULL) {
		if (isset($_GET[$input])) {
			return Input::filter($_GET[$input], $filter);
		}
		elseif (isset($_POST[$input])) {
			return Input::filter($_POST[$input], $filter);
		}
		else {
			if ($default!==NULL) {
				return $default;
			}
			else {
				return NULL;
			}
		}
	}

	public static function filter($input, $filter='RAW') {
		$foo=$input;
		if ($filter=="RAW") {
			return $foo;
		}
		elseif ($filter=="ALIAS") {
			$temp = filter_var($foo, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
			return Input::stringURLSafe($temp);
		}
		elseif ($filter=="TEXTAREA") {
			// replace newlines with placeholder
			$foo = str_replace("\n","[NEWLINE]",$foo);
			return filter_var($foo, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
		}
		elseif ($filter=="USERNAME"||$filter=="TEXT"||$filter=="STRING") {
			return htmlspecialchars($foo, ENT_QUOTES);
		}
		elseif ($filter=="EMAIL") {
			return filter_var($foo, FILTER_VALIDATE_EMAIL);
		}
		elseif ($filter=="URL") {
			return filter_var($foo, FILTER_VALIDATE_URL);
		}
		elseif ($filter=="ARRAYRAW") {
			if (!is_array($foo)) {
				CMS::Instance()->queue_message('ARRAYRAW cannot return a non-array','danger',Config::uripath() . '/admin');
				return false;
			}
			return $foo;
		}
		elseif ($filter=="CSVINT") {
			$temparr = explode(",",$foo);
			$ok = true;
			foreach ($temparr as $temp) {
				if (!is_numeric($temp)) {
					CMS::Instance()->queue_message('CSVINT can only contains INTS','danger',Config::uripath() . '/admin');
					return false;
				}
			}
			return $foo;
		}
		elseif ($filter=="ARRAYTOJSON"||$filter=="ARRAY") {
			if (!is_array($foo)) {
				CMS::Instance()->queue_message('Cannot convert non-array to json in ARRAYTOJSON','danger',Config::uripath() . '/admin');
				//echo "<h5>Variable is not array, cannot perform ARRAYTOJSON filter</h5>";
				return false;
			}
			$json = json_encode($foo);
			return $json;
		}
		elseif ($filter=="ARRAYOFINT"||$filter=="ARRAYNUM") {
			if (is_array($foo)) {
				// disablenote: this code below always ended up returning true, so have commented it out to be revisted at a future point
				/* $ok = true;
				foreach ($foo as $bar) {
					if ($bar===0||is_numeric($bar)) { // disablenote: move on if its already an int
						// this one is fine
					}
					else { // disablenote: but if it isnt an int....
						$bar = (int)$bar; // disablenote: ...... force it to be one anyways
						if ($bar===0||is_numeric($bar)) { // disablenote: pointless since php will force it to an int no matter what
							// cast to int ok
						}
						else { // disablenote: pointless, see previous disablenote
							$ok = false;
						}
					}
				}
				if ($ok) { // disablenote: as stated in previous comments, the value will never actually be changed from true, so we always return $foo
					return $foo;
				}
				else {
					CMS::Instance()->queue_message('Cannot convert non-array to array in ARRAYOFINT','danger',Config::uripath() . '/admin');
					return false;
				} */
				return $foo;
			}
			else {
				CMS::Instance()->queue_message('Cannot convert non-array to array in ARRAYOFINT','danger',Config::uripath() . '/admin');
				return false;
			}
		}
		elseif ($filter=="ARRAYOFSTRING") {
			if (is_array($foo)) {
				$ok = true;
				foreach ($foo as $bar) {
					if (is_string($bar)) {
						// this one is fine
					}
					else {
						$ok = false;
					}
				}
				if ($ok) {
					return $foo;
				}
				else {
					return false;
				}
			}
			else {
				//CMS::log('Cannot convert non-array to array in ARRAYOFSTRING');
				return false;
			}
		}
		elseif ($filter=="ARRAYOFARRAYS") {
			if (is_array($foo)) {
				$ok = true;
				foreach ($foo as $bar) {
					if (is_array($bar)) {
						// this one is fine
					}
					else {
						$ok = false;
					}
				}
				if ($ok) {
					return $foo;
				}
				else {
					return false;
				}
			}
			else {
				CMS::Instance()->queue_message('Cannot convert non-array to array in ARRAYOFSTRING','danger',Config::uripath() . '/admin');
				return false;
			}
		}
		elseif ($filter=="NUM"||$filter=="INT"||$filter=="NUMBER"||$filter=="NUMERIC") {
			if ($foo===0) {
				return 0;
			}
			else {
				return filter_var($foo, FILTER_SANITIZE_NUMBER_INT);
			}
		}
		elseif ($filter=="FLOAT") {
			if ($foo===0) {
				return 0;
			}
			else {
				return filter_var($foo, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			}
		}
		elseif ($filter=="JSON") {
			return json_decode($foo) ? $foo : false;
		}
		else {
			//return $foo;
			return false;
		}
	}


}