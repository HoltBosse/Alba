<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
$content_type_filter=null;

if (sizeof($segments)==1) {
	$view = 'all';
}
else {
	$view = $segments[1];
}

// check for new content types
$all_content_types = DB::fetchAll("select * from content_types");
$missing_content_types=[];
$missing=[];
//CMS::pprint_r ($all_content_types);
foreach (new DirectoryIterator(CMSPATH . '/controllers/') as $f) {
	if($f->isDot()) continue; // no dot files, shouldn't even happen with PHP defaults
	if($f->isFile()) continue; // skip files only dirs
	foreach ($all_content_types as $existing_content_type) {
		if ($f->getFilename()==$existing_content_type->controller_location) {
			continue 2; // already exists, skip outer loop
		}
	}
	// reached here, widget type is missing from db
	$missing[] = $f->getFilename();
}
// install / display info for missing widget types
if ($missing) { ?>
	<article class="message is-success">
	<div class="message-header">
		<p>New Content Type(s) Detected</p>
		<button class="delete" aria-label="delete"></button>
	</div>
	<div class="message-body">
		<h5 class='title is-5'>Detected <?php echo sizeof($missing);?> new content types - attempting installation:</h5>
		<ul class='list'>
			<?php //CMS::pprint_r ($missing);?>
			<?php foreach ($missing as $missed) {
				$controller_config_file = CMSPATH . '/controllers/' . $missed . '/controller_config.json';
				$custom_fields_file = CMSPATH . '/controllers/' . $missed . '/custom_fields.json';
				if (is_readable($controller_config_file)) {
					// $w_config = json_decode(file_get_contents($controller_config_file));
					$w_config = JSON::load_obj_from_file($controller_config_file);
					$custom_fields = JSON::load_obj_from_file($custom_fields_file);
					if (!$w_config || !$w_config->title || !$custom_fields->id) {
						echo "<li class='list-item has-text-danger'>Skipped <strong>{$missed}</strong> - missing or badly formed <em>controller_config.json</em></li>";
					}
					else {
						if (!$w_config->description) {
							$w_config->description = "No description found in json file";
						}
						$ok = DB::exec("INSERT into content_types (title, controller_location, description, state) values (?,?,?,1)", [$w_config->title, $missed, $w_config->description]);
						if ($ok) {
							echo "<li class='list-item'><strong>{$w_config->title}</strong> - {$w_config->description} - <em>Installed</em>";
							// create controller/content table
							$create_table_query = "
							CREATE TABLE `controller_{$custom_fields->id}` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`state` tinyint(2) NOT NULL DEFAULT '1',
								`ordering` int(11) NOT NULL DEFAULT '1',
								`title` varchar(255) NOT NULL,
								`alias` varchar(255) NOT NULL,
								`content_type` int(11) NOT NULL COMMENT 'content_types table',
								`start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
								`end` timestamp NULL DEFAULT NULL,
								`created_by` int(11) NOT NULL,
								`updated_by` int(11) NOT NULL,
								`note` varchar(255) DEFAULT NULL,
								`created` timestamp NOT NULL DEFAULT current_timestamp(),
								`category` int(11) NOT NULL DEFAULT 0,
								`updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
								PRIMARY KEY (id)
							  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
							DB::exec($create_table_query);
							// install views as well
							$content_type_id = DB::getLastInsertedId();
							$view_folders=[];
							foreach (new DirectoryIterator(CMSPATH . '/controllers/' . $missed . "/views/") as $f) {
								if($f->isDot()) continue; // no dot files, shouldn't even happen with PHP defaults
								if($f->isFile()) continue; // skip files only dirs
								// reached here, widget type is missing from db
								$view_folders[] = $f->getFilename();
							}
							foreach ($view_folders as $view_folder) {
								$view_config_file = CMSPATH . '/controllers/' . $missed . '/views/' . $view_folder . '/view_configuration.json';
								if (is_readable($view_config_file)) {
									//$view_config = json_decode(file_get_contents($view_config_file));
									$view_config = JSON::load_obj_from_file($view_config_file);
									if (!$view_config || !$view_config->title) {
										echo "<br>Malformed JSON in view_config file for view: " . $view_folder;
									}
									else {
										if (!property_exists($view_config,"description")) {
											$view->config->description = null;
										}
										// insert view content_type_id	title	location
										$view_ok = DB::exec("INSERT into content_views (content_type_id,title,location,description) values (?,?,?,?)", [$content_type_id, $view_config->title, $view_folder,$view_config->description]);
										if ($view_ok) {
											echo "<br>View " . $view_folder . " <em>Installed</em>.";
										}
										else {
											echo "<br>Failed to install view: " . $view_folder;
										}
									}
								}
								else {
									echo "<br>Unable to read view_config for view: " . $view_folder;
								}
							}
							echo "</li>"; // end install list notice
						}
						else {
							echo "<li class='list-item has-text-danger'><strong>{$w_config->title}</strong> - {$w_config->description} <br>Not installed</li>";
						}
						
					}
				}
				else {
					echo "<li class='list-item has-text-danger'>Problem loading <em>controller_config.json</em> in &ldquo;" . $missed . "&rdquo; controller folder</li>";
				}
			}?>
		</ul>
	</div>
	</article>
<?php }

// check for new content columns
$cols_added=[];
$all_content_types = DB::fetchAll('select * from content_types');
foreach ($all_content_types as $content_type) {
	$location = Content::get_content_location($content_type->id);
	$custom_fields = JSON::load_obj_from_file(CMSPATH . '/controllers/' . $location . '/custom_fields.json');
	$table_name = "controller_" . $custom_fields->id ;
	if ($table_name=="controller_") {
		CMS::show_error('Unable to determine table name for content id ' . $content_type->id);
	}
	else {
		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table_name}'";
		$cols = DB::fetchAllColumn($query);
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
            	DB::exec('ALTER TABLE ' . $table_name . ' ADD COLUMN ' . $f->name . ' ' . $coltype);
				// add to report array
				$cols_added[] = [$table_name, $f->name];
			}
		}
	}
}
if ($cols_added) { ?>
	<article class="message is-success">
	<div class="message-header">
		<p>New Content Columns Created</p>
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


// load model + view
if (is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$content_type_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$content_type_controller->load_view($view);
} else {
	CMS::raise_404();
}


