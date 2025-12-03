<?php

Use HoltBosse\Alba\Core\{CMS, Tag};
Use HoltBosse\Form\Input;
Use Respect\Validation\Validator as v;

//$all_tags = Tag::get_all_tags();

$all_tags = Tag::get_all_tags_by_depth();
$search = Input::getvar('search',v::StringVal(),null);

//CMS::pprint_r($all_tags);

$all_tags = array_values(array_filter($all_tags, function($tag) {
    return ($tag->domain === null || $tag->domain === $_SESSION["current_domain"]);
})); //filter tags by current domain or null for all domains