<?php

use HoltBosse\Alba\Core\{CMS, File, Form};
use \ReflectionClass;
use HoltBosse\Form\FormBuilderDataType;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

//recursively order properties by inheritance
// @phpstan-ignore-next-line
function getOrderedProperties(ReflectionClass $rc): array {
    $properties = [];
    $parent = $rc->getParentClass();
    if ($parent) {
        $properties = getOrderedProperties($parent);
    }

    foreach ($rc->getProperties() as $prop) {
        if ($prop->getDeclaringClass()->getName() === $rc->getName()) {
            $properties[] = $prop;
        }
    }

    //filter out duplicate properties, prefering the child class version
    $seen = [];
    $properties = array_reverse(array_values(array_filter(array_reverse($properties), function($prop) use (&$seen) {
        $name = $prop->getName();
        if (isset($seen[$name])) {
            return false;
        }
        $seen[$name] = true;
        return true;
    })));

    return $properties;
}

function generateFormForFieldType(string $fieldType): Form {
    $formObject = (object) [
        "id"=>"fieldconfig",
        "fields"=>[]
    ];

    // @phpstan-ignore argument.type
    $reflection = new ReflectionClass(Form::getFieldClass($fieldType));

    foreach(getOrderedProperties($reflection) as $property) {
        $attributes = $property->getAttributes();
        if(sizeof($attributes) == 0) {
            continue;
        }
        //CMS::pprint_r($property->getName());

        foreach ($attributes as $attribute) {
            if($attribute->getName() == "HoltBosse\Form\FormBuilderAttribute") {
                $attrInstance = $attribute->newInstance();
                //CMS::pprint_r($attrInstance);

                $field = [
                    "name"=>$property->getName(),
                    "type"=>$attrInstance->fieldType,
                    "label"=>$attrInstance->label ?? ucwords(str_replace("_", " ", $property->getName())),
                    "required"=>$attrInstance->required,
                    "description"=>$attrInstance->description ?? null,
                ];

                $field = (object) array_merge($field, $attrInstance->config ?? []);

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