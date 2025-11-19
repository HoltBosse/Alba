<?php

Use HoltBosse\Alba\Core\Tag;
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

//$all_tags = Tag::get_all_tags();

$all_tags = Tag::get_all_tags_by_depth();
$search = Input::getvar('search',v::StringVal(),null);

