<?php
namespace HoltBosse\Alba\Core;

use HoltBosse\DB\DB;
use HoltBosse\Form\{Form as FormForm, Input};
use \Exception;

class Form extends FormForm {
    public function saveToDb() {
		//if the form was loaded from an object and the path not set afterwords.....
		if(gettype($this->formPath)!="string") {
			throw new Exception("Failed to save form submission, bad form path!");
		}

		DB::exec(
			"INSERT INTO form_submissions (form_id, form_path, data) values (?,?,?)",
			[$this->id, str_replace($_ENV["root_path_to_forms"], "", $this->formPath), $this->serializeJson()]
		);
	}
}