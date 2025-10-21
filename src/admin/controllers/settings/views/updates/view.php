<?php

Use HoltBosse\Alba\Core\{CMS, Content, Component};
Use HoltBosse\DB\DB;

function show_message ($heading, $text, $class) {
	echo "<article class=\"message $class\">
	<div class=\"message-header\">
		<p>$heading</p>
		<button class=\"delete\" aria-label=\"delete\"></button>
	</div>
	<div class=\"message-body\">
		$text
	</div>
</article>";
}

Component::addon_page_title("System Version and Updates");
?>

<hr>
<h5 class='is-5 title is-title'>Legacy DB Checks/Fixes</h5>

<?php 
if ($page_options_ok) {
	show_message ('Pages Table','Pages table OK.','is-success');
}
else {
	show_message ('Pages Table','Added missing columns','is-warning');
}

if ($plugins_table_ok) {
	show_message ('Plugins Table','Plugins table OK.','is-success');
}
else {
	show_message ('Plugins Table','Plugins table created.','is-warning');
}

if ($tags_table_ok) {
	show_message ('Tags Table Parent Column','Tags table OK.','is-success');
}
else {
	show_message ('Tags Table Parent Column','Tags table updated.','is-warning');
}

if ($tag_category_ok) {
	show_message ('Tags Table Category Column','Tags table OK.','is-success');
}
else {
	show_message ('Tags Table Category Column','Tags table updated.','is-warning');
}

if ($custom_fields_category_ok) {
	show_message ('Category Custom Fields Column','Category table OK.','is-success');
}
else {
	show_message ('Category Custom Fields Column','Category table updated.','is-warning');
}

if ($redirects_table_ok) {
	show_message ('Redirects Table','Redirects table OK.','is-success');
}
else {
	show_message ('Redirects Table','Redirects table created and/or updated.','is-warning');
}

if ($user_actions_table_ok) {
	show_message ('User Actions Table','User Actions table OK.','is-success');
}
else {
	show_message ('User Actions Table','User Actions table created.','is-warning');
}

if ($user_actions_details_table_ok) {
	show_message ('User Actions Details Table','User Actions table OK.','is-success');
}
else {
	show_message ('User Actions Details Table','User Actions table created.','is-warning');
}

if ($form_submissions_table_ok) {
	show_message ('Form Submissions Table','Form Submissions table OK.','is-success');
}
else {
	show_message ('Form Submissions Table','Form Submissions table created.','is-warning');
}

if ($messages_table_ok) {
	show_message ('Messages Table','Messages table OK.','is-success');
}
else {
	show_message ('Messages Table','Messages table created.','is-warning');
}

if ($form_instances_table_ok) {
	show_message ('Form Instances Table','Form Instances table OK.','is-success');
}
else {
	show_message ('Form Instances Table','Form Instances table created.','is-warning');
}

if($content_table_ordering_ok) {
	show_message ('Content Table(s)','Content Ordering OK.','is-success');
}
else {
	show_message ('Messages Table','Updated ordering of content tables','is-warning');
}

if(sizeof($page_domains_ok)>0 || sizeof($redirect_domains_ok)>0) {
	show_message ('Pages and/or Redirects Table','Updated domains to index based','is-warning');
} else {
	show_message ('Pages and/or Redirects Table','domain column ok','is-success');
}