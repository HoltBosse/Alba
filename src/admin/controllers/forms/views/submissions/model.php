<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, DataExport, Hook, HookQueryResult};
Use HoltBosse\Form\{Input, Form};
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

$segments = CMS::Instance()->uri_segments;
if(sizeof($segments) > 2) {
    CMS::raise_404();
}

$paginationSize = Configuration::get_configuration_value ('general_options', 'pagination_size');

$curPage = Input::getvar('page',v::IntVal(),'1');

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

    if(isset($json->domains) && is_array($json->domains) && !in_array($_SESSION["current_domain"], $json->domains)) {
        return null;
    }
    return $option;
    
}, $formSelectOptions);

$formSelectOptions = array_values(array_filter($formSelectOptions, function($input) {
    return $input !== null;
}));

$searchFieldsObject = json_decode(file_get_contents(__DIR__ . "/search_form.json"));
$searchFieldsObject->fields[0]->select_options = $formSelectOptions;
// @phpstan-ignore-next-line
if(Input::getVar("form", v::StringVal(), null)==null && $searchFieldsObject->fields[0]->select_options[0]) {
    $searchFieldsObject->fields[0]->default = $searchFieldsObject->fields[0]->select_options[0]->value;
}
$searchFieldsObject->fields[] = (object) [
    "type"=>"Html",
    "html"=>"<div style='display: flex; gap: 1rem;'>
                <button class='button is-info' type='submit'>Search</button>
                <button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
            </div>"
];

$searchFieldsObject = Hook::execute_hook_filters('admin_search_form_object', $searchFieldsObject);

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

//check that $searchFieldsForm->getFieldByName("form")->default exists in formSelectOptions
$formExists = false;
foreach($formSelectOptions as $option) {
    if($option->value == $searchFieldsForm->getFieldByName("form")->default) {
        $formExists = true;
        break;
    }
}

//dont show anything
if(!$formExists) {
    $countResults = 0;
}

if(Input::getVar("exportcsv", v::NumericVal(), 0) != 1) {
    $paginationQuery = " LIMIT $paginationSize";
    
    $offset = ($curPage-1) * $paginationSize;
    $paginationQuery .= " OFFSET $offset";
} else {
    $paginationQuery = "";
}

$results = DB::fetchAll($query . $queryWhereFilters . $paginationQuery, $params);

$headerFields = [];
if($results[0]) {
    $currentSelectedObject = json_decode(file_get_contents($_ENV["root_path_to_forms"] . $results[0]->form_path));
    if(isset($currentSelectedObject->list)) {
        $headerFields = $currentSelectedObject->list;
    } else {
        foreach($currentSelectedObject->fields as $field) {
            if((isset($field->save) ? $field->save : true)!==false) {
                $headerFields[] = $field->name;
            }
        }
    }

    $currentSelectedForm = new Form($currentSelectedObject);
}

if(Input::getVar("exportcsv", v::NumericVal(), 0) == 1) {
    $exporter = new DataExport();
    $exporter->format = "csv";
    $exporter->filename = "form_export_" . date("Y-m-d");
    $exporter->data = [];

    foreach($results as $row) {
        $buffer = ["Submission Date"=>Date("m/d/Y g:i a", strtotime($row->created))];
        foreach($headerFields as $header) {
            $currentSelectedForm->deserializeJson($row->data);
            $field = $currentSelectedForm->getFieldByName($header);

            $data = json_decode($row->data);
            $normalizedFields = array_combine(array_column($data, 'name'), array_column($data, 'value'));
            //$value = $normalizedFields[$header];
            $value = (string) $field->getFriendlyValue((object)["return_in_text_form"=>true]);
            $buffer[$header] = $value;
        }
        $exporter->data[] = $buffer;
    }

    $exporter->exec();

    die;
}