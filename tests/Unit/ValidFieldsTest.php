<?php

use HoltBosse\Alba\Core\{CMS, File};
use HoltBosse\Form\{Form, Field};

CMS::registerBuiltInFormFields();

$files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../src'));
foreach ($iterator as $file) {
    if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
        $file = File::realpath($file->getPathname());
        $contents = json_decode(File::getContents($file));

        if(is_object($contents) && isset($contents->fields) && sizeof($contents->fields) > 0) {
            $files[] = $file;
        }

    }
}

test("Fields are valid", function ($file) {
    $contents = json_decode(File::getContents($file));

    foreach($contents->fields as $field) {
        expect(new (Form::getFieldClass($field->type)))->toBeInstanceOf(Field::class);
    }
// @phpstan-ignore method.notFound
})->with($files);