<?php
defined('CMSPATH') or die; // prevent unauthorized access

$requiredFieldsObject = json_decode(file_get_contents(CMSPATH . "/admin/controllers/content/views/edit/required_fields_form.json"));
$requiredFieldsForm = new Form($requiredFieldsObject);

$formOptionsForm = new Form(CMSPATH . "/admin/controllers/forms/views/edit/form_options.json");