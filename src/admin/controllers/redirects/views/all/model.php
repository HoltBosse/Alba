<?php

Use HoltBosse\Alba\Core\{CMS, Configuration, Hook, Form};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view

$searchFormObject = json_decode(file_get_contents(__DIR__ . "/search_form.json"));
$searchFormObject->fields[] = (object) [
	"type"=>"Html",
	"html"=>"<div style='display: flex; gap: 1rem;'>
				<button class='button is-info' type='submit'>Submit</button>
				<button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
			</div>"
];

$searchFormObject = Hook::execute_hook_filters('admin_search_form_object', $searchFormObject);

$searchForm = new Form($searchFormObject);

if($searchForm->isSubmitted()) {
	$searchForm->setFromSubmit();
}

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search',v::StringVal(),null);
$filters = Input::tuplesToAssoc( Input::getvar('filters',v::AlwaysValid(),null) );
$cur_page = Input::getvar('page',v::IntVal(),'1');
$page_size = Configuration::get_configuration_value ('general_options', 'pagination_size'); 
$order_by = Input::getvar('Hits_order',v::StringVal(),'');
$order_by_snippet = " ORDER BY created DESC ";
$state = Input::getVar('state',v::IntVal(),null);
if ($order_by && $order_by=="desc") {
    $order_by_snippet = " ORDER BY hits DESC ";
}


// get paginated / searched redirects
$params=[];
$query = "SELECT * FROM redirects ";
if ($search) {
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%'; // 3x for both urls + note
    $query.= " WHERE (old_url LIKE ? OR new_url LIKE ? OR note LIKE ?) ";
}
if (!is_null($state)) {
    $and_or_where = $search ? " and " : " where ";
    $query.= " {$and_or_where} state=? ";
    $params[] = $state;
} else {
    $and_or_where = $search ? " and " : " where ";
    $query.= " {$and_or_where} state>=0 ";
}

$query .= " AND domain=?";
$params[] = $_SESSION["current_domain"];

$query.= "{$order_by_snippet} LIMIT ? OFFSET ?";
$params[] = $page_size;
$params[] = ($cur_page-1)*$page_size; // offset
$redirects = DB::fetchAll($query, $params);

// get total count
$params=[];
$query = "SELECT COUNT(*) AS c FROM redirects ";
if ($search) {
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%'; // 3x for both urls + note
    $query.= " WHERE (old_url LIKE ? OR new_url LIKE ? OR note LIKE ?) ";
}
if (!is_null($state)) {
    $query.= " {$and_or_where} state=? ";
    $params[] = $state;
} else {
    $query.= " {$and_or_where} state>=0 ";
}
$query .= " AND domain=?";
$params[] = $_SESSION["current_domain"];
$redirect_count = DB::fetch($query, $params)->c ?? 0;

