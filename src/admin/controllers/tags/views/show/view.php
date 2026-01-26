<?php

	Use HoltBosse\Alba\Core\{Component, Tag, CMS};
	Use HoltBosse\Form\{Input, Form, Field};
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
	Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

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

	<?php
		$customStates = [];
		if (isset($_ENV["tag_custom_fields_file_path"])) {
			$customFieldsFormObject = json_decode(file_get_contents($_ENV["tag_custom_fields_file_path"]));
			if(isset($customFieldsFormObject->states)) {
				$customStates = $customFieldsFormObject->states;
			}
		}

		// prepare tag rows: expose custom field values as properties and composites used by renderers
		$all_tags = array_map(function($tag) use ($customStates) {
			$customFieldsDataValues = $tag->custom_fields!="" ? json_decode($tag->custom_fields ?? "[]") : [];
			$customFieldsDataValuesNormalized = [];
			if (count($customFieldsDataValues)) {
				$customFieldsDataValuesNormalized = array_combine(array_column($customFieldsDataValues, 'name'), array_column($customFieldsDataValues, 'value')) ?: [];
			}
			foreach ($customFieldsDataValuesNormalized as $k => $v) {
				$tag->{$k} = $v;
			}

			$tag->stateComposite = [$tag->id, $tag->state, $customStates, -1];
			$tag->titleComposite = [$tag->id, $tag->title, $tag->depth, $tag];
			$tag->usageComposite = $tag; // pass whole tag to renderer
			return $tag;
		}, $all_tags);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/tags/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/tags/action/toggle",
							"states"=>$this->state[2],
							"contentType"=>-1
						]);
						$stateButton->state = $this->state[1];
						$stateButton->display();
					}
				},
				"tdAttributes"=>["class"=>"state-wrapper"],
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Title",
				"sortable"=>false,
				"rowAttribute"=>"titleComposite",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						$contentId = $this->defaultvalue[0];
						$contentTitle = $this->defaultvalue[1];
						$depth = $this->defaultvalue[2];
						$tagObj = $this->defaultvalue[3];
						for ($n=0; $n<$depth; $n++) {
							echo "&nbsp&#x21B3;&nbsp";
						}
						echo '<div><a href="' . $_ENV["uripath"] . '/admin/tags/edit/' . $contentId . '">' . Input::stringHtmlSafe($contentTitle) . '</a></div>';
					}
				},
				"tdAttributes"=>["class"=>"title-wrapper"],
				"columnSpan"=>3,
			]),
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

						echo $this->formfield->getFriendlyValue($this->named_custom_fields[$this->formfield->name]);
					}
				},
				"tdAttributes"=>["dataset-name"=>$content_list_field->label]
			]);

			$propname = "{$content_list_field->name}"; 
			$classname = Form::getFieldClass($content_list_field->type);
			$curfield = new $classname();
			$curfield->loadFromConfig($named_custom_fields[$propname]);

			$lastField = $listColumns[count($listColumns)-1];
			//@phpstan-ignore-next-line
			$lastField->renderer->formfield = $curfield;
			//@phpstan-ignore-next-line
			$lastField->renderer->named_custom_fields = $named_custom_fields;
		}

		array_splice($columns, 2, 0, $listColumns);

		$columns[] = (new AdminTableField())->loadFromConfig((object)[
			"label"=>"Available To Use On",
			"sortable"=>false,
			"rowAttribute"=>"usageComposite",
			"rendererAttribute"=>"defaultvalue",
			"renderer"=>new class extends Component {
				public mixed $defaultvalue;

				public function display(): void {
					echo "<div>";
						$tag = $this->defaultvalue;
						$filter_content_type_ids = Tag::get_tag_content_types($tag->id);
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
					echo "</div>";
				}
			},
			"tdAttributes"=>["class"=>"unimportant"],
		]);

		$columns[] = (new AdminTableField())->loadFromConfig((object)[
			"label"=>"Category",
			"sortable"=>false,
			"rowAttribute"=>"cat_title",
			"tdAttributes"=>["class"=>"note_td"]
		]);

		$columns[] = (new AdminTableField())->loadFromConfig((object)[
			"label"=>"Front-End",
			"sortable"=>false,
			"rowAttribute"=>"public",
			"rendererAttribute"=>"public",
			"renderer"=>new class extends Component {
				public int $public;

				public function display(): void {
					if ($this->public) echo '<i class="fas fa-eye"></i>';
					else echo '<i class="fas fa-eye-slash"></i>';
				}
			},
			"tdAttributes"=>["class"=>"unimportant"]
		]);

		$columns[] = (new AdminTableField())->loadFromConfig((object)[
			"label"=>"Note",
			"sortable"=>false,
			"rowAttribute"=>"note",
			"tdAttributes"=>["class"=>"note_td"]
		]);

		// filter out any hidden fields
		$columns = array_values(array_filter($columns, function($col) use ($hidden_list_fields) {
			return !in_array($col->hideAttribute ?? null, $hidden_list_fields ?? []);
		}));

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$all_tags,
			"trClass"=>"tag_admin_row",
		])->display();
	?>
</form>