<?php

	Use HoltBosse\Alba\Core\{CMS, User, Component};
	Use HoltBosse\Form\Input;
	Use HoltBosse\DB\DB;
	Use HoltBosse\Alba\Components\Pagination\Pagination;
	Use HoltBosse\Alba\Components\StateButton\StateButton;
	Use HoltBosse\Alba\Components\Html\Html;
	Use HoltBosse\Alba\Components\TitleHeader\TitleHeader;
	Use HoltBosse\Alba\Components\Admin\StateButtonGroup\StateButtonGroup as AdminStateButtonGroup;
	Use HoltBosse\Alba\Components\Admin\ButtonToolBar\ButtonToolBar as AdminButtonToolBar;
	Use HoltBosse\Alba\Components\CssFile\CssFile;
	Use HoltBosse\Alba\Components\Admin\Table\Table as AdminTable;
	Use HoltBosse\Alba\Components\Admin\Table\TableField as AdminTableField;

	(new CssFile())->loadFromConfig((object)[
		"filePath"=>__DIR__ . "/style.css",
	])->display();

	$rightContent = "<a class='is-primary pull-right button btn' href='" . $_ENV["uripath"] . "/admin/forms/edit/new'>New Form</a>";
	
	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Forms",
		"rightContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div>" . $rightContent . "</div>",
			"wrap"=>false
		])
	])->display();
?>

<form action='' method='post' name='form_action' id='form_action_form'>

<?php
	(new AdminButtonToolBar())->loadFromConfig((object)[
		"stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
			"id"=>"content_operations",
			"location"=>"forms",
			"buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
		]),
		"leftContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div></div>",
			"wrap"=>false
		])
	])->display();
?>

<?php if (!$forms):?>
	<h2>No forms found.</h2>
<?php else:?>

	<?php
		// compose composite fields for table rendering
		$forms = array_map(function($item) {
			$item->stateComposite = [$item->id, $item->state, -1, NULL];
			$item->titleComposite = [$item->id, $item->title, $item->alias, $item->content_type, $item];
			return $item;
		}, $forms);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/forms/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/forms/action/toggle",
							"states"=>NULL,
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
								<a href="<?php echo $_ENV["uripath"]; ?>/admin/forms/edit/<?php echo $contentId;?>/<?php echo $contentType;?>"><?php echo Input::stringHtmlSafe($contentTitle); ?></a>
							</div>
							<span class='unimportant'>
								<?php echo $contentAlias; ?>
							</span>
						<?php
					}
				},
				"tdAttributes"=>["class"=>"title-wrapper"],
				"columnSpan"=>3,
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Created By",
				"sortable"=>false,
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
				"sortable"=>false,
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
		];

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$forms,
			"trClass"=>"form_admin_row",
		])->display();
	?>

<?php endif; ?>

</form>

<?php 

$num_pages = ceil($formsCount/$pageSize);

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
		"itemCount"=>$formCount,
		"itemsPerPage"=>$pageSize,
		"currentPage"=>$cur_page
	])->display();
?>
