<?php
defined('CMSPATH') or die; // prevent unauthorized access

$cur_page = $_GET["page"] ?? 1;
$pagination_size = Configuration::get_configuration_value ('general_options', 'pagination_size') ?? 10;

$event_types = DB::fetchall("SELECT DISTINCT `type` AS text, `type` AS value FROM user_actions");
$search_form_object = json_decode(file_get_contents(CMSPATH . "/admin/controllers/audit/views/default/search_form.json"));
$search_form_object->fields[0]->select_options = $event_types;
$search_form_object->fields[] = (object) [
    "type"=>"HTML",
    "html"=>"<div style='display: flex; gap: 1rem;'>
                <button class='button is-info' type='submit'>Submit</button>
                <button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
            </div>"
];
$search_form = new Form($search_form_object);

if($search_form->is_submitted()) {
    $search_form->set_from_submit();
}

$actionClasses = glob(CMSPATH . "/core/actions/*.php");
$actionTypes = [];
foreach($actionClasses as $class) {
    $file = basename($class);
    $actionTypes[] = explode(".", (explode("_", $file)[1]))[0];
}
$actionTypesString = "'" . implode("','", $actionTypes) . "'";

$params = [];
$data_select = "SELECT * FROM user_actions";
$count_select = "SELECT count(*) AS count FROM user_actions";

$data_where = " WHERE `type` IN ({$actionTypesString})";

if($search_form->is_submitted()) {
    if($search_form->get_field_by_name("fieldtype")->default) {
        $data_where .= " AND `type`=?";
        $params[] = $search_form->get_field_by_name("fieldtype")->default;
    }

    if($search_form->get_field_by_name("start")->default) {
        $data_where .= " AND date>=?";
        $params[] = $search_form->get_field_by_name("start")->default;
    }

    if($search_form->get_field_by_name("end")->default) {
        $data_where .= " AND date<=?";
        $params[] = $search_form->get_field_by_name("end")->default;
    }
}

$count_where = $data_where;

$data_filter = " ORDER BY date DESC LIMIT {$pagination_size} OFFSET " . (($cur_page-1)*$pagination_size);
$count_filter = "";

$results = DB::fetchall($data_select . $data_where . $data_filter, $params);
$item_count = DB::fetch($count_select . $count_where . $count_filter, $params)->count;

//CMS::pprint_r($results);