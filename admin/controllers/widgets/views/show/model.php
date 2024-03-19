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
	$widget_type_title = DB::fetch('SELECT title FROM widget_types WHERE id=?', [$widget_type_id])->title;
}

if (is_numeric($widget_type_id)) {
	$all_widgets = DB::fetchall('SELECT * FROM widgets WHERE state>=0 AND type=? ORDER BY id DESC', [$widget_type_id]);
}
else {
	$all_widgets = DB::fetchall('SELECT * FROM widgets WHERE state>=0 ORDER BY id DESC');
}

$all_widget_types = Widget::get_all_widget_types();

