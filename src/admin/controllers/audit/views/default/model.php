<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Actions, Hook, File};
Use HoltBosse\Form\Form;
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

$cur_page = Input::getVar("page", v::numericVal(), 1);
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size') ?? 10;

$event_types = DB::fetchAll("SELECT DISTINCT `type` AS text, `type` AS value FROM user_actions");
$search_form_object = json_decode(File::getContents(__DIR__ . "/search_form.json"));
$search_form_object->fields[0]->select_options = $event_types;
$search_form_object->fields[] = (object) [
    "type"=>"Html",
    "html"=>"<div style='display: flex; gap: 1rem;'>
                <button class='button is-info' type='submit'>Submit</button>
                <button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
            </div>"
];

$search_form_object = Hook::execute_hook_filters('admin_search_form_object', $search_form_object);

$search_form = new Form($search_form_object);

if($search_form->isSubmitted()) {
    $search_form->setFromSubmit();
}

$actionTypesString = "'" . implode("','", Actions::getActionTypes()) . "'";

$params = [];
$data_select = "SELECT * FROM user_actions";
$count_select = "SELECT count(*) AS count FROM user_actions";

$data_where = " WHERE `type` IN ({$actionTypesString})";

if($search_form->isSubmitted()) {
    if($search_form->getFieldByName("fieldtype")->default) {
        $data_where .= " AND `type`=?";
        $params[] = $search_form->getFieldByName("fieldtype")->default;
    }

    if($search_form->getFieldByName("start")->default) {
        $data_where .= " AND date>=?";
        $params[] = $search_form->getFieldByName("start")->default;
    }

    if($search_form->getFieldByName("end")->default) {
        $data_where .= " AND date<=?";
        $params[] = $search_form->getFieldByName("end")->default;
    }
}

$count_where = $data_where;

$data_filter = " ORDER BY date DESC LIMIT {$pagination_size} OFFSET " . (($cur_page-1)*$pagination_size);
$count_filter = "";

$results = DB::fetchAll($data_select . $data_where . $data_filter, $params);
$item_count = DB::fetch($count_select . $count_where . $count_filter, $params)->count;

//CMS::pprint_r($results);