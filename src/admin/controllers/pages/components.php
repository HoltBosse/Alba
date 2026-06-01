<?php

//todo: improve this/dehardcode this
$availableComponents = [
    "layout"=>[
        "collection"=>"HoltBosse\Alba\Components\Collection\Collection",
    ],
    "actions"=>[
        "button"=>"HoltBosse\Alba\Components\Button\Button",
        "removeme"=>"HoltBosse\Alba\Components\Button\Button",
    ],
    "other"=>[
        "image"=>"HoltBosse\Alba\Components\Image\Image",
    ]
];

$availableComponentsFlat = [];
foreach($availableComponents as $category=>$components) {
    foreach($components as $name=>$class) {
        $availableComponentsFlat[$name] = $class;
    }
}