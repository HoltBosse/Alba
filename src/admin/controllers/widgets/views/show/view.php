<?php

	Use HoltBosse\Alba\Core\{CMS, Component, Hook, Page, Widget};
	Use HoltBosse\Form\Input;
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
	Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;
	Use HoltBosse\Alba\Components\CssFile\CssFile;

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

	ob_start();
	if ($widget_type_id) {
?>
	<a class='is-primary pull-right button btn' href='<?php echo $_ENV["uripath"];?>/admin/widgets/edit/new/<?php echo $widget_type_id;?>'>New &ldquo;<?php echo $widget_type_title;?>&rdquo; Widget</a>
<?php } else { ?>
	<div class='field pull-right'>
		<label class='label'>New Widget</label>
		<div class='control'>
			<div class='select'>
				<select onchange="choose_new_widget_type();" data-widget_type_id='0' id='new_widget_type_selector'>
					<option value='666'>Make selection:</option>
					<?php 
						foreach ($all_widget_types as $widget_type) {
							if(!Widget::isAccessibleOnDomain($widget_type->id, $_SESSION["current_domain"])) {
								continue;
							}
							?>
								<option value='<?php echo $widget_type->id;?>'><?php echo $widget_type->title;?></option>
							<?php
						}	
					?>
				</select>
				<script>
					function choose_new_widget_type() {
						new_id = document.getElementById("new_widget_type_selector").value;
						window.location.href = "<?php echo $_ENV["uripath"];?>/admin/widgets/edit/new/" + new_id;
					}
				</script>
			</div>
		</div>
	</div>
<?php
	}
	$rightContent = ob_get_clean();

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Widgets",
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

<form action='' method='post' name='widget_action' id='widget_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"widget_operations",
				"location"=>"widgets",
				"buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();
	?>

	<?php
		$all_widgets = array_map(function($w) {
			$w->stateComposite = [$w->id, $w->state, -1, []];
			$w->titleComposite = [$w->id, $w->title];
			$w->pagesComposite = $w;
			return $w;
		}, $all_widgets);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/widgets/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/widgets/action/toggle",
							"states"=>NULL,
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
						?>
							<a href="<?php echo $_ENV["uripath"]; ?>/admin/widgets/edit/<?php echo $contentId;?>"><?php echo Input::stringHtmlSafe($contentTitle); ?></a>
						<?php
					}
				},
				"columnSpan"=>5,
				"tdAttributes"=>["class"=>"title-wrapper"],
			]),

			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Type",
				"sortable"=>false,
				"rowAttribute"=>"type",
				"rendererAttribute"=>"type",
				"renderer"=>new class extends Component {
					public int $type;

					public function display(): void {
						echo Widget::get_widget_type_title($this->type);
					}
				},
				"columnSpan"=>2,
			]),

			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Pages/Positions",
				"sortable"=>false,
				"rowAttribute"=>"pagesComposite",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public object $defaultvalue;

					public function display(): void {
						echo "<div>";
							$widget = $this->defaultvalue;
							if ($widget->position_control==0) {$position_control_description="Only On Marked Pages";} 
							elseif ($widget->position_control==1) {$position_control_description="On All Pages Except Marked";}
							elseif ($widget->position_control==2) {$position_control_description="Controlled by Pages";}
							else {$position_control_description="Unknown position control";}
							echo "<span class='position_control'>{$position_control_description}</span>";
							if ($widget->page_list && $widget->position_control!=2) {
								echo "<br><span class='page_list'>";
								$page_list_pages = Page::get_pages_from_id_array (explode(',',$widget->page_list));
								$comma="";
								foreach ($page_list_pages as $page_list_page) {
									echo $comma . $page_list_page->title ;
									$comma = ", ";
								}
								echo "</span>";
							}
							if ($widget->position_control!=2) {
								echo "<br><div class='position_single_wrap'>Position: <span class='position_single'>" . $widget->global_position . "</span></div>";
							}
						echo "</div>";
					}
				},
				"columnSpan"=>2,
				"tdAttributes"=>["dataset-name"=>"Pos"]
			]),

			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Note",
				"sortable"=>false,
				"rowAttribute"=>"note",
				"tdAttributes"=>["class"=>"note_td"],
				"columnSpan"=>2,
			])
		];

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$all_widgets,
			"trClass"=>"widget_admin_row",
		])->display();

	?>
</form>