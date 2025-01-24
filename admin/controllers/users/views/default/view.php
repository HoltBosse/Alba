<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/users/views/default/style.css"); ?>
</style>

<form id='searchform' action="" method="GET"></form>

<form action='' method='post' name='user_action' id='user_action_form'>
	<h1 class='title is-1'>
		Users: <?php echo $group_name; ?>
		<a href='<?php echo Config::uripath() . "/admin/users/edit"?>' class="button is-primary pull-right">
			<span class="icon is-small">
				<i class="fas fa-check"></i>
			</span>
			<span>New User</span>
		</a>
		<!-- user operation toolbar -->
		<div id="user_operations" class="pull-right buttons has-addons">
			<button formaction='<?php echo Config::uripath();?>/admin/users/action/publish' class='button is-primary' type='submit'>Publish</button>
			<button formaction='<?php echo Config::uripath();?>/admin/users/action/unpublish' class='button is-warning' type='submit'>Unpublish</button>
			<button formaction='<?php echo Config::uripath();?>/admin/users/action/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
		</div>
	</h1>

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
					</select>
				</div>
			</div>
		</div>

		

		<div class='field'>
			<label class="label">Group</label>
			<div class='control'>
				<div class="select">
					<select id="user_search_groups" name="groups[]" form="searchform">
						<option value=''>Select Group</option>
						<?php foreach ($all_groups as $g):?>
							<option <?php if (in_array($g->id,$groups)) { echo " selected "; }?> value='<?=$g->id?>'><?=$g->display?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class='field' id='content_search_tags_wrap'>
			<label class="label">Tagged</label>
			<div class='control'>
				<div class="select">
					<select id="content_search_tags" name="coretags[]" form="searchform" multiple>
						<?php foreach ($applicable_tags as $t):?>
							<option <?php if (in_array($t->id, $coretags)) { echo " selected "; }?> value='<?=$t->id?>'><?=Input::stringHtmlSafe($t->title)?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<script>
		new SlimSelect({
			select:'#content_search_tags'
		});
		new SlimSelect({
			select:'#user_search_groups'
		});
		</script>

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

	<table id='all_users_table' class="table">
		<thead>
			<th>Status</th>
			<?php
				if(property_exists("Admin_Config", "show_ids_in_tables") ? Admin_Config::$show_ids_in_tables : false) {
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
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $user->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::uripath();?>/admin/users/action/toggle' name='id[]' value='<?php echo $user->id; ?>'>
						<?php 
						if ($user->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<?php
					if(property_exists("Admin_Config", "show_ids_in_tables") ? Admin_Config::$show_ids_in_tables : false) {
						echo "<td>$user->id</td>";
					}
				?>
				<td>
					<a class='edit_user' href='<?php echo Config::uripath();?>/admin/users/edit/<?php echo $user->id;?>'><?php echo Input::stringHtmlSafe($user->username); ?></a>
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

<?php
	if (!$order_by) {
		Component::create_pagination($user_count, $pagination_size, $cur_page);
	}
?>

<script>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/users/views/default/script.js"); ?>
</script>