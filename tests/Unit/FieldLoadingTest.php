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

test('registers built-in form fields and loads controller basic article config without exceptions', function () {
    // Call the CMS method to register built-in form fields
    CMS::registerBuiltInFormFields();
    
    // Load the basic_article controller custom_fields.json file into a Form class
    $basicArticleConfigPath = __DIR__ . '/../../src/controllers/basic_article/custom_fields.json';
    
    // Assert that the file exists
    expect(file_exists($basicArticleConfigPath))->toBeTrue();
    
    // This should not throw any exceptions - the form should load successfully
    $form = new Form($basicArticleConfigPath);
    
    // Verify the form was loaded correctly
    expect($form)->toBeInstanceOf(Form::class);
    expect($form->id)->toBe('basic_html');
    
    // Verify some key fields were loaded and configured correctly
    expect($form->fieldExists('markup'))->toBeTrue();
    $markupField = $form->getFieldByName('markup');
    expect($markupField->type)->toBe('Rich');
    expect($markupField->label)->toBe('Content');
    expect($markupField->required)->toBeTrue();
    
    expect($form->fieldExists('og_description'))->toBeTrue();
    $ogDescField = $form->getFieldByName('og_description');
    expect($ogDescField->type)->toBe('Textarea');
    expect($ogDescField->label)->toBe('Opengraph / Search Engine Description');
    expect($ogDescField->required)->toBeFalse();
    
    expect($form->fieldExists('og_image'))->toBeTrue();
    $ogImageField = $form->getFieldByName('og_image');
    expect($ogImageField->type)->toBe('Image');
    expect($ogImageField->label)->toBe('Opengraph Image');
    expect($ogImageField->required)->toBeFalse();
    
    // Verify the form has the expected number of fields
    expect(count($form->fields))->toBe(7);
});

test('registers built-in form fields and loads content required fields config without exceptions', function () {
    // Call the CMS method to register built-in form fields
    CMS::registerBuiltInFormFields();
    
    // Load the content required fields form configuration
    $requiredFieldsConfigPath = __DIR__ . '/../../src/admin/controllers/content/views/edit/required_fields_form.json';
    
    // Assert that the file exists
    expect(file_exists($requiredFieldsConfigPath))->toBeTrue();

    // Remove the category and tags field to prevent database errors during testing
    $requiredFieldsConfig = json_decode(file_get_contents($requiredFieldsConfigPath));
    unset($requiredFieldsConfig->fields[2]);
    unset($requiredFieldsConfig->fields[3]);
    //print_r($requiredFieldsConfig);
    
    // This should not throw any exceptions - the form should load successfully
    $form = new Form($requiredFieldsConfig);
    
    // Verify the form was loaded correctly
    expect($form)->toBeInstanceOf(Form::class);
    expect($form->id)->toBe('required_content_fields');
    
    // Verify key fields were loaded and configured correctly
    expect($form->fieldExists('title'))->toBeTrue();
    $titleField = $form->getFieldByName('title');
    expect($titleField->type)->toBe('Text');
    expect($titleField->label)->toBe('Content Title');
    expect($titleField->required)->toBeTrue();
    expect($titleField->maxlength)->toBe(255);
    
    expect($form->fieldExists('alias'))->toBeTrue();
    $aliasField = $form->getFieldByName('alias');
    expect($aliasField->type)->toBe('Text');
    expect($aliasField->label)->toBe('URL Friendly');
    
    expect($form->fieldExists('state'))->toBeTrue();
    $stateField = $form->getFieldByName('state');
    expect($stateField->type)->toBe('Select');
    expect($stateField->label)->toBe('Content State');
    
    // Verify the form has the expected number of fields (6 after removing category)
    expect(count($form->fields))->toBe(6);
});

test('registers built-in form fields and loads widget required fields config without exceptions', function () {
    // Call the CMS method to register built-in form fields
    CMS::registerBuiltInFormFields();
    
    // Load the widget required fields form configuration
    $widgetRequiredFieldsConfigPath = __DIR__ . '/../../src/admin/controllers/widgets/views/edit/required_fields_form.json';
    
    // Assert that the file exists
    expect(file_exists($widgetRequiredFieldsConfigPath))->toBeTrue();
    
    // This should not throw any exceptions - the form should load successfully
    $form = new Form($widgetRequiredFieldsConfigPath);
    
    // Verify the form was loaded correctly
    expect($form)->toBeInstanceOf(Form::class);
    expect($form->id)->toBe('required_widget_fields');
    
    // Verify key fields were loaded and configured correctly
    expect($form->fieldExists('title'))->toBeTrue();
    $titleField = $form->getFieldByName('title');
    expect($titleField->type)->toBe('Text');
    expect($titleField->label)->toBe('Widget Title');
    expect($titleField->required)->toBeTrue();
    expect($titleField->maxlength)->toBe(255);
    
    expect($form->fieldExists('note'))->toBeTrue();
    $noteField = $form->getFieldByName('note');
    expect($noteField->type)->toBe('Text');
    expect($noteField->label)->toBe('Note');
    expect($noteField->required)->toBeFalse();
    expect($noteField->maxlength)->toBe(255);
    
    expect($form->fieldExists('state'))->toBeTrue();
    $stateField = $form->getFieldByName('state');
    expect($stateField->type)->toBe('Select');
    expect($stateField->label)->toBe('Widget State');
    expect($stateField->default)->toBe(1);
    
    // Verify the form has the expected number of fields
    expect(count($form->fields))->toBe(3);
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
