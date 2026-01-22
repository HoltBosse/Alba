<?php

Use HoltBosse\Alba\Core\{Component, Tag, CMS};
Use HoltBosse\Form\{Input, Form};
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
	$header = "All Tags";
	$rightContent = "<a class='pull-right button is-primary' href='" . $_ENV["uripath"] ."/admin/tags/edit/new'>New Tag</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"All Tags",
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

<form action='' method='post' name='tag_action' id='tag_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"tag_operations",
                "location"=>"tags",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();
	?>

	<table class='table'>
		<thead>
			<tr>
				<th>State</th><th>Title</th>
				<?php
					if($content_list_fields) {
						foreach($content_list_fields as $field) {
							echo "<th>" . Input::stringHtmlSafe($field->label) . "</th>";
						}
					}
				?>
				<th>Available To Use On</th><th>Category</th><th>Front-End</th><th>Note</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($all_tags as $tag):?>
			<tr class='tag_admin_row' data-itemid="<?php echo $tag->id;?>">
				<td>
					<?php
						$customStates = [];
						if (isset($_ENV["tag_custom_fields_file_path"])) {
							$customFieldsFormObject = json_decode(file_get_contents($_ENV["tag_custom_fields_file_path"]));
							if(isset($customFieldsFormObject->states)) {
								$customStates = $customFieldsFormObject->states;
							}
						}
						(new StateButton())->loadFromConfig((object)[
							"itemId"=>$tag->id,
							"state"=>$tag->state,
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/tags/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/tags/action/toggle",
							"states"=>$customStates,
							"contentType"=>-1
						])->display();
					?>
				</td>
				<td>
					<?php
					/* $tag_o = new Tag(); $tag_o->load($tag->id);
					$depth = $tag_o->get_depth(); */
					for ($n=0; $n<$tag->depth; $n++) {
						echo "&nbsp&#x21B3;&nbsp";
					}
					?>
					<a href="<?php echo $_ENV["uripath"]; ?>/admin/tags/edit/<?php echo $tag->id;?>"><?php echo Input::stringHtmlSafe($tag->title); ?></a>
				</td>

				<?php if ($content_list_fields):?>
						<?php
							$customFieldsDataValues = $tag->custom_fields!="" ? json_decode($tag->custom_fields ?? "[]") : [];
							$customFieldsDataValuesNormalized = array_combine(array_column($customFieldsDataValues, 'name'), array_column($customFieldsDataValues, 'value'));
						?>
						<?php foreach ($content_list_fields as $content_list_field):?>
							<td dataset-name="<?php echo $content_list_field->label; ?>"><?php 
								$propname = "{$content_list_field->name}"; 
								$classname = Form::getFieldClass($content_list_field->type);
								$curfield = new $classname();
								$curfield->loadFromConfig($named_custom_fields[$propname]); // load config - useful for some fields
								$curfield->default = $customFieldsDataValuesNormalized[$propname] ?? null; // set temp field value to current stored value
								// TODO: pass precalc array of table names for content types to aid in performance of lookups 
								// some fields will currently parse json config files to determine tables to query for friendly values
								// PER row/field. not ideal.
								echo $curfield->getFriendlyValue($named_custom_fields[$propname]); // pass named field custom field config to help determine friendly value
								?></td>
						<?php endforeach; ?>
					<?php endif; ?>
			
				<td class='usage'>
				
				<?php 
				$filter_content_type_ids = Tag::get_tag_content_types($tag->id);
				/* CMS::pprint_r ($tag->filter);
				CMS::pprint_r ($filter_content_type_ids); */
				if ($tag->filter==1) {
					if ($filter_content_type_ids) {
						echo "Available for use on all content except:";
						echo "<br><strong>" . Tag::get_tag_content_type_titles($tag->id, $_SESSION["current_domain"]) . "</strong>";
					}
					else {
						echo "Available for use on <em>all</em> content.";
					}
				}
				elseif ($tag->filter==2) {
					if ($filter_content_type_ids) {
						echo "Available for use <em>only</em> on the following content:";
						echo "<br><strong>" . Tag::get_tag_content_type_titles($tag->id, $_SESSION["current_domain"]) . "</strong>";
					}
					else {
						echo "<strong>Not available for use on any current content type.</strong>";
					}
				}
				elseif ($tag->filter==0) {
					echo "<em>For admin use only</em>";
				}
				else {
					echo "<span class='is-danger'>Unknown tag filter</span>";
				}
				?>
				</td>

				<td class='note_td'><?php echo $tag->cat_title;?></td>

				<td class='unimportant'>
					<?php if ($tag->public):?>
					<i class="fas fa-eye"></i>
					<?php else: ?>
					<i class="fas fa-eye-slash"></i>
					<?php endif; ?>
				</td>
				
				<td class='note_td'><?php echo Input::stringHtmlSafe($tag->note); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".tag_admin_row");
</script>