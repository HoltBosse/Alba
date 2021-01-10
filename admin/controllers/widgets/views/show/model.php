<?php
defined('CMSPATH') or die; // prevent unauthorized access
if (sizeof(CMS::Instance()->uri_segments)==3) {
	$widget_type_id = CMS::Instance()->uri_segments[2];
}
else {
	$widget_type_id = false;
}

$widget_type_title="";
if ($widget_type_id && is_numeric($widget_type_id)) {
	$widget_type_title = CMS::Instance()->pdo->query('select title from widget_types where id=' . $widget_type_id)->fetch()->title;
}

if (is_numeric($widget_type_id)) {
	//$all_widgets = CMS::Instance()->pdo->query('select * from widgets where state>=0 and type=' . $widget_type_id)->fetchAll();
	$all_widgets = DB::fetchall('select * from widgets where state>=0 and type=?', array($widget_type_id));
}
else {
	//$all_widgets = CMS::Instance()->pdo->query('select * from widgets where state>=0')->fetchAll();
	$all_widgets = DB::fetchall('select * from widgets where state>=0');
}

$all_widget_types = Widget::get_all_widget_types();

