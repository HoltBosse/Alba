<?php

Use HoltBosse\Alba\Core\{CMS, Component, Hook, Page, Widget};
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

	<table class='table'>
		<thead>
			<tr><th>State</th><th>Title</th><th>Type</th><th>Pages/Positions</th><!-- <th>Options</th> --><th>Note</th>
		</thead>
		<tbody>
		<?php foreach ($all_widgets as $widget):?>
			<tr class='widget_admin_row'>
				<td>
					<?php
						(new StateButton())->loadFromConfig((object)[
							"itemId"=>$widget->id,
							"state"=>$widget->state,
							"multiStateFormAction"=>$_ENV["uripath"] . "/admin/widgets/action/togglestate",
							"dualStateFormAction"=>$_ENV["uripath"] . "/admin/widgets/action/toggle",
							"states"=>NULL,
							"contentType"=>-1
						])->display();
					?>
				</td>
				<td><a href="<?php echo $_ENV["uripath"]; ?>/admin/widgets/edit/<?php echo $widget->id;?>"><?php echo $widget->title; ?></a></td>
				<td><?php echo Widget::get_widget_type_title($widget->type); ?></td>
				<td>
				<?php 
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
				?>
				</td>
				<!-- <td><?php //CMS::pprint_r ($widget->options);?></td> -->
				<td class='note_td'><?php echo $widget->note; ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".widget_admin_row");
</script>