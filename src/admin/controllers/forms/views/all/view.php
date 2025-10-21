<?php

Use HoltBosse\Alba\Core\{CMS, User, Component};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<?php
	$rightContent = "<a class='is-primary pull-right button btn' href='" . $_ENV["uripath"] . "/admin/forms/edit/new'>New Form</a>";
	Component::addon_page_title("Forms", null, $rightContent);
?>

<form action='' method='post' name='form_action' id='form_action_form'>

<?php
	$addonButtonGroupArgs = ["content_operations", "forms"];
	Component::addon_button_toolbar($addonButtonGroupArgs);
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
							Component::state_toggle($item->id, $item->state, "forms", NULL, -1);
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

<?php Component::create_pagination($formCount, $pageSize, $cur_page); ?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
