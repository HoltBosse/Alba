<?php
	Use HoltBosse\Alba\Core\{Content, Component, CMS};
	Use HoltBosse\Form\Input;
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
	<table class='table'>
		<thead>
			<tr>
				<th>State</th><th>Title</th>

				<?php if (!$content_type_filter):?><th>Type</th><?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($all_categories as $content_item):?>
				<tr id='row_id_<?php echo $content_item->id;?>' data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
						<?php
							(new StateButton())->loadFromConfig((object)[
								"itemId"=>$content_item->id,
								"state"=>$content_item->state,
								"multiStateFormAction"=>$_ENV["uripath"] . "/admin/categories/action/togglestate",
								"dualStateFormAction"=>$_ENV["uripath"] . "/admin/categories/action/toggle",
								"states"=>NULL,
								"contentType"=>-1
							])->display();
						?>
					</td>
					<td>
						<?php $title_prefix="";
						for ($n=0; $n<$content_item->depth; $n++) {
							$title_prefix .= "&nbsp;-&nbsp;";
						}?>
						<a href="<?php echo $_ENV["uripath"]; ?>/admin/categories/edit/<?php echo $content_item->id;?>"><?php echo $title_prefix . Input::stringHtmlSafe($content_item->title); ?></a>
					</td>

			

					<?php if (!$content_type_filter):?>
						<td><?php echo Content::get_content_type_title($content_item->content_type); ?></td>
					<?php endif; ?>
					
				</tr>
				
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

</form>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>