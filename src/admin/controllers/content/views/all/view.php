<?php

Use HoltBosse\Alba\Core\{CMS, JSON, Controller, Configuration, Content, Component, Hook, Tag, User};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Input, Form};
Use HoltBosse\Form\Fields\Select\Select as Field_Select;

?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>

	@media screen and (min-width: 1024px) {
		<?php
			$baseGridCount = 11;
			if($_ENV["admin_show_ids_in_tables"]==="true") {
				$baseGridCount++;
			}
			$baseGridCount += sizeof($content_list_fields);
		?>
		table.table tr {
			grid-template-columns: repeat(<?php echo $baseGridCount; ?>, 1fr);
		}

		table.table td:nth-of-type(<?php echo $_ENV["admin_show_ids_in_tables"]==="true" ? 3 : 2; ?>), table.table th:nth-of-type(<?php echo $_ENV["admin_show_ids_in_tables"]==="true" ? 3 : 2; ?>) {
			grid-column: span 3;
		}
	}
</style>

<?php
	$content_type_fields = Content::get_content_type_fields($content_type_filter);

	$header = "All &ldquo;" . Content::get_content_type_title($content_type_filter) . "&rdquo; Content";
	$byline = $content_type_fields->description;
	$rightContent = "<a class='is-primary button btn' href='" . $_ENV["uripath"] . "/admin/content/edit/new/$content_type_filter'>New &ldquo;" . Content::get_content_type_title($content_type_filter) . "&rdquo; Content</a>";
	Component::addon_page_title($header, $byline, $rightContent);
?>

<form id='searchform' action="" method="GET" style="margin: 0;">
	<div id='content_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search Title/Note</label>
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
			<label class="label">Category</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[1][key]' value='category' form='searchform'/>
					<select name="filters[1][value]" form="searchform">
						<option value=''>Select Category</option>
						<?php foreach ($applicable_categories as $cat):?>
							<option <?php if ($filters['category']==$cat->id) { echo " selected "; }?> value='<?=$cat->id?>'><?=Input::stringHtmlSafe($cat->title)?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class='field'>
			<label class="label">Creator</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[3][key]' value='created_by' form='searchform'/>
					<select name="filters[3][value]" form="searchform">
						<option value=''>Select Creator</option>
						<?php foreach ($applicable_users as $u):?>
							<option <?php if ($filters['created_by']==$u->id) { echo " selected "; }?> value='<?=$u->id?>'><?=Input::stringHtmlSafe($u->username)?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<?php
			$tagFieldOptions = array_map(Function($i) {
				return (object) [
					"text"=>$i->title,
					"value"=>$i->id,
				];
			}, $applicable_tags);

			$tagField = new Field_Select();
			$tagField->loadFromConfig((object) [
				"name"=>"coretags",
				"id"=>"content_search_tags",
				"label"=>"Tagged",
				"multiple"=>"true",
				"slimselect"=>"true",
				"form"=>"searchform",
				"select_options"=>$tagFieldOptions,
				"default"=>json_encode($coretags),
			]);
			$tagField->display();
		?>

		<?php Hook::execute_hook_actions('render_custom_content_search_filters', $content_type_filter); ?>
		
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
</form>

<?php if (!$all_content):?>
	<h2>No content to show!</h2>
