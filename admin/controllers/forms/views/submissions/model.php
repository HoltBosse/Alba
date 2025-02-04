<?php
defined('CMSPATH') or die; // prevent unauthorized access

$paginationSize = Configuration::get_configuration_value ('general_options', 'pagination_size');

$curPage = Input::getvar('page','INT','1');

$searchFieldsObject = json_decode(file_get_contents(CMSPATH . "/admin/controllers/forms/views/submissions/search_fields.json"));
$searchFieldsObject->fields[0]->select_options = DB::fetchall("SELECT DISTINCT form_id AS text, form_id as value FROM form_submissions");
if(!$_GET["form"] && $searchFieldsObject->fields[0]->select_options[0]) {
    $searchFieldsObject->fields[0]->default = $searchFieldsObject->fields[0]->select_options[0]->value;
}
$searchFieldsObject->fields[] = (object) [
    "type"=>"HTML",
    "html"=>"<div style='display: flex; gap: 1rem;'>
                <button class='button is-info' type='submit'>Submit</button>
                <button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
            </div>"
];
$searchFieldsForm = new Form($searchFieldsObject);

$query = "SELECT * FROM form_submissions WHERE 1";
$queryWhereFilters = "";
$params = [];

if($searchFieldsForm->is_submitted()) {
    $searchFieldsForm->set_from_submit();

    if($searchFieldsForm->get_field_by_name("form")->default) {
        $queryWhereFilters .= " AND form_id=?";
        $params[] = $searchFieldsForm->get_field_by_name("form")->default;
    }

    if($searchFieldsForm->get_field_by_name("start")->default) {
        $queryWhereFilters .= " AND created >= ?";
        $params[] = $searchFieldsForm->get_field_by_name("start")->default;
    }

    if($searchFieldsForm->get_field_by_name("end")->default) {
        $queryWhereFilters .= " AND created <= ?";
        $params[] = $searchFieldsForm->get_field_by_name("end")->default;
    }
} else {
    $queryWhereFilters .= " AND form_id=?";
    $params[] = $searchFieldsObject->fields[0]->select_options[0]->value;
}

$countResults = DB::fetch("SELECT count(*) AS c FROM form_submissions WHERE 1" . $queryWhereFilters, $params)->c;

$paginationQuery = " LIMIT $paginationSize";

$offset = ($curPage-1) * $paginationSize;
$paginationQuery .= " OFFSET $offset";

$results = DB::fetchall($query . $queryWhereFilters . $paginationQuery, $params);

$headerFields = [];
if($results[0]) {
    $currentSelectedObject = json_decode(file_get_contents(CMSPATH . $results[0]->form_path));
    if($currentSelectedObject->list) {
        $headerFields = $currentSelectedObject->list;
    } else {
        foreach($currentSelectedObject->fields as $field) {
            if($field->save!==false) {
                $headerFields[] = $field->name;
            }
        }
    }
}

if($_GET["exportcsv"]) {
    ob_get_clean();
    ob_get_clean();

    $filename = "form_export_" . date("Y-m-d") . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $results = DB::fetchall("SELECT * FROM form_submissions WHERE 1" . $queryWhereFilters, $params);

    echo implode(",", $headerFields) . "\n";
    foreach($results as $row) {
        $buffer = [];
        foreach($headerFields as $header) {
            $data = json_decode($row->data);
            $normalizedFields = array_combine(array_column($data, 'name'), array_column($data, 'value'));
            $value = $normalizedFields[$header];
            $value = str_replace(",", ".", ($value));
            $value = str_replace("\n", " ", ($value));
            $value = str_replace("\r", " ", ($value));
            $value = str_replace("\r\n", " ", ($value));
            $value = str_replace("\n\r", " ", ($value));
            $buffer[] = $value;
        }
        echo implode(",", $buffer) . "\n";
    }

    die;
}