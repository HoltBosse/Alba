<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/pages/views/default/style.css"); ?>
</style>

<?php
	$header = "All Pages";
	$rightContent = "<a href='" . Config::uripath() . "/admin/pages/edit/0' class='button is-primary pull-right'>
			<span class='icon is-small'>
				<i class='fas fa-check'></i>
			</span>
			<span>New Page</span>
		</a>";
	Component::addon_page_title($header, null, $rightContent);
?>

<form action='' method='post' name='page_action' id='page_action_form'>
	<?php
		$addonButtonGroupArgs = ["page_operations", "pages"];
		Component::addon_button_toolbar($addonButtonGroupArgs);
	?>

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
					<a href='<?php echo Config::uripath() . "/admin/pages/edit/" . $page->id . "/" . $page->content_type . "/" . $page->content_view;?>'><?php echo Input::stringHtmlSafe($page->title); ?></a>
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

<script type="module">
	import {handleAdminRows} from "/core/js/admin_row.js";
	handleAdminRows(".page_admin_row");
</script>