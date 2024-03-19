<?php
defined('CMSPATH') or die; // prevent unauthorized access

function in_group ($group_id, $edit_user) {
	foreach ($edit_user->groups as $group) {
		if ($group->id==$group_id) {
			return true;
		}
	}
	return false;
}

?>
<h1 class='title is-1'>
	<?php if (!$edit_user->email):?>New User
	<?php else:?>Edit &ldquo;<?php echo $edit_user->username;?>&rdquo;<?php endif; ?>
</h1>
<form method="POST" action="<?php echo Config::uripath() . "/admin/users/save";?>" id="new_user_form">
	<?php if ($edit_user->email):?>
		<input type='hidden' name='id' value='<?php echo $edit_user->id;?>'/>
	<?php endif; ?>
	<div class="field">
		<label class="label">Username</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="username" class="input iss-success" type="text" placeholder="Username" value="<?php if ($edit_user->email) { echo $edit_user->username; } ?>">
			<span class="icon is-small is-left">
			<i class="fas fa-user"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<!-- <p class="help is-success">This username is available</p> -->
	</div>

	<div class="field">
		<label class="label">Email</label>
		<div class="control has-icons-left has-icons-right">
			<input required name="email" class="input iss-success" type="email" placeholder="email@wherever.com" value="<?php echo $edit_user->email;?>">
			<span class="icon is-small is-left">
			<i class="fas fa-envelope"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
	<!-- <p class="help is-success">This username is available</p> -->
	</div>

	<div class="field">
		<label class="label">Password</label>
		<div class="control has-icons-left has-icons-right">
			<!-- password field only required for new user -->
			<input <?php if (!$edit_user->email):?>required<?php endif;?> name="password" class="input iss-success" type="password" placeholder="Password" value="">
			<span class="icon is-small is-left">
			<i class="fas fa-lock"></i>
			</span>
			<!-- <span class="icon is-small is-right">
				<i class="fas fa-check"></i>
			</span> -->
		</div>
		<?php if ($edit_user->email):?><p class="help">Leave blank if no password change required</p><?php endif; ?>
	</div>

	<h2 class="title">User Groups</h2>
	<p class='help'>At least one group should be selected, but this is not enforced.</p><br>
	<div class="field">
		<?php 
		/* CMS::pprint_r ($all_groups);
		CMS::pprint_r ($edit_user); */
		?>
		<?php foreach ($all_groups as $group):?>
		<div class="control">
			<label class="checkbox">
				<input name='groups[]' value='<?php echo $group->id;?>' type="checkbox" <?php if (in_group($group->id, $edit_user)) echo " checked " ?>  >
				<?php echo $group->display;?>
			</label>
		</div>
		<br>
		<?php endforeach; ?>
	</div>

	<h2 class="title">User Tags</h2>
	<?php
	$all_user_tags = Tag::get_tags_available_for_content_type(-2); // -2 is user tags
	
	echo "<div class='field'>";
		echo "<label class='label'>Make selection:</label>";
		echo "<div class='control'>";
			echo "<div class='select is-multiple'>";
				echo "<select class='is-multiple' multiple id='usertags' name='tags[]'>";

					foreach ($all_user_tags as $tag) {
						if ($tag->state>0) {
							$selected = "";
							if (in_array($tag->id, $edit_user->tags)) { $selected="selected";}
							echo "<option {$selected} value='{$tag->id}'>{$tag->title}</option>";
						}
					}
				echo "</select>";
			echo "</div>";
		echo "</div>";
	echo "</div>";
	echo "<script>new SlimSelect({ select: '#usertags' });</script>"; 
	?>

	<?php Hook::execute_hook_actions('display_user_fields_form',$edit_user); ?>

	<?php if ($custom_user_fields_form) {
		$custom_user_fields_form->display_front_end();
	} ?>

	<div class="clear control">
		<button type="submit" class="button is-primary">Save</button>
	</div>

</form>