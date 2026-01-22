<?php

Use HoltBosse\Alba\Core\{CMS, JSON, Controller, Configuration, Content, Component, Hook, Tag, User};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Input, Form};
Use HoltBosse\Form\Fields\Select\Select as Field_Select;
Use Respect\Validation\Validator as v;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\StateButton\StateButton;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;

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
			$baseGridCount = $baseGridCount - sizeof($hidden_list_fields);
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
	$rightContent = "<a class='is-primary button btn' href='" . $_ENV["uripath"] . "/admin/content/edit/new/$content_type_filter'>New &ldquo;" . Content::get_content_type_title($content_type_filter) . "&rdquo; Content</a>";
	(new TitleHeader())->loadFromConfig((object)[
		"header"=>html_entity_decode($header),
		"byline"=>$content_type_fields->description,
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

<?php if (!$all_content):?>
	<h2>No content to show!</h2>
<?php else:?>
	<form style="margin: 0;" id='orderform' action="" methpd="GET"></form>

	<form action='' method='post' name='content_action' id='content_action_form'>
	<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
	<?php
		$leftContent = "<a class='button is-primary is-outlined is-small' href='" . $_ENV["uripath"] . "/admin/content/order/{$content_type_filter}'>MANAGE ORDERING</a>";

		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"content_operations",
                "location"=>"content",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div>" . $leftContent . "</div>",
                "wrap"=>false
            ])
        ])->display();
	?>

	<table class='table can-have-ids'>
		<thead>
			<tr>
				<?php if(!in_array("state", $hidden_list_fields)) { echo "<th>State</th>"; } ?>
				<?php
					if($_ENV["admin_show_ids_in_tables"]==="true" && !in_array("id", $hidden_list_fields)) {
						echo "<th>Id</th>";
					}
				?>
				<?php
					//if in ordering mode or search, disable content listing order controls
					if(Input::getVar("filters", v::AlwaysValid(), null)) {
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
				<?php if(!in_array("title", $hidden_list_fields)) { make_sortable_header("title"); } ?>

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

				<?php 
					if(!in_array("tags", $hidden_list_fields)) { echo "<th>Tags</th>"; }

					if(!in_array("category", $hidden_list_fields)) { make_sortable_header("category"); }
					if(!in_array("start", $hidden_list_fields)) { make_sortable_header("start"); }
					if(!in_array("end", $hidden_list_fields)) { make_sortable_header("end"); }
					if(!in_array("created_by", $hidden_list_fields)) { make_sortable_header("created by"); }
					if(!in_array("updated_by", $hidden_list_fields)) { make_sortable_header("updated by"); }
					
					if(!in_array("note", $hidden_list_fields)) { echo "<th>Note</th>"; }
				?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($all_content as $content_item):?>
				<tr id='row_id_<?php echo $content_item->id;?>' data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>" class='content_admin_row'>
					<?php if(!in_array("state", $hidden_list_fields)) { ?>
						<td class='drag_td state-wrapper'>
							<?php
								(new StateButton())->loadFromConfig((object)[
									"itemId"=>$content_item->id,
									"state"=>$content_item->state,
									"multiStateFormAction"=>$_ENV["uripath"] . "/admin/content/action/togglestate",
									"dualStateFormAction"=>$_ENV["uripath"] . "/admin/content/action/toggle",
									"states"=>$custom_fields->states ?? [],
									"contentType"=>$content_item->content_type
								])->display();
							?>
						</td>
					<?php } ?>
					<?php
						if($_ENV["admin_show_ids_in_tables"]==="true" && !in_array("id", $hidden_list_fields)) {
							echo "<td class='id-wrapper'>$content_item->id</td>";
						}
					?>
					<?php if(!in_array("title", $hidden_list_fields)) { ?>
						<td class='title-wrapper'>
							<div>
								<a href="<?php echo $_ENV["uripath"]; ?>/admin/content/edit/<?php echo $content_item->id;?>/<?php echo $content_item->content_type;?>"><?php echo Input::stringHtmlSafe($content_item->title); ?></a>
							</div>
							<span class='unimportant'>
								<?php
									echo Hook::execute_hook_filters('display_alias_override', $content_item->alias, $content_item);
								?>
							</span>
						</td>
					<?php } ?>

					<?php if ($content_list_fields):?>
						<?php foreach ($content_list_fields as $content_list_field):?>
							<td dataset-name="<?php echo $content_list_field->label; ?>"><?php 
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
					
					<?php if(!in_array("tags", $hidden_list_fields)) { ?>
						<td dataset-name="Tags"><?php //we need to have the td start and end butted right up to the php tags so that css empty will work
							$tags = Tag::get_tags_for_content($content_item->id, $content_item->content_type);
							if(sizeof($tags) > 0) { //wee use css empty, so cant have blank tags inside
								echo '<div class="tags are-small are-light">';
									foreach ($tags as $tag) {
										echo '<span class="tag is-info is-light">' . Input::stringHtmlSafe($tag->title) . '</span>';
									}
								echo '</div>';
							}
						?></td>
					<?php } ?>

					<?php if(!in_array("category", $hidden_list_fields)) { ?>
						<td dataset-name="Cat"><?php echo Input::stringHtmlSafe($content_item->catname);?></td>
					<?php } ?>
					<?php if(!in_array("start", $hidden_list_fields)) { ?>
						<td dataset-name="Start" class='unimportant'><?php echo $content_item->start; ?></td>
					<?php } ?>
					<?php if(!in_array("end", $hidden_list_fields)) { ?>
						<td dataset-name="End" class='unimportant'><?php echo $content_item->end; ?></td>
					<?php } ?>
					<?php if(!in_array("created_by", $hidden_list_fields)) { ?>
						<td dataset-name="Created By" class='unimportant'><?php echo User::get_username_by_id($content_item->created_by); ?></td>
					<?php } ?>
					<?php if(!in_array("updated_by", $hidden_list_fields)) { ?>
						<td dataset-name="Updated By" class='unimportant'><?php echo User::get_username_by_id($content_item->updated_by); ?></td>
					<?php } ?>
					<?php if(!in_array("note", $hidden_list_fields)) { ?>
						<td dataset-name="Note" class='unimportant'><?php echo Input::stringHtmlSafe($content_item->note); ?></td>
					<?php } ?>
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

<?php
	(new Pagination())->loadFromConfig((object)[
		"id"=>"pagination_component",
		"itemCount"=>$content_count,
		"itemsPerPage"=>$pagination_size,
		"currentPage"=>$cur_page
	])->display();
?>

<script>
	window.content_type_filter = <?php echo $content_type_filter; ?>;

	<?php echo file_get_contents(__DIR__ . "/script.js") ?>
</script>
<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
