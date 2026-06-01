<?php
use HoltBosse\Alba\Core\{CMS, AttributeForm};
use HoltBosse\Form\{Input, Form};
use Respect\Validation\Validator as v;

ob_get_clean();
ob_get_clean();

require_once(__DIR__ . "/../../components.php");

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) < 3) {
    http_response_code(400);
    die("Invalid request");
}

if($segments[2]=="get-component-form" && sizeof($segments)==3) {
    $config = Input::getvar("config", v::stringType()->notEmpty(), null);
    $component = Input::getvar("component", v::stringType()->notEmpty()->in(array_keys($availableComponentsFlat)), null);

    if(!$component || !$config) {
        http_response_code(400);
        die("Invalid input");
    }

    $componentForm = AttributeForm::generateFormForClass($availableComponentsFlat[$component]);
    $componentForm->deserializeJson($config);
    echo "<form action='/admin/pages/api/get-component-json?component=$component'>";
        $componentForm->display();
    echo "</form>";
    die;
}

if(sizeof($segments) == 3 && $segments[2] == "get-component-json") {
    $component = Input::getvar("component", v::stringType()->notEmpty()->in(array_keys($availableComponentsFlat)), null);
    if(!$component) {
        http_response_code(400);
        die("Invalid input");
    }

    $componentClass = $availableComponentsFlat[$component];
    $componentForm = AttributeForm::generateFormForClass($componentClass);
    $componentForm->setFromSubmit();

    header("Content-Type: application/json");
    echo json_encode((object) ["config" => $componentForm]);
    die;
}

http_response_code(404);
die;