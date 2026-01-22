<?php

Use HoltBosse\Alba\Core\{CMS, User, Component};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\StateButton\StateButton;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php
	$rightContent = "<a class='is-primary pull-right button btn' href='" . $_ENV["uripath"] . "/admin/redirects/edit/new'>New Redirect</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Redirects",
		"rightContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div>" . $rightContent . "</div>",
			"wrap"=>false
		])
	])->display();
?>

<form style="margin-bottom: 0;">
	<?php
		$searchForm->display();
	?>
</form>

<form action='' method='post' name='redirect_action' id='redirect_action_form'>

<?php
	(new AdminButtonToolBar())->loadFromConfig((object)[
		"stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
			"id"=>"content_operations",
			"location"=>"redirects",
			"buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
		]),
		"leftContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div></div>",
			"wrap"=>false
		])
	])->display();
?>

<?php if (!$redirects):?>
	<h2>No redirects found.</h2>
<?php else:?>

	<table class='table'>
		<thead>
			<tr>
				<th>State</th>
				<th>Source</th>
				<th>Destination</th>
				<th>Referer</th>
				<th>Created</th>
				<th>Created By</th>
				<th>Updated</th>
				<th>Updated By</th>
				<th><a href="?order_by=hits">Hits</a></th>
				<th>Header</th>
				<th>Note</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($redirects as $redirect_item):?>
				<tr id='row_id_<?php echo $redirect_item->id;?>' data-itemid="<?php echo $redirect_item->id;?>" data-ordering="<?php echo $redirect_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
						<?php
							(new StateButton())->loadFromConfig((object)[
								"itemId"=>$redirect_item->id,
								"state"=>$redirect_item->state,
								"multiStateFormAction"=>$_ENV["uripath"] . "/admin/redirects/action/togglestate",
								"dualStateFormAction"=>$_ENV["uripath"] . "/admin/redirects/action/toggle",
								"states"=>NULL,
								"contentType"=>-1
							])->display();
						?>
					</td>
					<td class='limitwidth'><a href='<?php echo $_ENV["uripath"]; ?>/admin/redirects/edit/<?php echo $redirect_item->id;?>/'><?php echo $redirect_item->old_url; ?></a></td>
					<td class='limitwidth'><?php echo $redirect_item->new_url; ?></td>
					<td class='limitwidth unimportant'><?php echo $redirect_item->referer; ?></td>
					<td class='unimportant'><?php echo $redirect_item->created; ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($redirect_item->created_by); ?></td>
					<td class='unimportant'><?php echo $redirect_item->updated; ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($redirect_item->updated_by); ?></td>
					<td class='unimportant'><?php echo $redirect_item->hits; ?></td>
					<td class='unimportant'><?php echo $redirect_item->header; ?></td>
					<td class='unimportant'><?php echo $redirect_item->note; ?></td>
				</tr>
				
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

</form>

<?php 

$num_pages = ceil($redirect_count/$page_size);

//$url_query_params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$url_query_params = $_GET;
$url_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($cur_page) {
	// not ordering view and page url is either 1 or no passed and assumed to be 1 in model
	$url_query_params['page'] = $cur_page+1;
	$next_url_params = http_build_query($url_query_params);
	$url_query_params['page'] = $cur_page-1;
	$prev_url_params = http_build_query($url_query_params);
	/* CMS::pprint_r ($url_query_params);
	CMS::pprint_r ($next_url_params); */
}

?>

<?php
	(new Pagination())->loadFromConfig((object)[
		"id"=>"pagination_component",
		"itemCount"=>$redirect_count,
		"itemsPerPage"=>$page_size,
		"currentPage"=>$cur_page
	])->display();
?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
