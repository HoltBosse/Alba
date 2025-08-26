<?php

use HoltBosse\Alba\Core\CMS;
use HoltBosse\Form\{Form, Field};

CMS::registerBuiltInFormFields();

$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src'));
foreach ($iterator as $file) {
    if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
        $file = realpath($file->getPathname());
        $contents = json_decode(file_get_contents($file));

        if(is_object($contents) && isset($contents->fields) && sizeof($contents->fields) > 0) {
            $files[] = $file;
        }

    }
}

test("Fields are valid", function ($file) {
    $contents = json_decode(file_get_contents($file));

    foreach($contents->fields as $field) {
        expect(new (Form::getFieldClass($field->type)))->toBeInstanceOf(Field::class);
    }
})->with($files);