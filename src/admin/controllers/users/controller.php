<?php

Use HoltBosse\Alba\Core\{CMS, Controller, JSON};
Use HoltBosse\DB\DB;

// flat custom user fields
$old_custom_user_fields_table_exists = DB::fetch("
	SELECT table_name  
	FROM information_schema.tables
	WHERE table_name = 'user_fields';
");
$flat_custom_user_fields_table_exists = DB::fetch("
	SELECT table_name  
	FROM information_schema.tables
	WHERE table_name = 'custom_user_fields';
");
if (!$flat_custom_user_fields_table_exists) {
	// create base table
	$create_table_query = "
	CREATE TABLE `custom_user_fields` (
		`user_id` int(11) NOT NULL,
		PRIMARY KEY (user_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	$table_created_ok = DB::exec($create_table_query);
	if (!$table_created_ok) {
		CMS::show_error('Failed to create new custom_user_fields table');
	}
}
// check for missing custom_user_fields columns based on loaded JSON
$custom_fields = isset($_ENV["custom_user_fields_file_path"]) ? JSON::load_obj_from_file($_ENV["custom_user_fields_file_path"]) : null;
if ($custom_fields) {
	$cols_added = [];
	$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'custom_user_fields'";
	$cols = DB::fetchAll($query, [], ["mode"=>PDO::FETCH_COLUMN]);
	foreach ($custom_fields->fields as $f) {
		if (!in_array($f->name, $cols)) {
			// check if column is saveable
			if (property_exists($f, 'save')) {
				if ($f->save===false) {
					// skip, not saveable
					continue;
				}
			}
			// new column needed, try and get type from JSON obj
			$coltype = $f->coltype ?? null ? $f->coltype : " mediumtext " ;
			DB::exec('ALTER TABLE `custom_user_fields` ADD COLUMN `' . $f->name . '` ' . $coltype );
			// add to report array
			$cols_added[] = [$f->name];
		}
	}
}
if ($cols_added) { ?>
	<article class="message is-success">
	<div class="message-header">
		<p>New User Custom Fields Columns Created</p>
		<button class="delete" aria-label="delete"></button>
	</div>
	<div class="message-body">
		<h5 class='title is-5'>Created <?php echo sizeof($cols_added);?> new table columns:</h5>
		<ul class='list'>
			<?php foreach ($cols_added as $c) {
				echo "<li class='list-item'>Added column <strong>{$c[1]}</strong> into table &ldquo;{$c[0]}&rdquo;</li>";
			}?>
		</ul>
	</div>
	</article>
<?php 
}

// quick user migration check - row count
$user_c = DB::fetch('SELECT COUNT(*) AS c FROM users')->c ?? 0;
$new_custom_fields_c = DB::fetch('SELECT COUNT(*) AS c FROM custom_user_fields')->c ?? 0;

if ($old_custom_user_fields_table_exists && $custom_fields && ($user_c != $new_custom_fields_c)) {
	// have old custom_fields table and we have user_fields in a json file
	// also mismatch in number of rows vs user table
	$all_user_ids = DB::fetchAll('select id from users', [], ["mode"=>PDO::FETCH_COLUMN]);
	$count = 0;
	foreach ($all_user_ids as $user_id) {
		// check if user data migrated
		$already_done = DB::fetch('SELECT user_id FROM `custom_user_fields` WHERE `user_id`=?',[$user_id])->user_id ?? null;
		if ($already_done) {
			continue; // skip user
		}
		// get all old custom user data
		$old_user_fields_for_user = DB::fetchAll('SELECT * FROM `user_fields` WHERE `user_id`=?',[$user_id]);
		$done_at_least_one = false;
		foreach ($custom_fields->fields as $f) {
			if (!in_array($f->name, $cols)) {
				// check if column is saveable
				if (property_exists($f, 'save')) {
					if ($f->save===false) {
						// skip, not saveable
						continue;
					}
				}
			}
			if ($old_user_fields_for_user) {
				foreach ($old_user_fields_for_user as $old_field) {
					// check if f->name is a col returned from user_fields (old) check
					if ($old_field->name === $f->name) {
						$old_value = $old_field->content ?? null;
						$insert_query = 'INSERT INTO `custom_user_fields` (user_id, ' . $f->name . ') VALUES (?,?) ON DUPLICATE KEY UPDATE ' . $f->name . '=?';
						DB::exec($insert_query, [$user_id, $old_value, $old_value]);
						$done_at_least_one = true;
						$count++;
					}
				}
			}
		}
		if (!$done_at_least_one) {
			// add entry into table with user id only - already tested doesn't exist above
			DB::exec('INSERT INTO `custom_user_fields` (user_id) VALUES(?)', [$user_id]);
		}
	}
	if ($count>0) { ?>
		<article class="message is-success">
		<div class="message-header">
			<p>Custom User Fields Data Migrated</p>
			<button class="delete" aria-label="delete"></button>
		</div>
		<div class="message-body">
			<h5 class='title is-5'>Migrated <?php echo $count;?> user fields.</h5>
		</div>
		</article>
	<?php 
	}
}
	



// router
$view = 'default';

// load model + view
$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)>1) {
	if (!is_numeric($segments[1])) {
		$view = $segments[1];
	}
}

if (is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$user_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$user_controller->load_view($view);
} else {
	CMS::raise_404();
}


