<?php
defined('CMSPATH') or die; // prevent unauthorized access

// router

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==0) {
	$view = 'default';
	$plugin_id = false;
}
else {
	if ($segments[1]=='show') {
		$view = 'show';
	}
	elseif ($segments[1]=='edit') {
		$view = 'edit';
	}
	elseif ($segments[1]=='action') {
		$view = 'action';
	}
}

// check for new plugins
$query = "select * from plugins";
$stmt = CMS::Instance()->pdo->prepare($query);
$stmt->execute(array());
$all_plugins = $stmt->fetchAll();
$missing_plugins=[];
foreach (new DirectoryIterator(CMSPATH . '/plugins/') as $f) {
	if($f->isDot()) continue; // no dot files, shouldn't even happen with PHP defaults
	if($f->isFile()) continue; // skip files only dirs
	foreach ($all_plugins as $existing_plugin) {
		if ($f->getFilename()==$existing_plugin->location) {
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
		<p>New Plugin Detected</p>
		<button class="delete" aria-label="delete"></button>
	</div>
	<div class="message-body">
		<h5 class='title is-5'>Detected <?php echo sizeof($missing);?> new plugin - attempting installation:</h5>
		<ul class='list'>
			<?php foreach ($missing as $missed) {
				$plugin_config_file = CMSPATH . '/plugins/' . $missed . '/plugin_config.json';
				if (is_readable($plugin_config_file)) {
					$w_config = json_decode(file_get_contents($plugin_config_file));
					if (!$w_config || !$w_config->title) {
						echo "<li class='list-item has-text-danger'>Skipped <strong>{$missed}</strong> - missing or badly formed <em>plugin_config.json</em></li>";
					}
					else {
						if (!$w_config->description) {
							$w_config->description = "No description found in json file";
						}
						$query = "insert into plugins (title, location, description) values (?,?,?)";
						$stmt = CMS::Instance()->pdo->prepare($query);
						$ok = $stmt->execute(array($w_config->title, $missed, $w_config->description));
						if ($ok) {
							echo "<li class='list-item'><strong>{$w_config->title}</strong> - {$w_config->description} - <em>Installed</em></li>";
						}
						else {
							echo "<li class='list-item has-text-danger'><strong>{$w_config->title}</strong> - {$w_config->description}</li>";
						}
					}
				}
				else {
					echo "<li class='list-item has-text-danger'>Problem loading <em>plugin_config.json</em> in &ldquo;" . $missed . "&rdquo; plugin folder</li>";
				}
			}?>
		</ul>
	</div>
	</article>
<?php }

// load model + view

//CMS::queue_message('Test','success');

$plugin_controller = new Controller(realpath(dirname(__FILE__)),$view);
$plugin_controller->load_view($view);

