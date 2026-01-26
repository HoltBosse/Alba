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

	$rightContent = "<a class='is-primary pull-right button btn' href='" . $_ENV["uripath"] . "/admin/redirects/edit/new'>New Redirect</a>";

	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"Redirects",
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

<form style="margin: 0;" id='orderform' action="" method="GET"></form>

<form action='' method='post' name='redirect_action' id='redirect_action_form'>

<?php
	(new AdminButtonToolBar())->loadFromConfig((object)[
		"stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
			"id"=>"content_operations",
			"location"=>"redirects",
			"buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
		]),
		"leftContent"=>(new Html())->loadFromConfig((object)[
			"html"=>"<div></div>",
			"wrap"=>false
		])
	])->display();
?>

<?php if (!$redirects):?>
	<h2>No redirects found.</h2>
<?php else:?>

	<?php
		$redirects = array_map(function($r){
			$r->stateComposite = [$r->id, $r->state];
			$r->sourceComposite = [$r->id, $r->old_url];
			return $r;
		}, $redirects);

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
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/redirects/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/redirects/action/toggle",
							"states"=>NULL,
							"contentType"=>-1
						]);
						$stateButton->state = $this->state[1];
						$stateButton->display();
					}
				},
				"tdAttributes"=>["class"=>"drag_td state-wrapper"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Source",
				"sortable"=>false,
				"rowAttribute"=>"sourceComposite",
				"rendererAttribute"=>"defaultvalue",
				"renderer"=>new class extends Component {
					public array $defaultvalue;

					public function display(): void {
						$id = $this->defaultvalue[0];
						$url = $this->defaultvalue[1];
						?>
							<div>
								<a href="<?php echo $_ENV["uripath"]; ?>/admin/redirects/edit/<?php echo $id; ?>/"><?php echo Input::stringHtmlSafe($url); ?></a>
							</div>
						<?php
					}
				},
				"tdAttributes"=>["class"=>"limitwidth title-wrapper"],
				"columnSpan"=>3
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Destination",
				"sortable"=>false,
				"rowAttribute"=>"new_url",
				"tdAttributes"=>["class"=>"limitwidth"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Referer",
				"sortable"=>false,
				"rowAttribute"=>"referer",
				"tdAttributes"=>["class"=>"limitwidth unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Created",
				"sortable"=>false,
				"rowAttribute"=>"created",
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Hits",
				"sortable"=>true,
				"rowAttribute"=>"hits",
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Header",
				"sortable"=>false,
				"rowAttribute"=>"header",
				"tdAttributes"=>["class"=>"unimportant"]
			]),
			(new AdminTableField())->loadFromConfig((object)[
				"label"=>"Note",
				"sortable"=>false,
				"rowAttribute"=>"note",
				"tdAttributes"=>["class"=>"unimportant"]
			])
		];

		(new AdminTable())->loadFromConfig((object)[
			"columns"=>$columns,
			"rows"=>$redirects,
			"trClass"=>"redirect_admin_row",
		])->display();
	?>

<?php endif; ?>

</form>

<?php 

$num_pages = ceil($redirect_count/$page_size);

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
		"itemCount"=>$redirect_count,
		"itemsPerPage"=>$page_size,
		"currentPage"=>$cur_page
	])->display();
?>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>
