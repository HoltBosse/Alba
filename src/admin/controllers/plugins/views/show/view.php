<?php

	Use HoltBosse\Alba\Core\{CMS, Component, JSON, Plugin};
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\CssFile\CssFile;
	Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
	Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;
	use HoltBosse\Form\Input;

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Plugins",
	])->display();
?>
	
<form action='' method='post' name='plugin_action' id='plugin_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"plugin_operations",
                "location"=>"plugins",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();

		$all_plugins = array_map(function($i) {
			$i->stateComposite = [$i->id, $i->state, null];
			$i->titleComposite = [$i->id, $i->title];

			return $i;
		}, $all_plugins);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/plugins/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/plugins/action/toggle",
							"states"=>$this->state[2],
							"contentType"=>-1
						]);
						$stateButton->state = $this->state[1];
						$stateButton->display();
					}
				},
				"tdAttributes"=>["class"=>"drag_td state-wrapper"],
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Title",
				"sortable"=>false,
				"rowAttribute"=>"titleComposite",
				"rendererAttribute"=>"title",
				"renderer"=>new class extends Component {
					public array $title;

					public function display(): void {
						echo "<div><a href='" . $_ENV["uripath"] . "/admin/plugins/edit/" . $this->title[0] . "'>" . Input::stringHtmlSafe($this->title[1]) . "</a></div>";
					}
				},
				"columnSpan"=>3
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Description",
				"sortable"=>false,
				"rowAttribute"=>"description",
				"rendererAttribute"=>"description",
				"renderer"=>new class extends Component {
					public string $description;

					public function display(): void {
						echo "<div>" . Input::stringHtmlSafe($this->description) . "</div>";
					}
				},
				"columnSpan"=>3
			]),
		];

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$all_plugins,
			"trClass"=>"plugin_admin_row",
		])->display();
	?>
</form>