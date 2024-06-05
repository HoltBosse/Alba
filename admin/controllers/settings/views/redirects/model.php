<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$segments = CMS::Instance()->uri_segments;
$search = Input::getvar('search','TEXT',null);
$filters = Input::tuples_to_assoc( Input::getvar('filters','RAW',null) );
$cur_page = Input::getvar('page','INT','1');
$page_size = Configuration::get_configuration_value ('general_options', 'pagination_size'); 

$state = $filters['state'] ?? null;


// get paginated / searched redirects
$params=[];
$query = "select * from redirects ";
if ($search) {
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%'; // 3x for both urls + note
    $query.= " where (old_url like ? or new_url like ? or note like ?) ";
}
if (!is_null($state)) {
    $and_or_where = $search ? " and " : " where ";
    $query.= " {$and_or_where} state=? ";
    $params[] = $state;
}
$params[] = $page_size;
$params[] = ($cur_page-1)*$page_size; // offset
$query.= "order by created DESC LIMIT ? OFFSET ?";
$redirects = DB::fetchAll($query, $params);

// get total count
$params=[];
$query = "select count(*) as c from redirects ";
if ($search) {
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%'; // 3x for both urls + note
    $query.= " where (old_url like ? or new_url like ? or note like ?) ";
}
if (!is_null($state)) {
    $query.= " {$and_or_where} state=? ";
    $params[] = $state;
}
$content_count = DB::fetch($query, $params)->c ?? 0;

