<?php

Use HoltBosse\Alba\Core\{CMS, Component, JSON, Plugin};

?>
<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php Component::addon_page_title("Plugins"); ?>
	
<form action='' method='post' name='plugin_action' id='plugin_action_form'>
	<?php
		$addonButtonGroupArgs = ["plugin_operations", "plugins"];
		Component::addon_button_toolbar($addonButtonGroupArgs);
	?>

	<table class='table'>
		<thead>
			<tr><th>State</th><th>Title</th><th>Description</th><th>Version</th><th>Author</th><th>Website</th>
		</thead>
		<tbody>
		<?php foreach ($all_plugins as $a_plugin):?>
			<?php
			// load config json for each plugin
			$a_plugin_config = JSON::load_obj_from_file (Plugin::getPluginPath($a_plugin->location) . '/plugin_config.json');
			//CMS::pprint_r ($a_plugin_config);
			?>
			<tr class='plugin_admin_row'>
				<td>
					<?php
						Component::state_toggle($a_plugin->id, $a_plugin->state, "plugins", NULL, -1);
					?>
				</td>
				<td><a href="<?php echo $_ENV["uripath"]; ?>/admin/plugins/edit/<?php echo $a_plugin->id;?>"><?php echo $a_plugin_config->title; ?></a></td>
				<td><?php echo $a_plugin_config->description; ?></td>
				<td><?php echo $a_plugin_config->version; ?></td>
				<td><?php echo $a_plugin_config->author; ?></td>
				<td><a href='<?php echo $a_plugin_config->website; ?>'>link</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".plugin_admin_row");
</script>