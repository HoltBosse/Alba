<?php

Use HoltBosse\Alba\Core\{CMS, JSON, Controller, Configuration, Content, Component, Hook, Tag, User};
Use HoltBosse\DB\DB;
Use HoltBosse\Form\{Input, Form, Field};
Use HoltBosse\Form\Fields\Select\Select as Field_Select;
Use Respect\Validation\Validator as v;
Use HoltBosse\Alba\Components\Pagination\Pagination;
Use HoltBosse\Alba\Components\StateButton\StateButton;
Use HoltBosse\Alba\Components\Html\Html;
Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;

?>

<style>
	#search_form {
		div.field:has(#category), div.field:has(#creator) {
			grid-column: span 2;
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
	<form style="margin: 0;" id='orderform' action="" method="GET"></form>

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

		//important this is run after the hook for query results. we are compositing some data together here so it can be passed as a single entry to the component rendering it
		$all_content = array_map(Function($i) use ($custom_fields) {
			$i->stateComposite = [$i->id, $i->state, $i->content_type, $custom_fields->states ?? []];
			$i->aliasTitleComposite = [$i->id, $i->title, $i->alias, $i->content_type, $i];
			$i->tagComposite = Tag::get_tags_for_content($i->id, $i->content_type);
			return $i;
		}, $all_content);

		$columns = [
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"State",
				"sortable"=>false,
				"rowAttribute"=>"stateComposite",
				"rendererAttribute"=>"state",
				"renderer"=>new class extends Component {
					public array $state;

					public function display(): void {
						$stateButton = (new StateButton())->loadFromConfig((object)[
							"itemId"=>$this->state[0],
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/content/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/content/action/toggle",
							"states"=>$this->state[3],
							"contentType"=>$this->state[2]
						]);
						$stateButton->state = $this->state[1];
						$stateButton->display();
					}
				},
				"tdAttributes"=>["class"=>"drag_td state-wrapper"],
			]),
			//Id column added conditionally in table header below
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Title",
				"sortable"=>true,
				"rowAttribute"=>"aliasTitleComposite",
				"hideAttribute"=>"title",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						$contentId = $this->defaultvalue[0];
						$contentTitle = $this->defaultvalue[1];
						$contentAlias = $this->defaultvalue[2];
						$contentType = $this->defaultvalue[3];
						?>
							<div>
								<a href="<?php echo $_ENV["uripath"]; ?>/admin/content/edit/<?php echo $contentId;?>/<?php echo $contentType;?>"><?php echo Input::stringHtmlSafe($contentTitle); ?></a>
							</div>
							<span class='unimportant'>
								<?php
									echo Hook::execute_hook_filters('display_alias_override', $contentAlias, $this->defaultvalue[4]);
								?>
							</span>
						<?php
					}
				},
				"tdAttributes"=>["class"=>"title-wrapper"],
				"columnSpan"=>3
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Tags",
				"sortable"=>false,
				"rowAttribute"=>"tagComposite",
				"hideAttribute"=>"tags",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						$tags = $this->defaultvalue;
						if(sizeof($tags) > 0) { // we use css empty, so cant have blank tags inside
							echo '<div class="tags are-small are-light">';
								foreach ($tags as $tag) {
									echo '<span class="tag is-info is-light">' . Input::stringHtmlSafe($tag->title) . '</span>';
								}
							echo '</div>';
						}
					}
				},
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Category",
				"sortable"=>true,
				"rowAttribute"=>"catname",
				"hideAttribute"=>"category",
				"tdAttributes"=>["dataset-name"=>"Cat"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Start",
				"sortable"=>true,
				"rowAttribute"=>"start",
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"End",
				"sortable"=>true,
				"rowAttribute"=>"end",
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Created By",
				"sortable"=>true,
				"rowAttribute"=>"created_by",
				"rendererAttribute"=>"created_by",
				"renderer"=>new class extends Component {
					public int $created_by;

					public function display(): void {
						echo User::get_username_by_id($this->created_by);
					}
				},
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Updated By",
				"sortable"=>true,
				"rowAttribute"=>"updated_by",
				"rendererAttribute"=>"updated_by",
				"renderer"=>new class extends Component {
					public int $updated_by;

					public function display(): void {
						echo User::get_username_by_id($this->updated_by);
					}
				},
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Note",
				"sortable"=>false,
				"rowAttribute"=>"note",
				"tdAttributes"=>["class"=>"unimportant"]
			])
		];

		$listColumns = [];
		foreach ($content_list_fields as $content_list_field) {
			$listColumns[] = (new AdminTableField())->loadFromConfig((object)[
				"label"=>$content_list_field->label,
				"sortable"=>false,
				"rowAttribute"=>$content_list_field->name,
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public mixed $defaultvalue;
					public Field $formfield;
					public mixed $named_custom_fields;

					public function display(): void {
						$this->formfield->default = $this->defaultvalue;

						echo $this->formfield->getFriendlyValue($this->named_custom_fields[$this->formfield->name]); // pass named field custom field config to help determine friendly value
					}
				},
				"tdAttributes"=>["dataset-name"=>$content_list_field->label]
			]);

			$propname = "{$content_list_field->name}"; 
			$classname = Form::getFieldClass($content_list_field->type);
			$curfield = new $classname();
			$curfield->loadFromConfig($named_custom_fields[$propname]); // load config - useful for some fields

			$lastField = $listColumns[count($listColumns)-1];
			//@phpstan-ignore-next-line
			$lastField->renderer->formfield = $curfield;
			//@phpstan-ignore-next-line
			$lastField->renderer->named_custom_fields = $named_custom_fields;
		}

		array_splice($columns, 2, 0, $listColumns);

		if($_ENV["admin_show_ids_in_tables"]==="true") {
			array_splice($columns, 1, 0, [(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Id",
				"sortable"=>false,
				"rowAttribute"=>"id",
				"tdAttributes"=>["class"=>"id-wrapper"]
			])]);
		}

		$columns = array_values(array_filter($columns, Function($col) use ($hidden_list_fields) {
			return !in_array($col->hideAttribute, $hidden_list_fields);
		}));

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$all_content,
			"trClass"=>"content_admin_row",
		])->display();
	?>

<?php endif; ?>

</form>

<?php 

$num_pages = ceil($content_count/$pagination_size);

$url_query_params = $_GET;
$url_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($cur_page) {
	// not ordering view and page url is either 1 or no passed and assumed to be 1 in model
	$url_query_params['page'] = $cur_page+1;
	$next_url_params = http_build_query($url_query_params);
	$url_query_params['page'] = $cur_page-1;
	$prev_url_params = http_build_query($url_query_params);
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
</script>
