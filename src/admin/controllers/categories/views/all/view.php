<?php
	Use HoltBosse\Alba\Core\{Content, Component, CMS};
	Use HoltBosse\Form\Input;
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

	ob_start();
	if ($content_type_filter) {
?>
	<a class='is-primary pull-right button btn' href='<?php echo $_ENV["uripath"];?>/admin/categories/edit/new/<?php echo $content_type_filter;?>'>New &ldquo;<?php echo Content::get_content_type_title($content_type_filter);?>&rdquo; Category</a>
<?php
	} else {
?>
	<div class='field pull-right'>
		<label class='label'>New Category</label>
		<div class='control'>
			<div class='select'>
				<select onchange="choose_new_content_type();" data-widget_type_id='0' id='new_content_type_selector'>
					<option value='666'>Make selection:</option>
					<?php
						foreach ($all_content_types as $content_type) {
							if(Content::isAccessibleOnDomain($content_type->id, $_SESSION["current_domain"])==false) {
								continue;
							}
							?>
								<option value='<?php echo $content_type->id;?>'><?php echo $content_type->title;?></option>
							<?php
						}
					?>
				</select>
				<script>
					function choose_new_content_type() {
						new_id = document.getElementById("new_content_type_selector").value;
						window.location.href = "<?php echo $_ENV["uripath"];?>/admin/categories/edit/new/" + new_id;
					}
				</script>
			</div>
		</div>
	</div>
<?php
	}
	$rightContent = ob_get_clean();
	(new TitleHeader())->loadFromConfig((object)[
		"header"=>"All Categories",
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

<form action='' method='post' name='content_action' id='content_action_form'>
	<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
	<?php
		(new AdminButtonToolBar())->loadFromConfig((object)[
			"stateButtonGroup"=>(new AdminStateButtonGroup())->loadFromConfig((object)[
				"id"=>"content_operations",
				"location"=>"categories",
				"buttons"=>["publish"=>"primary","unpublish"=>"warning","delete"=>"danger"]
			]),
			"leftContent"=>(new Html())->loadFromConfig((object)[
				"html"=>"<div></div>",
				"wrap"=>false
			])
		])->display();
	?>

	<?php if (!$all_categories):?>
		<h2>No categories to show!</h2>
	<?php else:?>
		<?php
			$all_categories = array_map(function($i) {
				$i->stateComposite = [$i->id, $i->state, null];
				$i->titleComposite = [$i->id, $i->title, $i->depth];

				return $i;
			}, $all_categories);

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
								"multiStateFormAction"=>$_ENV["uripath"] . "/admin/categories/action/togglestate",
								"dualStateFormAction"=>$_ENV["uripath"] . "/admin/categories/action/toggle",
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
							echo "<div>";
								$title_prefix="";
								for ($n=0; $n<$this->title[2]; $n++) {
									$title_prefix .= "&nbsp;-&nbsp;";
								}
								?>
									<a href="<?php echo $_ENV["uripath"]; ?>/admin/categories/edit/<?php echo $this->title[0];?>"><?php echo $title_prefix . Input::stringHtmlSafe($this->title[1]); ?></a>
								<?php
							echo "</div>";
						}
					},
					"tdAttributes"=>["class"=>"title-wrapper"],
					"columnSpan"=>8
				]),
				(new AdminTableField())->loadFromConfig((object)[
					"label"=>"Type",
					"sortable"=>false,
					"rowAttribute"=>"content_type",
					"rendererAttribute"=>"content_type",
					"renderer"=>new class extends Component {
						public int $content_type;

						public function display(): void {
							echo Content::get_content_type_title($this->content_type);
						}
					},
				]),
			];	

			(new AdminTable())->loadFromConfig((object)[
				"columns"=>$columns,
				"rows"=>$all_categories,
				"trClass"=>"category_admin_row",
			])->display();
		?>
	<?php endif; ?>

</form>