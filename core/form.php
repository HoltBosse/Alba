<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Form extends HoltBosse\Form\Form implements JsonSerializable {
	public function set_field_required_based_on_logic($field) {
		return $this->setFieldRequiredBasedOnLogic($field);
	}

	public function set_from_submit() {
		return $this->setFromSubmit();
	}

	public function get_field_by_name($field_name) {
		return $this->getFieldByName($field_name);
	}

	public function is_submitted() {
		return $this->isSubmitted();
	}

	public function serialize_json() {
		return $this->serializeJson();
	}

	public function deserialize_json($json) {
		return $this->deserializeJson($json);
	}

	public function display_front_end($repeatableTemplate=false) {
		return $this->display($repeatableTemplate);
	}

	public function save_to_db() {
		return $this->saveToDb();
	}

	public function saveToDb() {
		//if the form was loaded from an object and the path not set afterwords.....
		if(gettype($this->formPath)!="string") {
			throw new Exception("Failed to save form submission, bad form path!");
		}

		DB::exec(
			"INSERT INTO form_submissions (form_id, form_path, data) values (?,?,?)",
			[$this->id, str_replace(CMSPATH, "", $this->formPath), $this->serializeJson()]
		);
	}

	public static function create_email_html_wrapper($body) {
		$adminLogoId = Configuration::get_configuration_value('general_options','admin_logo');
		$logoSrc = "https://" . $_SERVER['SERVER_NAME'] . "/image/" . $adminLogoId;

		return parent::createEmailHtmlWrapper($body, $logoSrc);
	}

	public function create_email_html() {
		$adminLogoId = Configuration::get_configuration_value('general_options','admin_logo');
		$logoSrc = "https://" . $_SERVER['SERVER_NAME'] . "/image/" . $adminLogoId;

		return $this->createEmailHtml($logoSrc);
	}
}

foreach(glob(CMSPATH . "/core/fields/Field_*.php") as $coreField) {
	$fileName = basename($coreField);
	$fileName = str_replace(".php", "", $fileName);
	$explodedFileName = explode("_", $fileName);
	Form::registerField($explodedFileName[1], $fileName);
}

foreach(glob(CMSPATH . "/user_classes/Field_*.php") as $userField) {
	$fileName = basename($userField);
	$fileName = str_replace(".php", "", $fileName);
	$explodedFileName = explode("_", $fileName);
	Form::registerField($explodedFileName[1], $fileName);
}