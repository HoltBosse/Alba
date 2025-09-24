<?php

Use HoltBosse\Alba\Core\{CMS, Configuration};
Use HoltBosse\Form\{Input, Form};
Use HoltBosse\DB\DB;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments) > 2) {
    CMS::raise_404();
}

$paginationSize = Configuration::get_configuration_value ('general_options', 'pagination_size');

$curPage = Input::getvar('page','INT','1');

$formSelectOptions = DB::fetchAll("SELECT DISTINCT form_id AS value, form_path FROM form_submissions");
$formSelectOptions = array_map(function($input) {
    $option = (object) [
        "value"=>$input->value,
        "text"=>$input->value,
    ];

    $json = json_decode(file_get_contents($_ENV["root_path_to_forms"] . $input->form_path));
    if(isset($json->display_name)) {
        $option->text = $json->display_name;
    }

    return $option;
    
}, $formSelectOptions);

$searchFieldsObject = json_decode(file_get_contents(__DIR__ . "/search_fields.json"));
$searchFieldsObject->fields[0]->select_options = $formSelectOptions;
// @phpstan-ignore-next-line
if(!isset($_GET["form"]) && $searchFieldsObject->fields[0]->select_options[0]) {
    $searchFieldsObject->fields[0]->default = $searchFieldsObject->fields[0]->select_options[0]->value;
}
$searchFieldsObject->fields[] = (object) [
    "type"=>"Html",
    "html"=>"<div style='display: flex; gap: 1rem;'>
                <button class='button is-info' type='submit'>Search</button>
                <button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
            </div>"
];
$searchFieldsForm = new Form($searchFieldsObject);

$query = "SELECT * FROM form_submissions WHERE 1";
$queryWhereFilters = "";
$params = [];

if($searchFieldsForm->isSubmitted()) {
    $searchFieldsForm->setFromSubmit();

    if($searchFieldsForm->getFieldByName("form")->default) {
        $queryWhereFilters .= " AND form_id=?";
        $params[] = $searchFieldsForm->getFieldByName("form")->default;
    }

    if($searchFieldsForm->getFieldByName("start")->default) {
        $queryWhereFilters .= " AND created >= ?";
        $params[] = $searchFieldsForm->getFieldByName("start")->default;
    }

    if($searchFieldsForm->getFieldByName("end")->default) {
        $queryWhereFilters .= " AND created <= ?";
        $params[] = $searchFieldsForm->getFieldByName("end")->default;
    }
} else {
    $queryWhereFilters .= " AND form_id=?";
    $params[] = $searchFieldsObject->fields[0]->select_options[0]->value;
}

$countResults = DB::fetch("SELECT count(*) AS c FROM form_submissions WHERE 1" . $queryWhereFilters, $params)->c;

$paginationQuery = " LIMIT $paginationSize";

$offset = ($curPage-1) * $paginationSize;
$paginationQuery .= " OFFSET $offset";

$results = DB::fetchAll($query . $queryWhereFilters . $paginationQuery, $params);

$headerFields = [];
if($results[0]) {
    $currentSelectedObject = json_decode(file_get_contents($_ENV["root_path_to_forms"] . $results[0]->form_path));
    if($currentSelectedObject->list) {
        $headerFields = $currentSelectedObject->list;
    } else {
        foreach($currentSelectedObject->fields as $field) {
            if($field->save!==false) {
                $headerFields[] = $field->name;
            }
        }
    }

    $currentSelectedForm = new Form($currentSelectedObject);
}

if($_GET["exportcsv"]) {
    ob_get_clean();
    ob_get_clean();

    $filename = "form_export_" . date("Y-m-d") . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $results = DB::fetchAll("SELECT * FROM form_submissions WHERE 1" . $queryWhereFilters, $params);

    echo implode(",", $headerFields) . "\n";
    foreach($results as $row) {
        $buffer = [];
        foreach($headerFields as $header) {
            $currentSelectedForm->deserializeJson($row->data);
            $field = $currentSelectedForm->getFieldByName($header);

            $data = json_decode($row->data);
            $normalizedFields = array_combine(array_column($data, 'name'), array_column($data, 'value'));
            //$value = $normalizedFields[$header];
            $value = $field->getFriendlyValue((object)["return_in_text_form"=>true]);
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