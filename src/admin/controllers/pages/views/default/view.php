<?php
	Use HoltBosse\Alba\Core\{Component, Content, Page, Template};
	Use HoltBosse\Form\{Input};
	Use HoltBosse\DB\DB;
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

	$header = "All Pages";
	$rightContent = "<a href='" . $_ENV["uripath"] . "/admin/pages/edit/0' class='button is-primary pull-right'>
			<span class='icon is-small'>
				<i class='fas fa-check'></i>
			</span>
			<span>New Page</span>
		</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"All Pages",
		"rightContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div>" . $rightContent . "</div>",
			"wrap"=>false
		])
	])->display();

	$domainLookup = DB::fetchAll("SELECT value FROM `domains`", [], ["mode"=>PDO::FETCH_COLUMN]);
?>

<form action='' method='post' name='page_action' id='page_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"page_operations",
                "location"=>"pages",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();
	?>

	<table id='all_pages_table' class="table">
		<thead>
			<th>Status</th>
			<th>Title</th>
			<?php
				if(sizeof($domainLookup)>1) {
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
						(new StateButton())->loadFromConfig((object)[
							"itemId"=>$page->id,
							"state"=>$page->state,
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/pages/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/pages/action/toggle",
							"states"=>NULL,
							"contentType"=>-1
						])->display();
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
					if(sizeof($domainLookup)>1) {
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