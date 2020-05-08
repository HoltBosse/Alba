<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<style>
table.table {
	width:100%;
}
.position_single_wrap {
	font-size:70%;
	padding-top:0.3rem;
	border-top:1px solid rgba(0,0,0,0.1);
	margin-top:0.3rem;
	opacity:0.6;
}
span.position_single {
	font-weight:bold;
}
span.page_list, td.note_td, .lighter_note, .unimportant, .usage {
	font-size:70%; opacity:0.6;
}

div.pull-right {
	/* display:inline-block; */
}
#tag_operations {
	margin-right:2rem;
}
.state1 {
	color:#00d1b2;
}
.state0 {
	color:#f66;
}
.hidden_multi_edit {
	display:none;
	/* display:inline-block; */
}
.tag_admin_row.selected {
	background:rgba(200,255,200,0.3);
}
</style>

<form action='' method='post' name='tag_action' id='tag_action_form'>

	<h1 class='title'>All Tags
		<a class='pull-right button is-primary' href='<?php echo Config::$uripath;?>/admin/tags/edit/new'>New Tag</a>
		<!-- tag operation toolbar -->
		<div id="tag_operations" class="pull-right buttons has-addons">
			<button formaction='<?php echo Config::$uripath;?>/admin/tags/action/publish' class='button is-primary' type='submit'>Publish</button>
			<button formaction='<?php echo Config::$uripath;?>/admin/tags/action/unpublish' class='button is-warning' type='submit'>Unpublish</button>
			<button formaction='<?php echo Config::$uripath;?>/admin/tags/action/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
		</div>
	</h1>

	<table class='table'>
		<thead>
			<tr><th>State</th><th>Title</th><th>Available To Use On</th><th>Front-End<th>Note</th>
		</thead>
		<tbody>
		<?php foreach ($all_tags as $tag):?>
			<tr class='tag_admin_row'>
				<td>
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $tag->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::$uripath;?>/admin/tags/action/toggle' name='id[]' value='<?php echo $tag->id; ?>'>
						<?php 
						if ($tag->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<td><a href="<?php echo Config::$uripath; ?>/admin/tags/edit/<?php echo $tag->id;?>"><?php echo $tag->title; ?></a></td>
			
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
				<td class='unimportant'>
					<?php if ($tag->public):?>
					<i class="fas fa-eye"></i>
					<?php else: ?>
					<i class="fas fa-eye-slash"></i>
					<?php endif; ?>
				</td>
				<td class='note_td'><?php echo $tag->note; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script>
	admin_rows = document.querySelectorAll('.tag_admin_row');
	admin_rows.forEach(row => {
		row.addEventListener('click',function(e){
			tr = e.target.closest('tr');
			tr.classList.toggle('selected');
			hidden_checkbox = tr.querySelector('.hidden_multi_edit');
			hidden_checkbox.checked = !hidden_checkbox.checked;
		});
	});
</script>