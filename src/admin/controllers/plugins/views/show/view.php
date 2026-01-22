<?php

Use HoltBosse\Alba\Core\{CMS, Component, JSON, Plugin};
Use HoltBosse\Alba\Components\StateButton\StateButton;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;

?>
<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php
	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Plugins",
	])->display();
?>
	
<form action='' method='post' name='plugin_action' id='plugin_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"plugin_operations",
                "location"=>"plugins",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();
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
						(new StateButton())->loadFromConfig((object)[
							"itemId"=>$a_plugin->id,
							"state"=>$a_plugin->state,
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/plugins/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/plugins/action/toggle",
							"states"=>NULL,
							"contentType"=>-1
						])->display();
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