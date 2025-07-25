<?php

Use HoltBosse\Alba\Core\{Component, Content, Page, Template};
Use HoltBosse\Form\{Input};

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php
	$header = "All Pages";
	$rightContent = "<a href='" . $_ENV["uripath"] . "/admin/pages/edit/0' class='button is-primary pull-right'>
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
			<?php
				if(isset($_ENV["domains"])) {
					echo "<th>Domain</th>";
				}
			?>
			<th>URL</th>
			<th>Template</th>
			<th>ID</th>
		</thead>
		<tbody> 
			<?php foreach($all_pages as $page):?>
			<tr class='page_admin_row'>
				<td>
					<?php
						Component::state_toggle($page->id, $page->state, "pages", NULL, -1);
					?>
				</td>
				<td>
					<?php
					for ($n=0; $n<$page->depth; $n++) {
						echo "<span class='child_indicator'>-&nbsp;</span>";
					}
					?>
					<a href='<?php echo $_ENV["uripath"] . "/admin/pages/edit/" . $page->id . "/" . $page->content_type . "/" . $page->content_view;?>'><?php echo Input::stringHtmlSafe($page->title); ?></a>
					<br>
					<?php 
					if ($page->content_type > 0) {
						echo "<span class='unimportant'>" . Content::get_content_type_title($page->content_type) ;
						echo " &raquo; ";
						echo Content::get_view_title($page->content_view) . "</span>";
						$component_path = Content::get_content_location($page->content_type);
						$component_view = Content::get_view_location($page->content_view);
					}
					else {
						echo "<span class='unimportant'>Widgets only</span>";
					}
					?>
				</td>

				<?php
					if(isset($_ENV["domains"])) {
						echo "<td class='unimportant'>{$domainLookup[$page->domain]}</td>";
					}
				?>

				<td>
					<?php
						$pageInstance = new Page();
						$pageInstance->load_from_id($page->id);
						$url = $pageInstance->get_url();
						$displayUrl = $url;
						if($page->domain!=$_SERVER["HTTP_HOST"]) {
							$url = "https://" . $domainLookup[$page->domain] . $url;
						}
					?>
					<a style="color: var(--bulma-table-color);" target="_blank" class='unimportant' href="<?php echo $url; ?>"><?php echo $displayUrl; ?></a>
				</td>
				
				<td class='unimportant'>
					<span class=''><?php echo  get_template_title($page->template, $all_templates); ?></span>
					<?php if (Page::has_overrides($page->id)) {
						echo "<br><span class='has-text-info widget_override_indicator'>Has Widget Overrides</span>";
					}?>
				</td>
				<td class='unimportant'>
					<span class=''><?php echo $page->id; ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".page_admin_row");
</script>