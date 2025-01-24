<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/tags/views/show/style.css"); ?>
</style>

<form id='searchform' action="" method="GET"></form>

<form action='' method='post' name='tag_action' id='tag_action_form'>

	<h1 class='title'>All Tags
		<a class='pull-right button is-primary' href='<?php echo Config::uripath();?>/admin/tags/edit/new'>New Tag</a>
		<!-- tag operation toolbar -->
		<div id="tag_operations" class="pull-right buttons has-addons">
			<button formaction='<?php echo Config::uripath();?>/admin/tags/action/publish' class='button is-primary' type='submit'>Publish</button>
			<button formaction='<?php echo Config::uripath();?>/admin/tags/action/unpublish' class='button is-warning' type='submit'>Unpublish</button>
			<button formaction='<?php echo Config::uripath();?>/admin/tags/action/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
		</div>
	</h1>

	<div id='tag_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search Title/Note</label>
			<div class="control">
				<input value="<?php echo $search; ?>" name="search" form="searchform" class="input" type="text" placeholder="">
			</div>
		</div>

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

	<table class='table'>
		<thead>
			<tr><th>State</th><th>Title</th><th>Available To Use On</th><th>Category</th><th>Front-End</th><th>Note</th>
		</thead>
		<tbody>
		<?php foreach ($all_tags as $tag):?>
			<?php if ($search) {
				if (stripos($tag->title,$search)===false && stripos($tag->note,$search)===false) {
					// skip, nothing matching 
					continue;
				}
			}
			?>
			<tr class='tag_admin_row'>
				<td>
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $tag->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::uripath();?>/admin/tags/action/toggle' name='id[]' value='<?php echo $tag->id; ?>'>
						<?php 
						if ($tag->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<td>
					<?php
					/* $tag_o = new Tag(); $tag_o->load($tag->id);
					$depth = $tag_o->get_depth(); */
					for ($n=0; $n<$tag->depth; $n++) {
						echo "&nbsp&#x21B3;&nbsp";
					}
					?>
					<a href="<?php echo Config::uripath(); ?>/admin/tags/edit/<?php echo $tag->id;?>"><?php echo Input::stringHtmlSafe($tag->title); ?></a>
				</td>
			
				<td class='usage'>
				
				<?php 
				$filter_content_type_ids = Tag::get_tag_content_types($tag->id);
				/* CMS::pprint_r ($tag->filter);
				CMS::pprint_r ($filter_content_type_ids); */
				if ($tag->filter==1) {
					if ($filter_content_type_ids) {
						echo "Available for use on all content except:";
						echo "<br><strong>" . Tag::get_tag_content_type_titles($tag->id) . "</strong>";
					}
					else {
						echo "Available for use on <em>all</em> content.";
					}
				}
				elseif ($tag->filter==2) {
					if ($filter_content_type_ids) {
						echo "Available for use <em>only</em> on the following content:";
						echo "<br><strong>" . Tag::get_tag_content_type_titles($tag->id) . "</strong>";
					}
					else {
						echo "<strong>Not available for use on any current content type.</strong>";
					}
				}
				elseif ($tag->filter==0) {
					echo "<em>For admin use only</em>";
				}
				else {
					echo "<span class='is-danger'>Unknown tag filter</span>";
				}
				?>
				</td>

				<td class='note_td'><?php echo $tag->cat_title;?></td>

				<td class='unimportant'>
					<?php if ($tag->public):?>
					<i class="fas fa-eye"></i>
					<?php else: ?>
					<i class="fas fa-eye-slash"></i>
					<?php endif; ?>
				</td>
				
				<td class='note_td'><?php echo Input::stringHtmlSafe($tag->note); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/tags/views/show/script.js"); ?>
</script>