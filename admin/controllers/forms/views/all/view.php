<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/forms/views/all/style.css"); ?>
</style>

<h1 class="title is-1">All Forms
	<a class="is-primary pull-right button btn" href="/admin/forms/edit/new">New Form</a>
	<span class="unimportant subheading">Create Forms</span>
	<?php
		//disabled at this time, here so this functionality may be added in the future
		//Component::addon_button_group("content_operations", "forms", ["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]);
	?>
</h1>

<form>
	<?php
		$searchForm->display_front_end();
	?>
</form>

<table class='table'>
	<thead>
		<tr>
			<th>State</th>
			<th>Title</th>
			<th>Tags</th>
			<th>Category</th>
			<th>Start</th>
			<th>End</th>
			<th>Created By</th>
			<th>Updated By</th>
			<th>Note</th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($formItems as $item) {
				?>
					<tr id='row_id_<?php echo $item->id;?>' data-itemid="<?php echo $item->id;?>" class='form_admin_row'>
						<td>
							<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $item->id; ?>'/>
							<button class='button' type='submit' formaction='<?php echo Config::uripath();?>/admin/forms/action/toggle' name='id[]' value='<?php echo $item->id; ?>'>
								<?php 
									if ($item->state==1) { 
										echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
									} else {
										echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
									}
								?>
							</button>
						</td>
						<td>
							<a href="<?php echo Config::uripath(); ?>/admin/forms/edit/<?php echo $item->id;?>"><?php echo Input::stringHtmlSafe($item->title); ?></a>
							<br>
							<span class='unimportant'>
								<?php
									echo Hook::execute_hook_filters('display_alias_override', $item->alias, $item);
								?>
							</span>
						</td>
						<td>
							<?php 
								/* $tags = Tag::get_tags_for_content($content_item->id, $content_item->content_type);
								echo '<div class="tags are-small are-light">';
								foreach ($tags as $tag) {
									echo '<span class="tag is-info is-light">' . Input::stringHtmlSafe($tag->title) . '</span>';
								}
								echo '</div>'; */
								echo "TODO: TAGS";
							?>
						</td>
						<td><?php echo Input::stringHtmlSafe($item->catname);?></td>
						<td class='unimportant'><?php echo $item->start; ?></td>
						<td class='unimportant'><?php echo $item->end; ?></td>
						<td class='unimportant'><?php echo Input::stringHtmlSafe($item->created_by); ?></td>
						<td class='unimportant'><?php echo Input::stringHtmlSafe($item->updated_by); ?></td>
						<td class='unimportant'><?php echo Input::stringHtmlSafe($item->note); ?></td>
					</tr>
				<?php
			}
		?>
	</tbody>
</table>