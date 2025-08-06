<?php

use HoltBosse\Alba\Core\CMS;
use HoltBosse\Form\Form;

test('registers built-in form fields and loads HTML widget config without exceptions', function () {
    // Call the CMS method to register built-in form fields
    CMS::registerBuiltInFormFields();
    
    // Load the HTML widget config file into a Form class
    // This should automatically call loadFromConfig method for each field
    $widgetConfigPath = __DIR__ . '/../../src/Widgets/Html/widget_config.json';
    
    // Assert that the file exists
    expect(file_exists($widgetConfigPath))->toBeTrue();
    
    // This should not throw any exceptions - the form should load successfully
    $form = new Form($widgetConfigPath);
    
    // Verify the form was loaded correctly
    expect($form)->toBeInstanceOf(Form::class);
    expect($form->id)->toBe('widget_html_options');
    expect($form->displayName)->toBe('widget_html_options');
    
    // Verify the field was loaded and loadFromConfig was called
    expect($form->fieldExists('markup'))->toBeTrue();
    $markupField = $form->getFieldByName('markup');
    expect($markupField->type)->toBe('Rich');
    expect($markupField->label)->toBe('Content');
    expect($markupField->required)->toBeTrue();
    expect($markupField->filter)->toBe('RAW');
});

test('registers built-in form fields and loads MENU widget config without exceptions', function () {
    // Call the CMS method to register built-in form fields
    CMS::registerBuiltInFormFields();
    
    // Test loading Menu widget config (has no fields)
    $menuWidgetPath = __DIR__ . '/../../src/Widgets/Menu/widget_config.json';
    expect(file_exists($menuWidgetPath))->toBeTrue();
    
    // This should not throw any exceptions even for empty fields array
    $menuForm = new Form($menuWidgetPath);
    
    // Verify the form was loaded correctly
    expect($menuForm)->toBeInstanceOf(Form::class);
    expect($menuForm->id)->toBe('widget_menu_options');
    expect($menuForm->displayName)->toBe('widget_menu_options');
    
    // Verify empty fields array is handled correctly
    expect(count($menuForm->fields))->toBe(0);
});

test('form field registration allows field creation from config', function () {
    // Call the CMS method to register built-in form fields
    CMS::registerBuiltInFormFields();
    
    // Create a simple form configuration that uses built-in field types
    $simpleFormConfig = (object) [
        'id' => 'test_form',
        'display_name' => 'Test Form',
        'fields' => [
            (object) [
                'type' => 'Text',
                'name' => 'test_input',
                'label' => 'Test Input',
                'required' => true,
                'filter' => 'STRING'
            ],
            (object) [
                'type' => 'Html',
                'html' => '<p>Test HTML content</p>'
            ]
        ]
    ];
    
    // This should not throw any exceptions - the registered fields should be available
    $form = new Form($simpleFormConfig);
    
    // Verify the form was created successfully
    expect($form)->toBeInstanceOf(Form::class);
    expect($form->id)->toBe('test_form');
    expect($form->displayName)->toBe('Test Form');
    
    // Verify the text field was created and configured
    expect($form->fieldExists('test_input'))->toBeTrue();
    $textField = $form->getFieldByName('test_input');
    expect($textField->type)->toBe('Text');
    expect($textField->label)->toBe('Test Input');
    expect($textField->required)->toBeTrue();
    expect($textField->filter)->toBe('STRING');
    
    // Verify HTML field was created (won't have a name, so check by index)
    expect(count($form->fields))->toBe(2);
});
