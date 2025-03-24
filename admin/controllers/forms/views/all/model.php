<?php
defined('CMSPATH') or die; // prevent unauthorized access

$searchFormObject = json_decode(file_get_contents(CMSPATH . "/admin/controllers/forms/views/all/search_form.json"));
$searchFormObject->fields[] = (object) [
	"type"=>"HTML",
	"html"=>"<div style='display: flex; gap: 1rem;'>
				<button class='button is-info' type='submit'>Submit</button>
				<button type='button' onclick='window.location = window.location.href.split(\"?\")[0]; return false;' class='button is-default'>Clear</button>
			</div>"
];
$searchForm = new Form($searchFormObject);

$formItems = DB::fetchall(
	"SELECT fi.id, fi.state, fi.title, fi.alias, fi.category, fi.start, fi.end, cbu.username AS created_by, ubu.username AS updated_by, fi.note
	FROM form_instances fi
	LEFT JOIN users cbu ON fi.created_by=cbu.id
	LEFT JOIN users ubu ON fi.updated_by=ubu.id"
);
