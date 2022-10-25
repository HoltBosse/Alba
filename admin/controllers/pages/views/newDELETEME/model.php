<?php
defined('CMSPATH') or die; // prevent unauthorized access

// any variables created here will be available to the view

$user = new User();
$all_groups = $user->get_all_groups();
//$all_users = $user->get_all_users();

$all_templates = Template::get_all_templates();

// todo: get default template as set by user instead of template 1
$default_template = 1;
$template = new Template(1);

$layout_path = CMSPATH . '/templates/' . $template->folder . "/layout.php";
if (!file_exists($layout_path)) {
	CMS::Instance()->queue_message('Failed to locate layout for template ' . $template->title, 'danger',Config::uripath().'/admin/pages');
}
