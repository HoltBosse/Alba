<?php
defined('CMSPATH') or die; // prevent unauthorized access

if (sizeof(CMS::Instance()->uri_segments)==3) {
	$widget_type_id = CMS::Instance()->uri_segments[2];
}
else {
	$widget_type_id = false;
}

$searchFormObject = json_decode(file_get_contents(CMSPATH . "/admin/controllers/widgets/views/show/search_form.json"));
if(!is_numeric($widget_type_id)) {
	$searchFormObject->fields[] = (object) [
		"type"=>"Select",
        "label"=>"Widget Type",
        "name"=>"widget_type",
        "id"=>"widget_type",
        "placeholder"=>"widget",
        "select_options"=>DB::fetchall("SELECT title AS text, location AS value FROM widget_types"),
	];
}
$searchFormObject->fields[] = (object) [
	"type"=>"HTML",
	"html"=>"<div style='display: flex; gap: 1rem;'>
				<button class='button is-info' type='submit'>Submit</button>
				<button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
			</div>"
];
$searchForm = new Form($searchFormObject);

if($searchForm->is_submitted()) {
	$searchForm->set_from_submit();
}

$widget_type_title="";
if ($widget_type_id && is_numeric($widget_type_id)) {
	$widget_type_title = DB::fetch('SELECT title FROM widget_types WHERE id=?', [$widget_type_id])->title;
}

$query = 'SELECT w.* FROM widgets w LEFT JOIN widget_types wt ON w.type=wt.id WHERE w.state>=0';
$params = [];

if(is_numeric($widget_type_id)) {
	$query .= " AND w.type=?";
	$params[] = $widget_type_id;
}

if(isset($_POST["state"]) && is_numeric($_POST["state"])) {
	$query .= " AND w.state=?";
	$params[] = $_POST["state"];
}

if($_POST["title"]) {
	$query .= " AND w.title like ?";
	$params[] = "%{$_POST['title']}%";
}

if($_POST["widget_type"]) {
	$query .= " AND wt.location=?";
	$params[] = $_POST["widget_type"];
}

$query .= ' ORDER BY id DESC';

$all_widgets = DB::fetchall($query, $params);

$all_widget_types = Widget::get_all_widget_types();

