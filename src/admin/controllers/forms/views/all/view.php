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
	$rightContent = "<a class='is-primary pull-right button btn' href='" . $_ENV["uripath"] . "/admin/forms/edit/new'>New Form</a>";
	
	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Forms",
		"rightContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div>" . $rightContent . "</div>",
			"wrap"=>false
		])
	])->display();
?>

<form action='' method='post' name='form_action' id='form_action_form'>

<?php
	(new AdminButtonToolBar())->loadFromConfig((object)[
		"stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
			"id"=>"content_operations",
			"location"=>"forms",
			"buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
		]),
		"leftContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div></div>",
			"wrap"=>false
		])
	])->display();
?>

<?php if (!$forms):?>
	<h2>No forms found.</h2>
<?php else:?>

	<table class='table'>
		<thead>
			<tr>
				<th>State</th>
				<th>Title</th>
				<th>Created By</th>
				<th>Updated By</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($forms as $item):?>
				<tr id='row_id_<?php echo $item->id;?>' data-itemid="<?php echo $item->id;?>" data-ordering="<?php echo $item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
						<?php
							(new StateButton())->loadFromConfig((object)[
								"itemId"=>$item->id,
								"state"=>$item->state,
								"multiStateFormAction"=>$_ENV["uripath"] . "/admin/forms/action/togglestate",
								"dualStateFormAction"=>$_ENV["uripath"] . "/admin/forms/action/toggle",
								"states"=>NULL,
								"contentType"=>-1
							])->display();
						?>
					</td>
					<td>
						<div>
							<a href="<?php echo $_ENV["uripath"]; ?>/admin/forms/edit/<?php echo $item->id;?>/<?php echo $item->content_type;?>"><?php echo Input::stringHtmlSafe($item->title); ?></a>
						</div>
						<span class='unimportant'>
							<?php
								echo $item->alias;
							?>
						</span>
					</td>
					<td><?php echo User::get_username_by_id($item->created_by); ?></td>
					<td><?php echo User::get_username_by_id($item->updated_by); ?></td>
				</tr>
				
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

</form>

<?php 

$num_pages = ceil($formsCount/$pageSize);

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
		"itemCount"=>$formCount,
		"itemsPerPage"=>$pageSize,
		"currentPage"=>$cur_page
	])->display();
?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
