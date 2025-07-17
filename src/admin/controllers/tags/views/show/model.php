<?php

Use HoltBosse\Alba\Core\Tag;
Use HoltBosse\Form\Input;

//$all_tags = Tag::get_all_tags();

$all_tags = Tag::get_all_tags_by_depth();
$search = Input::getvar('search','STRING',null);

