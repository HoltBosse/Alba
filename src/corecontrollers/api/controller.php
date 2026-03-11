<?php

Use HoltBosse\Alba\Core\CMS;
Use HoltBosse\Alba\Core\Actions;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

header('Content-Type: application/json; charset=utf-8');

$response = [
    "data"=>[],
    "success"=>false,
    "msg"=>"invalid api endpoint",
];

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments) == 3 && $segments[1] == "actions" && $segments[2] == "add_action") {
    $stockErrorMsg = "invalid action request";

    $type = Input::getVar('type', v::stringType()->notEmpty(), null);
    $actionJson = Input::getVar('action', v::stringType()->notEmpty()->json(), null);

    if($type === null || $actionJson === null) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "invalid action request" : $stockErrorMsg,
        ]);
        exit();
    }

    $type = strtolower(trim((string) $type));

    try {
        $actionData = json_decode((string) $actionJson, false, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $exception) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "invalid action json" : $stockErrorMsg,
        ]);
        exit();
    }

    if(!v::objectType()->validate($actionData)) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "invalid action request" : $stockErrorMsg,
        ]);
        exit();
    }

    if(!v::in(Actions::getActionTypes())->validate($type)) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "unknown action type" : $stockErrorMsg,
        ]);
        exit();
    }

    if(!Actions::isApiAccessEnabledForType($type)) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "action type not enabled for api access" : $stockErrorMsg,
        ]);
        exit();
    }

    $csrfToken = Input::getVar("csrf_$type", v::stringType()->notEmpty(), null);

    if(!Actions::validateApiCsrfTokenForType($type, $csrfToken)) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "invalid csrf token" : $stockErrorMsg,
        ]);
        exit();
    }

    $schema = Actions::getActionDataSchemaForType($type);

    if(!$schema->validate($actionData)) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "action data failed schema validation" : $stockErrorMsg,
        ]);
        exit();
    }

    try {
        $actionId = Actions::add_action($type, $actionData);
    } catch (\Throwable $exception) {
        echo json_encode([
            "data"=>[],
            "success"=>false,
            "msg"=>$_ENV["debug"]==="true" ? "failed to add action" : $stockErrorMsg,
        ]);
        exit();
    }

    echo json_encode([
        "data"=>[],
        "success"=>true,
        "msg"=>"action added",
    ]);
    exit();
}

if(sizeof($segments)==2 && $segments[1]=="parsedown") {

    //user must be logged in to use this api, to prevent against abuse
    if(CMS::Instance()->user->id===null) {
        echo json_encode($response);
    }

    if(Input::getVar("markup", v::StringVal(), null)) {

        /* $markup = '
        Welcome to the demo:

        1. Write Markdown text on the left
        2. Hit the __Parse__ button or ***~~thing~~***
        3. See the result to on the right
        '; */

        $Parsedown = new Parsedown();

        $response = [
            "data"=>[
                "html"=>urlencode($Parsedown->text(urldecode(Input::getVar("markup", v::StringVal(), '')))),
            ],
            "success"=>true,
            "msg"=>"markdown converted",
        ];
    }
}

echo json_encode($response);