<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Input extends HoltBosse\Form\Input {
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

	static public function make_alias($string) {
		return Input::makeAlias($string);
	}
}