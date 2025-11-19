<?php

Use HoltBosse\Alba\Core\{CMS, Configuration};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use Respect\Validation\Validator as v;

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search',v::StringVal(),null);
$filters = Input::tuplesToAssoc( Input::getvar('filters','RAW',null) );
$cur_page = Input::getvar('page',v::IntVal(),'1');
$page_size = Configuration::get_configuration_value ('general_options', 'pagination_size'); 
$order_by = Input::getvar('order_by',v::StringVal(),'');
$order_by_snippet = " ORDER BY created DESC ";
if ($order_by && $order_by=="hits") {
    $order_by_snippet = " ORDER BY hits DESC ";
}

$state = $filters['state'] ?? null;


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
$params[] = $page_size;
$params[] = ($cur_page-1)*$page_size; // offset
$query.= "{$order_by_snippet} LIMIT ? OFFSET ?";
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
$redirect_count = DB::fetch($query, $params)->c ?? 0;

