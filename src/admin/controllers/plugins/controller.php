<?php

Use HoltBosse\Alba\Core\{CMS, Controller, Plugin, File};
Use HoltBosse\DB\DB;

// router

$segments = CMS::Instance()->uri_segments;
if ($segments[1]=='show') {
	$view = 'show';
}
elseif ($segments[1]=='edit') {
	$view = 'edit';
}
elseif ($segments[1]=='action') {
	$view = 'action';
}

// check for new plugins
$all_plugins = DB::fetchAll("select * from plugins");
$missing=[];
foreach (Plugin::getPluginNames() as $f) {
	foreach ($all_plugins as $existing_plugin) {
		if ($f==$existing_plugin->location) {
			continue 2; // already exists, skip outer loop
		}
	}
	// reached here, widget type is missing from db
	$missing[] = $f;
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
				$plugin_config_file = Plugin::getPluginPath($missed) . '/plugin_config.json';
				if (is_readable($plugin_config_file)) {
					$w_config = json_decode(File::getContents($plugin_config_file));
					if (!$w_config || !$w_config->title) {
						echo "<li class='list-item has-text-danger'>Skipped <strong>{$missed}</strong> - missing or badly formed <em>plugin_config.json</em></li>";
					}
					else {
						if (!$w_config->description) {
							$w_config->description = "No description found in json file";
						}
						$ok = DB::exec("insert into plugins (title, location, description) values (?,?,?)", [$w_config->title, $missed, $w_config->description]);
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

if ($view && is_dir(File::realpath(dirname(__FILE__) . "/views")) && is_dir(File::realpath(dirname(__FILE__) . "/views/$view"))) {
	$plugin_controller = new Controller(File::realpath(dirname(__FILE__)),$view);
	$plugin_controller->load_view($view);
} else {
	CMS::raise_404();
}

