<?php

	Use HoltBosse\Alba\Core\{CMS, Component, Hook, User, Tag, Form};
	Use HoltBosse\Form\Fields\Select\Select as Field_Select;
	Use HoltBosse\Form\{Field, Input};
	Use HoltBosse\Alba\Components\Pagination\Pagination;
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
	Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;

	$rightContent = "<a href='" . $_ENV["uripath"] . "/admin/users/edit' class='button is-primary'>
		<span class='icon is-small'>
			<i class='fas fa-check'></i>
		</span>
		<span>New User</span>
	</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Users: " . $group_name,
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

<form action='' method='post' name='user_action' id='user_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"user_operations",
                "location"=>"users",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();

		$statesForToggle = $states;
		if(!is_array($statesForToggle)) {
			$statesForToggle = [];
		}
		$statesForToggle = array_merge([(object) ["state"=>2,"name"=>"Published - Pwd Reset Req", "color"=>"lime"]], $statesForToggle);

		$all_users = array_map(Function($i) use ($statesForToggle) {
			$i->stateComposite = [$i->id, $i->state, $statesForToggle];
			$i->usernameComposite = [$i->id, $i->username];
			$i->groupComposite = User::get_all_groups_for_user($i->id);
			$i->tagComposite = Tag::get_tags_for_content($i->id, -2);
			return $i;
		}, $all_users);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/users/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/users/action/toggle",
							"states"=>$this->state[2],
							"contentType"=>-1
						]);
						$stateButton->state = $this->state[1];
						$stateButton->display();
					}
				},
				"tdAttributes"=>["class"=>"drag_td state-wrapper"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Name",
				"sortable"=>false,
				"rowAttribute"=>"usernameComposite",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						?>
							<div>
								<a class='edit_user' href='<?php echo $_ENV["uripath"];?>/admin/users/edit/<?php echo $this->defaultvalue[0];?>'><?php echo Input::stringHtmlSafe($this->defaultvalue[1]); ?></a>
							</div>
						<?php
					}
				},
				"columnSpan"=>3,
				"tdAttributes"=>["class"=>"title-wrapper"],
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Email",
				"sortable"=>false,
				"rowAttribute"=>"email",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public string $defaultvalue;

					public function display(): void {
						echo Input::stringHtmlSafe($this->defaultvalue);
					}
				},
				"columnSpan"=>2,
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Group(s)",
				"sortable"=>false,
				"rowAttribute"=>"groupComposite",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						if(sizeof($this->defaultvalue) > 0) { // we use css empty, so cant have blank tags inside
							echo '<div class="tags are-small are-light">';
								foreach ($this->defaultvalue as $group) {
									echo '<span class="tag is-info is-light">' . $group->display . '</span>';
								}
							echo '</div>';
						}
					}
				},
				"columnSpan"=>2,
				"tdAttributes"=>["dataset-name"=>"Grp(s)"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Tags",
				"sortable"=>false,
				"rowAttribute"=>"tagComposite",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						if(sizeof($this->defaultvalue) > 0) { // we use css empty, so cant have blank tags inside
							echo '<div class="tags are-small are-light">';
								foreach ($this->defaultvalue as $tag) {
									echo '<span class="tag is-info is-light">' . $tag->title . '</span>';
								}
							echo '</div>';
						}
					}
				},
				"columnSpan"=>2,
			]),
		];

		$listColumns = [];
		if ($content_list_fields) {
			foreach ($content_list_fields as $content_list_field) {
				$listColumns[] = (new AdminTableField())->loadFromConfig((object)[
					"label"=>$content_list_field->label,
					"sortable"=>false,
					"rowAttribute"=>"id",
					"rendererAttribute"=>"defaultvalue",
					"renderer"=>new class extends Component {
						public mixed $defaultvalue;
						public Field $formfield;
						public mixed $named_custom_fields;
						public mixed $customUserFieldsLookup;

						public function display(): void {
							$this->formfield->default = $this->customUserFieldsLookup[$this->defaultvalue]->{$this->formfield->name}; // set temp field value to current stored value

							echo $this->formfield->getFriendlyValue($this->named_custom_fields[$this->formfield->name]); // pass named field custom field config to help determine friendly value
						}
					},
					"columnSpan"=>2,
				]);

				$named_custom_fields = array_column(json_decode(file_get_contents($_ENV["custom_user_fields_file_path"]))->fields, null, 'name'); 

				$propname = "{$content_list_field->name}"; 
				$classname = Form::getFieldClass($content_list_field->type);
				$curfield = new $classname();
				$curfield->loadFromConfig($named_custom_fields[$propname]); // load config - useful for some fields

				$lastField = $listColumns[count($listColumns)-1];
				//@phpstan-ignore-next-line
				$lastField->renderer->formfield = $curfield;
				//@phpstan-ignore-next-line
				$lastField->renderer->named_custom_fields = $named_custom_fields;
				//@phpstan-ignore-next-line
				$lastField->renderer->customUserFieldsLookup = $customUserFieldsLookup;
			}
		}
		array_splice($columns, 3, 0, $listColumns);

		if($_ENV["admin_show_ids_in_tables"]==="true") {
			array_splice($columns, 1, 0, [(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Id",
				"sortable"=>false,
				"rowAttribute"=>"id",
				"tdAttributes"=>["class"=>"id-wrapper"]
			])]);
		}

		//todo: hide fields

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$all_users,
			"trClass"=>"user_admin_row",
		])->display();
	?>

</form>

<?php
	(new Pagination())->loadFromConfig((object)[
		"id"=>"pagination_component",
		"itemCount"=>$user_count,
		"itemsPerPage"=>$pagination_size,
		"currentPage"=>$cur_page
	])->display();
?>