<?php

use HoltBosse\Alba\Core\{CMS, File, Form, AttributeForm};
use \ReflectionClass;
use HoltBosse\Form\FormBuilderDataType;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

function generateFormForFieldType(string $fieldType): Form {
    // @phpstan-ignore argument.type
    return AttributeForm::generateFormForClass(Form::getFieldClass($fieldType));
}

function getFieldOptionsForm(Form $form, string $fieldType, string $prefixMarkup=""): string {
    $formMarkup = "";
    $formMarkup .= "<form style='width: 100%;' onsubmit='return false;' action='/admin/forms/api/fieldoptionsformsubmit/$fieldType'>";
        $formMarkup .= $prefixMarkup;
        ob_start();
            $form->display();
        $formMarkup.= ob_get_clean();
    $formMarkup.= "</form>";
    return $formMarkup;
}

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments)==4 && $segments[2]=="fieldoptions") {
    $jsonConfig = json_decode(File::getContents(__DIR__ . "/../edit/config.json"));

    if($segments[3]=="none") {
        echo "<p>Select Field</p>";
    } elseif(in_array($segments[3], $jsonConfig->allowedFields)) {
        $index = Input::getVar("index", v::numericVal(), 0);
        $submittedFieldTypes = Input::getVar("fieldtypes", v::ArrayType(), []);

        $form = generateFormForFieldType($segments[3]);
        if($submittedFieldTypes[$index]) {
            $form->deserializeJson($submittedFieldTypes[$index]);
        }
        echo getFieldOptionsForm($form, $segments[3]);
    } else {
        echo "<p>Unknown Field Type</p>";
    }
} elseif(sizeof($segments)==4 && $segments[2]=="fieldoptionsformsubmit") {
    $form = generateFormForFieldType($segments[3]);
    $form->setFromSubmit();

    if($form->validate()) {
        $response = (object) [
            "status"=>1,
            "data"=>json_encode($form),
            "html"=>"",
        ];

        echo json_encode($response);
    } else {
        $response = (object) [
            "status"=>0,
            "data"=>"{}",
            "html"=>getFieldOptionsForm($form, $segments[3], "<div class='alert notification is-danger'>invalid form field</div>" . print_r($_POST, true)),
        ];

        echo json_encode($response);
    }
}

die;