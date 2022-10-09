<?php
defined('CMSPATH') or die; // prevent unauthorized access

$segments = CMS::Instance()->uri_segments;

$version_count = 0;

function preview_field($field, $cur_field) {
	?>
	<tr>
		<td><?php echo $field->name; ?></td>
		<td><?php version_field_preview ( $field, 'value' );?></td>
		<td><?php version_field_preview ( $cur_field, 'content' );?></td>
	</tr>
	<?php
}

function name_exists_in_serialied_obj ($fields, $name) {
	foreach ($fields as $field) {
		if ($field->name==$name) {
			return $field;
		}
	}
	return false;
}

function get_field_by_name ($fields, $name) {
	foreach ($fields as $field) {
		if ($field->name==$name) {
			return $field;
		}
	}
	return false;
}

function version_field_preview ($field, $prop) {
	echo "<pre><xmp>";
	print_r ($field->$prop);
	echo "</xmp></pre>"; 
}

if (sizeof($segments)==3 && is_numeric($segments[2])) {
	$content_id = $segments[2];
	
	$content = new content();
	$content->load($content_id);
	$new_content = false;

	$cur_content_fields = DB::fetchAll('select * from content_fields where content_id=?',array($content_id));
	$version_count = DB::fetch('select count(id) as c from content_versions where content_id=?',array($content_id))->c;
	$versions = DB::fetchAll('select v.*, u.username from content_versions v, users u where content_id=? and u.id=v.created_by order by v.created desc',array($content_id));
}
elseif (sizeof($segments)==4 && $segments[2]=='restore' && is_numeric($segments[3])) {
	$version_id = $segments[3];
	$content_id = DB::fetch('select content_id from content_versions where id=?',[$version_id])->content_id ?? false;
	$content_type = DB::fetch('select content_type from content where id=?', $content_id)->content_type ?? false;
	if ($content_id && $content_type) {
		$content_location = DB::fetch('select ct.controller_location from content_types ct, content c where c.id=? and c.content_type = ct.id', $content_id)->controller_location ?? "";
		if ($content_location) {
			$content_form = new Form (CMSPATH . '/controllers/' . $content_location . "/custom_fields.json");
			$version_json = DB::fetch('select fields_json from content_versions where id=?', $version_id)->fields_json;
			$version_fields_obj = json_decode($version_json);
			$content_form->deserialize_json($version_json);

			// replace with old version data
			// loop over every current custom form field
			foreach ($content_form->fields as $field) {
				// insert field info
				if (isset($field->save)) {
					if ($field->save===false) {
						continue;
					}
				}
				if ($field->filter=="ARRAYOFINT") {
					if (is_array($field->default)) {
						$field->default = implode(",",$field->default);
					}
				}
				// check version has field data before deleting/inserting 
				// this will preserve any new data added to content since
				// new fields have been added
				if (name_exists_in_serialied_obj($version_fields_obj, $field->name)) {
					// field exists in version - delete this field for current live content
					DB::exec("delete from content_fields where content_id=? and name=?", [$content_id, $field->name]);
					// insert version information from form created from saved version data
					$result = DB::exec("insert into content_fields (content_id, name, field_type, content) values (?,?,?,?)", [$content_id, $field->name, $field->type, $field->default]);
					if (!$result) {
						CMS::Instance()->log("Error saving version field: " . $field->name);
						CMS::Instance()->queue_message('Error restoring data','warning',Config::$uripath.'/admin/content/all/' . $content_type);
					}
				}
			}
			CMS::Instance()->queue_message('Version restored','success', Config::$uripath.'/admin/content/all/' . $content_type);
		}
	}
	CMS::Instance()->queue_message('Error restoring data','warning',Config::$uripath.'/admin/content/all/' . $content_type);
}
else {
	CMS::Instance()->queue_message('Unknown content version operation','danger',Config::$uripath.'/admin/content/all');
	exit(0);
}

// update CMS instance with this content information
// this allows custom form fields etc to easily access information such as
// content id/type
CMS::Instance()->editing_content = $content;


// prep forms
$required_details_form = new Form(ADMINPATH . '/controllers/content/views/edit/required_fields_form.json');
$content_form = new Form (CMSPATH . '/controllers/' . $content->content_location . "/custom_fields.json");
// set content_type for tag field based on content type of new/editing content
$tags_field = $required_details_form->get_field_by_name('tags');
$tags_field->content_type = $content->content_type;


