<?php

Use HoltBosse\Alba\Core\{CMS, Component, Hook, User, Tag, Form};
Use HoltBosse\Form\Fields\Select\Select as Field_Select;
Use HoltBosse\Form\Input;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\StateButton\StateButton;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
	<?php if($_ENV["admin_show_ids_in_tables"]==="true") { ?>
		@media screen and (max-width: 1023px) {
			table.table th:nth-of-type(1), table.table th:nth-of-type(3), table.table td:nth-of-type(1), table.table td:nth-of-type(3) {
				display: block;
			}
			table.table th:nth-of-type(2), table.table td:nth-of-type(2) {
				display: none;
			}
			table.table th:nth-of-type(3), table.table td:nth-of-type(3) {
				width: 100%;
			}
		}
	<?php } ?>
</style>

<?php
	$rightContent = "<a href='" . $_ENV["uripath"] . "/admin/users/edit' class='button is-primary'>
		<span class='icon is-small'>
			<i class='fas fa-check'></i>
		</span>
		<span>New User</span>
	</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Users: " . $group_name,
		"rightContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div>" . $rightContent . "</div>",
			"wrap"=>false
		])
	])->display();
?>

<form style="margin-bottom: 0;">
	<?php
		$searchForm->display();
	?>
</form>

<form action='' method='post' name='user_action' id='user_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"user_operations",
                "location"=>"users",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();
	?>
	<table id='all_users_table can-have-ids' class="table">
		<thead>
			<th>Status</th>
			<?php
				if($_ENV["admin_show_ids_in_tables"]==="true") {
					echo "<th>Id</th>";
				}
			?>
			<th>Name</th>
			<th>Email</th>
			<?php if ($content_list_fields):?>
				<?php foreach ($content_list_fields as $content_list_field):?>
					<th><?php echo $content_list_field->label; ?></th>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if (!$group_id):?><th>Group(s)</th><?php endif; ?>
			<th>Tags</th>
			<!-- <th>Created</th>
			<th>ID</th> -->
		</thead>
		<tbody>
			<?php foreach($all_users as $user):?> 
			<tr class='user_admin_row'>
				<td>
					<?php
						$statesForToggle = $states;
						if(!is_array($statesForToggle)) {
							$statesForToggle = [];
						}
						$statesForToggle = array_merge([(object) ["state"=>2,"name"=>"Published - Pwd Reset Req", "color"=>"lime"]], $statesForToggle);
						(new StateButton())->loadFromConfig((object)[
							"itemId"=>$user->id,
							"state"=>$user->state,
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/users/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/users/action/toggle",
							"states"=>$statesForToggle,
							"contentType"=>-1
						])->display();
					?>
				</td>
				<?php
					if($_ENV["admin_show_ids_in_tables"]==="true") {
						echo "<td>$user->id</td>";
					}
				?>
				<td>
					<a class='edit_user' href='<?php echo $_ENV["uripath"];?>/admin/users/edit/<?php echo $user->id;?>'><?php echo Input::stringHtmlSafe($user->username); ?></a>
				</td>
				<td>
					<?php echo Input::stringHtmlSafe($user->email); ?>
				</td>
				<?php if ($content_list_fields):?>
					<?php $named_custom_fields = array_column(json_decode(file_get_contents($_ENV["custom_user_fields_file_path"]))->fields, null, 'name'); ?>
					<?php foreach ($content_list_fields as $content_list_field):?>
						<td dataset-name="<?php echo $content_list_field->label; ?>"><?php 
							$propname = "{$content_list_field->name}"; 
							$classname = Form::getFieldClass($content_list_field->type);
							$curfield = new $classname();
							$curfield->loadFromConfig($named_custom_fields[$propname]); // load config - useful for some fields
							$curfield->default = $customUserFieldsLookup[$user->id]->$propname; // set temp field value to current stored value
							// TODO: pass precalc array of table names for content types to aid in performance of lookups 
							// some fields will currently parse json config files to determine tables to query for friendly values
							// PER row/field. not ideal.
							echo $curfield->getFriendlyValue($named_custom_fields[$propname]); // pass named field custom field config to help determine friendly value
							?></td>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if (!$group_id):?>
					<td>
						<?php 
						$groups = User::get_all_groups_for_user($user->id);
						echo '<div class="tags are-small are-light">';
						foreach ($groups as $group) {
							echo '<span class="tag is-info is-light">' . $group->display . '</span>';
						}
						echo '</div>';
						?>
					</td>
				<?php endif; ?>
				<td><?php 
					$tags = Tag::get_tags_for_content($user->id, -2);
					echo '<div class="tags are-small are-light">';
					foreach ($tags as $tag) {
						echo '<span class="tag is-info is-light">' . $tag->title . '</span>';
					}
					echo '</div>';
					?>
				</td>
				<!-- <td>
					<?php echo $user->created; ?>
				</td>
				<td>
					<?php echo $user->id; ?>
				</td> -->
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

</form>

<?php
	(new Pagination())->loadFromConfig((object)[
		"id"=>"pagination_component",
		"itemCount"=>$user_count,
		"itemsPerPage"=>$pagination_size,
		"currentPage"=>$cur_page
	])->display();
?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".user_admin_row");
</script>