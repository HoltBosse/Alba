<?php

use HoltBosse\Alba\Core\{CMS, Form};
use HoltBosse\Alba\Fields\UserPicker\UserPicker;
use HoltBosse\Alba\Fields\PageSelect\PageSelect;
use \Exception;
use HoltBosse\Form\{Input, FormBuilderAttribute};
use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
$formConfigPath = $_ENV["root_path_to_forms"] . "/forms/";

if(!is_dir($formConfigPath)) {
    mkdir($formConfigPath, 0755, true);
}

if(sizeof($segments)==3 && is_numeric($segments[2])){
    $formId = intval($segments[2]);
} elseif(sizeof($segments)==3 && $segments[2]=="new"){
    $formId = null;
} else {
    CMS::Instance()->queue_message('Unknown form operation','danger',$_ENV["uripath"].'/admin');
	exit(0);
}

$requiredDetailsForm = new Form(__DIR__ . "/required_fields_form.json");

CMS::Instance()->head_entries[] = '<script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.7/dist/htmx.min.js"></script>';

$emailField = new UserPicker();
$emailField->loadFromConfig((object) [
    "name"=>"emails",
    "id"=>"emails",
    "label"=>"Email",
    "type"=>"UserPicker",
    "description"=>"Select users to notify when this form is submitted",
    "required"=>false,
    "multiple"=>true
]);

$formSubmitPageField = new PageSelect();
$formSubmitPageField->loadFromConfig((object) [
    "name"=>"form_submit_page",
    "id"=>"form_submit_page",
    "label"=>"Form Submit Page",
    "type"=>"PageSelect",
    "description"=>"Select the page to redirect to after form submission",
    "required"=>true,
    "multiple"=>false,
    "slimselect"=>true,
]);

if($formId) {
    $formData = DB::fetch("SELECT * FROM form_instances WHERE id=?", [$formId]);

    if(!$formData) {
        CMS::Instance()->queue_message('Form not found','danger',$_ENV["uripath"].'/admin/forms/all');
        exit(0);
    }

    $requiredDetailsForm->getFieldByName("title")->default = $formData->title;
    $requiredDetailsForm->getFieldByName("alias")->default = $formData->alias;
    $requiredDetailsForm->getFieldByName("state")->default = $formData->state;

    if(!empty($formData->emails)) {
        $emailField->default = json_encode(explode(",", $formData->emails));
    }

    $formSubmitPageField->default = $formData->submit_page;
}

if($requiredDetailsForm->isSubmitted()) {
    $requiredDetailsForm->setFromSubmit();

    if($requiredDetailsForm->validate()/* check for form details??? */) {
        $aliasField = $requiredDetailsForm->getFieldByName("alias");
        if(empty($aliasField->default)) {
            $aliasField->default = Input::makeAlias($requiredDetailsForm->getFieldByName("title")->default);
            
            $aliasExists = DB::fetch("SELECT id FROM form_instances WHERE alias=?", [$aliasField->default]);
            if($aliasExists) {
                $aliasField->default .= "-" . uniqid();

                CMS::Instance()->queue_message('Added random suffix to "URL Friendly" field to ensure uniqueness.','warning');
            }
        }


        $formFields = [];

        $submittedFields = Input::getVar("fields", v::ArrayType()->each(v::StringVal()), []);
        $submittedFieldTypes = Input::getVar("fieldtypes", v::ArrayType()->each(v::Json()), []);

        foreach($submittedFields as $index=>$fieldType) {
            if(!empty($submittedFieldTypes[$index])) {
                try {
                    $fieldDetails = json_decode($submittedFieldTypes[$index], null, 512, JSON_THROW_ON_ERROR);
                    $normalizedDetails = array_combine(array_column($fieldDetails, 'name'), array_column($fieldDetails, 'value'));
                } catch(Exception $e) {
                    continue;
                }

                CMS::pprint_r($normalizedDetails);

                if(!empty(Form::getFieldClass($fieldType))) {
                    $fieldReflection = new ReflectionClass(Form::getFieldClass($fieldType));
                    //$propertiesWithAttributes = [];
                    $fieldInstance = [];

                    foreach ($fieldReflection->getProperties() as $property) {
                        $attributes = $property->getAttributes(FormBuilderAttribute::class);
                        if (!empty($attributes)) {
                            $fieldInstance[$property->getName()] = $normalizedDetails[$property->getName()] ?? null;
                        }
                    }

                    $fieldInstance["type"] = $fieldType;

                    $formFields[] = (object) $fieldInstance;
                }
            }
        }

        if(empty($formFields)) {
            CMS::Instance()->queue_message('At least one form field is required','danger', $_ENV["uripath"].'/admin/forms/all');
            exit(0);
        }

        $emails = Input::getVar("emails", v::arrayType()->each(v::NumericVal()), []);
        $emailsCsv = !empty($emails) ? implode(",", $emails) : null;
        if($formId) {
            DB::exec(
                "UPDATE form_instances SET title=?, alias=?, updated_by=?, emails=?, submit_page=? WHERE id=?",
                [
                    $requiredDetailsForm->getFieldByName("title")->default,
                    $aliasField->default,
                    CMS::Instance()->user->id,
                    $emailsCsv,
                    Input::getVar("form_submit_page", v::NumericVal(), null),
                    $formId
                ]
            );
        } else {
            DB::exec(
                "INSERT INTO form_instances (title, alias, created_by, updated_by, emails, submit_page, location, state) VALUES (?,?,?,?,?,?,?,?)",
                [
                    $requiredDetailsForm->getFieldByName("title")->default,
                    $aliasField->default,
                    CMS::Instance()->user->id,
                    CMS::Instance()->user->id,
                    $emailsCsv,
                    Input::getVar("form_submit_page", v::NumericVal(), null),
                    "", //create this below
                    1
                ]
            );

            $formId = DB::getLastInsertedId();

            DB::exec(
                "UPDATE form_instances SET location=? WHERE id=?",
                [
                    "/forms/form_instance_" . $formId,
                    $formId
                ]
            );
        }

        $formObject = (object) [
            "id"=>"form_instance_" . $formId,
            "display_name"=>$requiredDetailsForm->getFieldByName("title")->default,
            "fields"=>$formFields
        ];

        $fileHandle = fopen($formConfigPath . "form_instance_" . $formId . ".json", "w");
        fwrite($fileHandle, json_encode($formObject, JSON_PRETTY_PRINT));
        fclose($fileHandle);

        CMS::Instance()->queue_message('Form saved successfully','success',$_ENV["uripath"].'/admin/forms/all');
    } else {
        CMS::Instance()->queue_message('Invalid form','danger');
    }
}