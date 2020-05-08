<?php
defined('CMSPATH') or die; // prevent unauthorized access

// dummy fields, TODO: have these in DB fields table

$text = new stdClass();
$text->id=1; $text->title="Text"; $text->classname="Field_Text";
$rich = new stdClass();
$rich->id=2; $rich->title="Rich/HTML"; $rich->classname="Field_Rich";



$all_fields = array(
	$text, $rich
);