<?php

Use HoltBosse\Alba\Core\{CMS, User, Component};
Use HoltBosse\Form\Input;
Use HoltBosse\DB\DB;

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<form id='searchform' action="" method="GET"></form>

<?php
	$rightContent = "<a class='is-primary pull-right button btn' href='" . $_ENV["uripath"] . "/admin/redirects/edit/new'>New Redirect</a>";
	Component::addon_page_title("Redirects", null, $rightContent);
?>

	<div id='content_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search URL/Note</label>
			<div class="control">
				<input value="<?php echo $search; ?>" name="search" form="searchform" class="input" type="text" placeholder="">
			</div>
		</div>

		<div class='field'>
			<label class="label">State</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[2][key]' value='state' form='searchform'/>
					<select name="filters[2][value]" form="searchform">
						<option value=''>State</option>
						<option <?php if ($filters['state']==='1') { echo " selected "; }?> value='1'>Published</option>
						<option <?php if ($filters['state']==='0') { echo " selected "; }?> value='0'>Unpublished</option>
						<option <?php if ($filters['state']==='-1') { echo " selected "; }?> value='-1'>Deleted</option>
						<?php
							foreach($custom_fields->states as $state) {
								echo "<option " . ($filters['state']==$state->state ? "selected" : false) . " value='$state->state'>" . ucwords($state->name) . "</option>";
							}
						?>
					</select>
				</div>
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

<form action='' method='post' name='redirect_action' id='redirect_action_form'>

<?php
	$addonButtonGroupArgs = ["content_operations", "redirects"];
	Component::addon_button_toolbar($addonButtonGroupArgs);
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
				<?php CMS::Instance()->listing_content_id = $redirect_item->id; ?>
				<tr id='row_id_<?php echo $redirect_item->id;?>' data-itemid="<?php echo $redirect_item->id;?>" data-ordering="<?php echo $redirect_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
						<?php
							Component::state_toggle($redirect_item->id, $redirect_item->state, "redirects", NULL, -1);
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

<?php Component::create_pagination($redirect_count, $page_size, $cur_page); ?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
