<?php

Use HoltBosse\Alba\Core\CMS;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

header('Content-Type: application/json; charset=utf-8');

$response = [
    "data"=>[],
    "success"=>false,
    "msg"=>"invalid api endpoint",
];

$segments = CMS::Instance()->uri_segments;

if(sizeof($segments)==2 && $segments[1]=="parsedown") {

    //user must be logged in to use this api, to prevent against abuse
    if(CMS::Instance()->user->id===false) {
        echo json_encode($response);
    }

    if(isset($_GET["markup"])) {

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