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
$query = "select * from content_types";
$stmt = CMS::Instance()->pdo->prepare($query);
$stmt->execute(array());
$all_content_types = $stmt->fetchAll();
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
				if (is_readable($controller_config_file)) {
					//$w_config = json_decode(file_get_contents($controller_config_file));
					$w_config = JSON::load_obj_from_file($controller_config_file);
					if (!$w_config || !$w_config->title) {
						echo "<li class='list-item has-text-danger'>Skipped <strong>{$missed}</strong> - missing or badly formed <em>controller_config.json</em></li>";
					}
					else {
						if (!$w_config->description) {
							$w_config->description = "No description found in json file";
						}
						$query = "insert into content_types (title, controller_location, description, state) values (?,?,?,1)";
						$stmt = CMS::Instance()->pdo->prepare($query);
						$ok = $stmt->execute(array($w_config->title, $missed, $w_config->description));
						if ($ok) {
							echo "<li class='list-item'><strong>{$w_config->title}</strong> - {$w_config->description} - <em>Installed</em>";
							// install views as well
							$content_type_id = CMS::Instance()->pdo->lastInsertId();
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
										$query = "insert into content_views (content_type_id,title,location,description) values (?,?,?,?)";
										$stmt = CMS::Instance()->pdo->prepare($query);
										$view_ok = $stmt->execute(array($content_type_id, $view_config->title, $view_folder,$view_config->description));
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

// load model + view

$content_type_controller = new Controller(realpath(dirname(__FILE__)),$view);
$content_type_controller->load_view($view);