<?php else:?>
	<form style="margin: 0;" id='orderform' action="" methpd="GET"></form>

	<form action='' method='post' name='content_action' id='content_action_form'>
	<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
	<?php
		$leftContent = "<a class='button is-primary is-outlined is-small' href='" . $_ENV["uripath"] . "/admin/content/order/{$content_type_filter}'>MANAGE ORDERING</a>";
		$addonButtonGroupArgs = ["content_operations", "content", ["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]];
		Component::addon_button_toolbar($addonButtonGroupArgs, $leftContent);
	?>

	<table class='table can-have-ids'>
		<thead>
			<tr>
				<th>State</th>
				<?php
					if($_ENV["admin_show_ids_in_tables"]==="true") {
						echo "<th>Id</th>";
					}
				?>
				<?php
					//if in ordering mode or search, disable content listing order controls
					if(isset($_GET["filters"])) {
						echo "<style>
								.orderablerow{
									pointer-events: none;

									i {
										display: none !important;
									}
								}
							</style>";
					}
				?>
				<?php make_sortable_header("title"); ?>

				<?php if ($content_list_fields):?>
					<?php foreach ($content_list_fields as $content_list_field):?>
						<th>
						<?php echo $content_list_field->label; ?>
						<?php if (is_array($custom_fields->filters) && in_array ($content_list_field->name, $custom_fields->filters)): ?>
							<br/>
							<select class='auto_filter'>
								
							</select>
						<?php endif ;?>
						</th>
					<?php endforeach; ?>
				<?php endif; ?>

				<th>Tags</th>
				<?php
					make_sortable_header("category");
					make_sortable_header("start");
					make_sortable_header("end");
					make_sortable_header("created by");
					make_sortable_header("updated by");
				?>
				<th>Note</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($all_content as $content_item):?>
				<tr id='row_id_<?php echo $content_item->id;?>' data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
						<?php
							Component::state_toggle($content_item->id, $content_item->state, "content", $custom_fields->states ?? [], $content_item->content_type);
						?>
					</td>
					<?php
						if($_ENV["admin_show_ids_in_tables"]==="true") {
							echo "<td>$content_item->id</td>";
						}
					?>
					<td>
						<div>
							<a href="<?php echo $_ENV["uripath"]; ?>/admin/content/edit/<?php echo $content_item->id;?>/<?php echo $content_item->content_type;?>"><?php echo Input::stringHtmlSafe($content_item->title); ?></a>
						</div>
						<span class='unimportant'>
							<?php
								echo Hook::execute_hook_filters('display_alias_override', $content_item->alias, $content_item);
							?>
						</span>
					</td>

					<?php if ($content_list_fields):?>
						<?php foreach ($content_list_fields as $content_list_field):?>
							<td><?php 
								$propname = "{$content_list_field->name}"; 
								$classname = Form::getFieldClass($content_list_field->type);
								$curfield = new $classname();
								$curfield->loadFromConfig($named_custom_fields[$propname]); // load config - useful for some fields
								$curfield->default = $content_item->$propname; // set temp field value to current stored value
								// TODO: pass precalc array of table names for content types to aid in performance of lookups 
								// some fields will currently parse json config files to determine tables to query for friendly values
								// PER row/field. not ideal.
								echo $curfield->getFriendlyValue($named_custom_fields[$propname]); // pass named field custom field config to help determine friendly value
								?></td>
						<?php endforeach; ?>
					<?php endif; ?>
					
					<td><?php 
					$tags = Tag::get_tags_for_content($content_item->id, $content_item->content_type);
					echo '<div class="tags are-small are-light">';
					foreach ($tags as $tag) {
						echo '<span class="tag is-info is-light">' . Input::stringHtmlSafe($tag->title) . '</span>';
					}
					echo '</div>';
					?>
					</td>

					<td><?php echo Input::stringHtmlSafe($content_item->catname);?></td>
					<td class='unimportant'><?php echo $content_item->start; ?></td>
					<td class='unimportant'><?php echo $content_item->end; ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($content_item->created_by); ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($content_item->updated_by); ?></td>
					<td class='unimportant'><?php echo Input::stringHtmlSafe($content_item->note); ?></td>
				</tr>
				
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

</form>

<?php 
/* CMS::pprint_r ($content_count);
CMS::pprint_r ($pagination_size);
CMS::pprint_r (sizeof($all_content)); */
$num_pages = ceil($content_count/$pagination_size);

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

<?php Component::create_pagination($content_count, $pagination_size, $cur_page);?>

<script>
	window.content_type_filter = <?php echo $content_type_filter; ?>;

	<?php echo file_get_contents(__DIR__ . "/script.js") ?>
</script>
<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
