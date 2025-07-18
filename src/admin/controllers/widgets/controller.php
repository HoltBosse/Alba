<?php

Use HoltBosse\Alba\Core\{CMS, Template, Content, Widget, Controller};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\Form;

// router

$segments = CMS::Instance()->uri_segments;
if (sizeof($segments)==0) {
	$view = 'default';
	$widget_id = false;
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

// check for new widget types
$all_widget_types = DB::fetchAll("SELECT * from widget_types");
$missing_widgets=[];
foreach (Widget::getWidgetNames() as $f) {
	foreach ($all_widget_types as $existing_widget_type) {
		if ($f==$existing_widget_type->location) {
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
		<p>New Widget Types Detected</p>
		<button class="delete" aria-label="delete"></button>
	</div>
	<div class="message-body">
		<h5 class='title is-5'>Detected <?php echo sizeof($missing);?> new widget types - attempting installation:</h5>
		<ul class='list'>
			<?php foreach ($missing as $missed) {
				$widget_config_file = Widget::getWidgetPath($missed) . '/widget_config.json';
				if (is_readable($widget_config_file)) {
					$w_config = json_decode(file_get_contents($widget_config_file));
					if (!$w_config || !$w_config->title) {
						echo "<li class='list-item has-text-danger'>Skipped <strong>{$missed}</strong> - missing or badly formed <em>widget_config.json</em></li>";
					}
					else {
						if (!$w_config->description) {
							$w_config->description = "No description found in json file";
						}
						$ok = DB::exec("insert into widget_types (title, location, description) values (?,?,?)", [$w_config->title, $missed, $w_config->description]);
						if ($ok) {
							echo "<li class='list-item'><strong>{$w_config->title}</strong> - {$w_config->description} - <em>Installed</em></li>";
						}
						else {
							echo "<li class='list-item has-text-danger'><strong>{$w_config->title}</strong> - {$w_config->description}</li>";
						}
					}
				}
				else {
					echo "<li class='list-item has-text-danger'>Problem loading <em>widget_config.json</em> in &ldquo;" . $missed . "&rdquo; widget folder</li>";
				}
			}?>
		</ul>
	</div>
	</article>
<?php }

// load model + view

//CMS::queue_message('Test','success');

if ($view && is_dir(realpath(dirname(__FILE__) . "/views")) && is_dir(realpath(dirname(__FILE__) . "/views/$view"))) {
	$widgets_controller = new Controller(realpath(dirname(__FILE__)),$view);
	$widgets_controller->load_view($view);
} else {
	CMS::raise_404();
}

