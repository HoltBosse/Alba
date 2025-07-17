<?php

Use HoltBosse\Form\Input;
Use HoltBosse\Alba\Core\{CMS, User, Hook, Component, Tag};
Use HoltBosse\Form\Fields\Select\Select as Field_Select;

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
	<?php else:?>Edit &ldquo;<?php echo Input::stringHtmlSafe($edit_user->username);?>&rdquo;<?php endif; ?>
</h1>
<form method="POST" action="<?php echo $_ENV["uripath"] . "/admin/users/save";?>" id="new_user_form">
	<input type="hidden" name="http_referer_form" value="<?php echo $_SERVER['HTTP_REFERER'];?>">
	<?php if ($edit_user->email):?>
		<input type='hidden' name='id' value='<?php echo $edit_user->id;?>'/>
	<?php endif; ?>

	<?php
		$core_user_fields_form->display();

		if ($custom_user_fields_form) {
			$custom_user_fields_form->display();
		}
	?>

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

	$tagSelectOptions = array_map(function($i) {
		return (object) [
			"text"=>$i->title,
			"value"=>$i->id,
		];
	}, $all_user_tags);

	$tagSelectField = new Field_Select();
	$tagSelectField->loadFromConfig((object) [
		"name"=>"tags",
		"id"=>"usertags",
		"label"=>"Make Selection:",
		"multiple"=>"true",
		"slimselect"=>"true",
		"select_options"=>$tagSelectOptions,
		"default"=>json_encode($edit_user->tags),
	]);
	$tagSelectField->display();
	?>

	<?php Hook::execute_hook_actions('display_user_fields_form',$edit_user); ?>

	<?php Component::create_fixed_control_bar(); ?>

</form>