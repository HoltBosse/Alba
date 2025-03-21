<?php
defined('CMSPATH') or die; // prevent unauthorized access

$requiredFieldsObject = json_decode(file_get_contents(CMSPATH . "/admin/controllers/content/views/edit/required_fields_form.json"));
$requiredFieldsForm = new Form($requiredFieldsObject);

$formOptionsForm = new Form(CMSPATH . "/admin/controllers/forms/views/edit/form_options.json");

if($requiredFieldsForm->is_submitted() && $formOptionsForm->is_submitted()) {
    $requiredFieldsForm->set_from_submit();
    $formOptionsForm->set_from_submit();

    //check for update and do that first
    //also replace json form name with alias

    DB::exec(
        "INSERT INTO form_instances
        (state, ordering, title, alias, content_type, created_by, updated_by, note, category, cc, bcc, form_json)
        VALUES
        (?,?,?,?,?,?,?,?,?,?,?,?)",
        [
            1,
            0,
            $requiredFieldsForm->fields["title"]->default,
            $requiredFieldsForm->fields["alias"]->default, //turn into actual alias
            -4,
            CMS::Instance()->user->id,
            CMS::Instance()->user->id,
            $requiredFieldsForm->fields["note"]->default,
            $requiredFieldsForm->fields["category"]->default,
            $formOptionsForm->fields["cc"]->default,
            $formOptionsForm->fields["bcc"]->default,
            Input::getvar("from_json")
        ]
    );
}