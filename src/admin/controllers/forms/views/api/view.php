<?php

use HoltBosse\Alba\Core\{CMS, Form};
use \ReflectionClass;
use HoltBosse\Form\FormBuilderDataType;

function generateFormForFieldType($fieldType): Form {
    $formObject = (object) [
        "id"=>"fieldconfig",
        "fields"=>[]
    ];

    $reflection = new ReflectionClass(Form::getFieldClass($fieldType));

    foreach ($reflection->getProperties() as $property) {
        $attributes = $property->getAttributes();
        if(sizeof($attributes) == 0) {
            continue;
        }
        //CMS::pprint_r($property->getName());

        foreach ($attributes as $attribute) {
            if($attribute->getName() == "HoltBosse\Form\FormBuilderAttribute") {
                $attrInstance = $attribute->newInstance();
                //CMS::pprint_r($attrInstance);

                $field = (object) [
                    "name"=>$property->getName(),
                    "type"=>$attrInstance->fieldType,
                    "label"=>$attrInstance->label ?? ucwords(str_replace("_", " ", $property->getName())),
                    "required"=>$attrInstance->required,
                    "description"=>$attrInstance->description ?? null,
                ];

                if($attrInstance->dataType === FormBuilderDataType::Bool && $attrInstance->fieldType == "Select") {
                    $field->select_options = [
                        (object) ["value"=>1, "text"=>"True"],
                        (object) ["value"=>0, "text"=>"False"]
                    ];
                }

                if($attrInstance->dataType === FormBuilderDataType::LetterString && $attrInstance->fieldType == "Text") {
                    $field->pattern = "^[a-z]+$";
                    $field->description = "Only letters (a-z) are allowed.";
                }

                $formObject->fields[] = $field;
            }
            /* CMS::pprint_r($attribute);
            $attrInstance = $attribute->newInstance();
            CMS::pprint_r($attrInstance); */
        }
    }

    $formObject->fields[] = (object) [
        "type"=>"Html",
        "html"=>"<br><div class='button is-primary field-update'>Update Field</div>"
    ];

    $form = new Form($formObject);
    return $form;
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
    $jsonConfig = json_decode(file_get_contents(__DIR__ . "/../edit/config.json"));

    //CMS::pprint_r($_POST);

    if($segments[3]=="none") {
        echo "<p>Select Field</p>";
    } elseif(in_array($segments[3], $jsonConfig->allowedFields)) {
        $index = isset($_GET["index"]) ? intval($_GET["index"]) : 0; //intval forces to 0 if junk

        $form = generateFormForFieldType($segments[3]);
        $form->deserializeJson($_POST["fieldtypes"][$index]);
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