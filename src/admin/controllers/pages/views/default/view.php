<?php
	Use HoltBosse\Alba\Core\{Component, Content, Page, Template};
	Use HoltBosse\Form\{Input};
	Use HoltBosse\DB\DB;
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\CssFile\CssFile;
	Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
	Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;

	$header = "All Pages";
	$rightContent = "<a href='" . $_ENV["uripath"] . "/admin/pages/edit/0' class='button is-primary pull-right'>
			<span class='icon is-small'>
				<i class='fas fa-check'></i>
			</span>
			<span>New Page</span>
		</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"All Pages",
		"rightContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div>" . $rightContent . "</div>",
			"wrap"=>false
		])
	])->display();

	$domainLookup = DB::fetchAll("SELECT value FROM `domains`", [], ["mode"=>PDO::FETCH_COLUMN]);
?>

<form action='' method='post' name='page_action' id='page_action_form'>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
            "stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
                "id"=>"page_operations",
                "location"=>"pages",
                "buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
            ]),
            "leftContent"=>(new Html())->loadFromConfig((object)[
                "html"=>"<div></div>",
                "wrap"=>false
            ])
        ])->display();

		$all_pages = array_map(function($i) use ($all_templates) {
			$i->stateComposite = [$i->id, $i->state, NULL];
			$i->titleComposite = $i;
			$i->urlComposite = [$i->id, $i->domain];
			$i->templateComposite = [$i->template, $i->id, get_template_title($i->template, $all_templates)];
			return $i;
		}, $all_pages);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/pages/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/pages/action/toggle",
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
				"label"=>"Title",
				"sortable"=>false,
				"rowAttribute"=>"titleComposite",
				"rendererAttribute"=>"title",
				"renderer"=>new class extends Component {
					public object $title;

					public function display(): void {
						echo "<div>";
							for ($n=0; $n<$this->title->depth; $n++) {
								echo "<span class='child_indicator'>-&nbsp;</span>";
							}
							$url = $_ENV["uripath"] . "/admin/pages/edit/" . $this->title->id . "/" . $this->title->content_type . "/" . $this->title->content_view;
							echo "<a href='" . $url . "'>" . Input::stringHtmlSafe($this->title->title) . "</a>";
						echo "</div>";
						if ($this->title->content_type > 0) {
							echo "<span class='unimportant'>" . Content::get_content_type_title($this->title->content_type) ;
							echo " &raquo; ";
							echo Content::get_view_title($this->title->content_view) . "</span>";
						}
						else {
							echo "<span class='unimportant'>Widgets only</span>";
						}
					}
				},
				"tdAttributes"=>["class"=>"title-wrapper"],
				"columnSpan"=>3
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"URL",
				"sortable"=>false,
				"rowAttribute"=>"urlComposite",
				"rendererAttribute"=>"iid",
				"renderer"=>new class extends Component {
					public array $iid;

					public function display(): void {
						$pageInstance = new Page();
						$pageInstance->load_from_id($this->iid[0]);
						$url = $pageInstance->get_url();
						$displayUrl = $url;
						if($pageInstance->domain!=$_SERVER["HTTP_HOST"]) {
							$url = "https://" . DB::fetch("SELECT * FROM domains WHERE id = ?", $this->iid[1])->value . $url;
						}
						echo "<div><a style='color: var(--bulma-table-color);' target='_blank' class='unimportant' href='" . $url . "'>" . $displayUrl . "</a></div>";
					}
				},
				"tdAttributes"=>["class"=>"url-wrapper"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Template",
				"sortable"=>false,
				"rowAttribute"=>"templateComposite",
				"rendererAttribute"=>"data",
				"renderer"=>new class extends Component {
					public array $data;

					public function display(): void {
						echo "<div>";
							echo "<span class=''>" . $this->data[2] . "</span>";
							if (Page::has_overrides($this->data[1])) {
								echo "<br><span class='has-text-info widget_override_indicator'>Has Widget Overrides</span>";
							}
						echo "</div>";
					}
				},
				"tdAttributes"=>["class"=>"template-wrapper unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"ID",
				"sortable"=>false,
				"rowAttribute"=>"id",
				"rendererAttribute"=>"iid",
				"renderer"=>new class extends Component {
					public int $iid;

					public function display(): void {
						echo "<span class=''>" . $this->iid . "</span>";
					}
				},
				"tdAttributes"=>["class"=>"id-wrapper unimportant"]
			])
		];

		(new AdminTable())->loadFromConfig((object)[
			"id"=>"all_pages_table",
			"columns"=>$columns,
			"rows"=>$all_pages,
			"trClass"=>"page_admin_row",
		])->display();
	?>
</form>