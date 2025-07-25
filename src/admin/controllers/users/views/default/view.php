<?php

Use HoltBosse\Alba\Core\{CMS, Component, Hook, User, Tag};
Use HoltBosse\Form\Fields\Select\Select as Field_Select;
Use HoltBosse\Form\Input;

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php
	$header = "Users: " . $group_name;
	$rightContent = "<a href='" . $_ENV["uripath"] . "/admin/users/edit' class='button is-primary'>
		<span class='icon is-small'>
			<i class='fas fa-check'></i>
		</span>
		<span>New User</span>
	</a>";
	Component::addon_page_title($header, null, $rightContent);
?>

<form id='searchform' action="" method="GET" style="margin: 0;">
	<div id='content_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search Name/Email</label>
			<div class="control">
				<input value="<?php echo $search; ?>" name="search" form="searchform" class="input" type="text" placeholder="">
			</div>
		</div>

		<div class='field'>
			<label class="label">State</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[2][key]' value='state' form='searchform'/>
					<select name="filters[2][value]" form="searchform">
						<option value=''>State</option>
						<option <?php if ($filters['state']==='1') { echo " selected "; }?> value='1'>Enabled</option>
						<option <?php if ($filters['state']==='0') { echo " selected "; }?> value='0'>Disabled</option>
						<option <?php if ($filters['state']==='-1') { echo " selected "; }?> value='-1'>Deleted</option>
						<?php
							if($states!==NULL) {
								foreach($states as $state) {
									$selected = $filters['state']==$state->state ? "selected" : "";
									echo "<option $selected value='$state->state'>$state->name</option>";
								}
							}
						?>
					</select>
				</div>
			</div>
		</div>

		<?php
			$groupFieldOptions = array_map(Function($i) {
				return (object) [
					"text"=>$i->display,
					"value"=>$i->id,
				];
			}, $all_groups);

			$groupField = new Field_Select();
			$groupField->loadFromConfig((object) [
				"name"=>"groups",
				"id"=>"user_search_groups",
				"label"=>"Group",
				"multiple"=>"true",
				"slimselect"=>"true",
				"form"=>"searchform",
				"select_options"=>$groupFieldOptions,
				"default"=>json_encode($groups),
			]);
			$groupField->display();



			$tagFieldOptions = array_map(Function($i) {
				return (object) [
					"text"=>$i->title,
					"value"=>$i->id,
				];
			}, $applicable_tags);

			$tagField = new Field_Select();
			$tagField->loadFromConfig((object) [
				"name"=>"coretags",
				"id"=>"content_search_tags",
				"label"=>"Tagged",
				"multiple"=>"true",
				"slimselect"=>"true",
				"form"=>"searchform",
				"select_options"=>$tagFieldOptions,
				"default"=>json_encode($coretags),
			]);
			$tagField->display();
		?>

		<?php Hook::execute_hook_actions('render_custom_user_search_filters'); ?>
		
		<div class='field'>
			<label class="label">&nbsp;</label>
			<div class='control'>
				<button form="searchform" type="submit" class="button is-info">
					Search
				</button>
			</div>
		</div>

		<div class='field'>
			<label class="label">&nbsp;</label>
			<div class='control'>
				<button form="searchform" type="button" value="" onclick='window.location = window.location.href.split("?")[0]; return false;' class="button is-default">
					Clear
				</button>
			</div>
		</div>
	</div>
</form>

<form action='' method='post' name='user_action' id='user_action_form'>
	<?php
		$addonButtonGroupArgs = ["user_operations", "users", ["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]];
		Component::addon_button_toolbar($addonButtonGroupArgs);
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
						Component::state_toggle($user->id, $user->state, "users", $states, -1);
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

<?php Component::create_pagination($user_count, $pagination_size, $cur_page);?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".user_admin_row");
</script>