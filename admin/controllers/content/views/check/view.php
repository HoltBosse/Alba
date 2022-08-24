<?php
defined('CMSPATH') or die; // prevent unauthorized access

// TODO: decide if check content function should be in Content class
// or if it's fine to be here - theoretically this is the only place
// check should take place?

function fix_content ($content_type) {
	// get custom fields json for content type
	$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $content_type->controller_location . '/custom_fields.json');
	//CMS::pprint_r ($custom_fields);
	// loop through fields and check name / content_id pair exists for every content item
	// of type in database
	// also check for lack of default value for missing items
	$all_content_of_type = DB::fetchall("select * from content where content_type=?", [$content_type->id]);
	if ($all_content_of_type) {
		$response = true;
		foreach ($custom_fields->fields as $field) {
			// check if field save attribute is false - if so, no need for checking :)
			if (property_exists($field,'save')) {
				if ($field->save===false) {
					echo "<p class='help success'>Field &ldquo;" . $field->name . "&rdquo; is not saveable - skipping</p>";
					continue;
				}
			}

			// field count for content
			$field_count = DB::fetch("select count(*) as c from content_fields where name=? and content_id in (select id from content where content_type=?)", [$field->name, $content_type->id])->c;

			$missing_count = sizeof($all_content_of_type) - $field_count;

			if (!$missing_count) {
				echo "<p class='help success'>Field &ldquo;" . $field->name . "&rdquo; ok</p>";
			}
			else {
				$response = false;
				echo "<p class='help warning'>Field &ldquo;" . $field->name . "&rdquo; missing from {$missing_count} content items</p>";
				// insert defaults if available, warn if default not present
				if (property_exists($field, 'default')) {
					// found a default value, loop through all content of this type and
					// insert default value field where it doesn't exist
					foreach ($all_content_of_type as $content) {
						// check if missing field
						$present_c = DB::fetch('select count(content_id) as c from content_fields where name=? and content_id=?', [$field->name, $content->id])->c;
						if (!$present_c) {
							$ok = DB::exec("insert into content_fields (content_id, name, field_type, content) values (?,?,?,?)", [$content->id, $field->name, $field->type, $field->default]);
							if (!$ok) {
								echo "<p class='help error'>Error inserting default &ldquo;" . $field->name . "&rdquo; for content &ldquo;{$content->title}&rdquo;</p>";								
							}
							else {
								echo "<p class='help default'>Inserted default &ldquo;" . $field->name . "&rdquo; for content &ldquo;{$content->title}&rdquo;</p>";								
							}
						}
						else {
							// was present, do nothing
							//echo "<p class='help default'>Field ok: &ldquo;" . $field->name . "&rdquo; for content &ldquo;{$content->title}&rdquo;</p>";								
						}
					}

					//echo "<p class='help default'>Field &ldquo;" . $field->name . "&rdquo; default found and inserted</p>";
				}
				else {
					echo "<p class='help error'>Field &ldquo;" . $field->name . "&rdquo; has no default in custom_fields.json file - please fix</p>";
				}
			}
		}
		return $response;
	}
	else {
		echo "<p class='help'>No content of type found :)</p>";
		return true;
	}
}

?>

<style>
	p.help {
		margin-left:2ch;
	}
	p.help.success { color:green;}
	p.help.default { color:blue;}
	p.help.warning { color:orange;}
	p.help.error { color:red;}
	h5 {
		margin-top:1em;
	}
	p {
		margin-top:0.5em;
	}
</style>

<form action='' method='post' name='check_fields' id='check_fields_form'>

	<h1 class='title is-1'>Check Content Fields</h1>

	<?php foreach ($all_content_types as $content_type):?>
		<?php //CMS::pprint_r ($content_type); ?>
		<h5 class='title is-5'>Checking &ldquo;<?php echo $content_type->title;?>&rdquo;</h5>
		<?php $content_ok = fix_content ($content_type);?>
		<?php if ($content_ok):?>
			<p>Content fields present and accounted for.</p>
		<?php else:?>
			<p>One or more custom fields were missing from database - populated with defaults where possible.</p>
		<?php endif; ?>
		<hr>
	<?php endforeach; ?>

</form>

<script>
	
</script>