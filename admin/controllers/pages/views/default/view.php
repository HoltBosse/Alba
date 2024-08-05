<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
.unimportant {
	font-size:70%;
	opacity:0.5;
}
.child_indicator {
	opacity:0.2;
	font-size:120%;
}
#all_pages_table {
	width:100%;
}

.state1 {
	color:rgba(50,200,50,0.5);
}


</style>

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


div.pull-right {
	/* display:inline-block; */
}
#page_operations {
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
.page_admin_row.selected {
	background:rgba(200,255,200,0.3);
}
</style>

<form action='' method='post' name='page_action' id='page_action_form'>

	<h1 class='title is-1'>
		All Pages
		<a href='<?php echo Config::uripath() . "/admin/pages/edit/0"?>' class="button is-primary pull-right">
			<span class="icon is-small">
				<i class="fas fa-check"></i>
			</span>
			<span>New Page</span>
		</a>
		<?php Component::addon_button_group("page_operations", "pages", ["publish"=>"primary","unpublish"=>"warning","duplicate"=>"default","delete"=>"danger"]); ?>
	</h1>

	<table id='all_pages_table' class="table">
		<thead>
			<th>Status</th>
			<th>Title</th>
			<th>URL</th>
			<!-- <th>Content</th> -->
			<th>Template</th>
			<!-- <th>Configuration</th> -->
			<!-- <th>Created</th> -->
			<th>ID</th>
		</thead>
		<tbody>
			<?php foreach($all_pages as $page):?>
			<tr class='page_admin_row'>
				<td>
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $page->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::uripath();?>/admin/pages/action/toggle' name='id[]' value='<?php echo $page->id; ?>'>
						<?php 
						if ($page->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<td>
					<?php
					for ($n=0; $n<$page->depth; $n++) {
						echo "<span class='child_indicator'>-&nbsp;</span>";
					}
					?>
					<a href='<?php echo Config::uripath() . "/admin/pages/edit/" . $page->id . "/" . $page->content_type . "/" . $page->content_view;?>'><?php echo $page->title; ?></a>
					<br>
					<?php 
					if ($page->content_type > 0) {
						echo "<span class='unimportant'>" . Content::get_content_type_title($page->content_type) ;
						echo " &raquo; ";
						echo Content::get_view_title($page->content_view) . "</span>";
						//echo "<br><p>TODO: get options nice</p>";
						$component_path = Content::get_content_location($page->content_type);
						$component_view = Content::get_view_location($page->content_view);
						// TODO - maybe make this an option to view content info on pages overview? it works!
						/* $view_options = new View_Options($component_path, $component_view, $page->content_view_configuration);
						$content_info = $view_options->get_content_info();
						if ($content_info) {
							echo "<p>{$content_info}</p>";
						} */
					}
					else {
						echo "<span class='unimportant'>Widgets only</span>";
					}
					?>
				</td>
				<td>
					<span class='unimportant'><?php echo $page->alias; ?></span>
				</td>
			
				
				<td class='unimportant'>
					<span class=''><?php echo  get_template_title($page->template, $all_templates); ?></span>
					<?php if (Page::has_overrides($page->id)) {
						echo "<br><span class='has-text-info widget_override_indicator'>Has Widget Overrides</span>";
					}?>
				</td>
				<!-- <td>
					<span class='unimportant'><?php //echo $page->content_view_configuration; ?></span>
				</td> -->
				<!-- <td>
					<span class='unimportant'><?php //echo $page->updated; ?></span>
				</td> -->
				<td class='unimportant'>
					<span class=''><?php echo $page->id; ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script>
	admin_rows = document.querySelectorAll('.page_admin_row');
	admin_rows.forEach(row => {
		row.addEventListener('click',function(e){
			tr = e.target.closest('tr');
			tr.classList.toggle('selected');
			hidden_checkbox = tr.querySelector('.hidden_multi_edit');
			hidden_checkbox.checked = !hidden_checkbox.checked;
		});
	});
</script>