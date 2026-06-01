<?php
namespace HoltBosse\Alba\Core;

use HoltBosse\Alba\Core\{CMS, File, Form};
use \ReflectionClass;
use HoltBosse\Form\FormBuilderDataType;
Use HoltBosse\Form\Input;
use Respect\Validation\Validator as v;
use \InvalidArgumentException;

class AttributeForm extends Form {
    // @phpstan-ignore-next-line
    public static function getOrderedProperties(ReflectionClass $rc): array {
        //recursively order properties by inheritance
        $properties = [];
        $parent = $rc->getParentClass();
        if ($parent) {
            $properties = self::getOrderedProperties($parent);
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

    /**
     * @param class-string $class
    */
    public static function generateFormForClass(string $class, bool $setDefaults = false): Form {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class does not exist: $class");
        }

        $formObject = (object) [
            "id"=>"fieldconfig",
            "fields"=>[]
        ];

        $reflection = new ReflectionClass($class);

        foreach(self::getOrderedProperties($reflection) as $property) {
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

                    if($setDefaults) {
                        //check if the type is non nullable
                        $isNonNullable = !$property->getType()->allowsNull();
                        if($isNonNullable) {
                            $defaultValue = null;
                            //cast default value to correct type based on dataType. i.e. (int) for integer, (string) for string, etc
                            switch($attrInstance->dataType) {
                                case FormBuilderDataType::Integer:
                                    $defaultValue = (int) $defaultValue;
                                    break;
                                case FormBuilderDataType::String:
                                    $defaultValue = (string) $defaultValue;
                                    break;
                                case FormBuilderDataType::Bool:
                                    $defaultValue = (bool) $defaultValue;
                                    break;
                                case FormBuilderDataType::LetterString:
                                    $defaultValue = (string) $defaultValue;
                                    break;
                                default:
                                    $defaultValue = null;
                            }

                            //check if the property has a default value in the class definition and use that if available
                            if ($property->hasDefaultValue()) {
                                $defaultValue = $property->getDefaultValue();
                            }

                            $field->default = $defaultValue;
                        }
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
            "html"=>"<br><div class='button is-primary class-update'>Update</div>"
        ];

        $form = new Form($formObject);
        return $form;
    }
}