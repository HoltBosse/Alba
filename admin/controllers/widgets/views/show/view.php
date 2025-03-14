<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<style>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/widgets/views/show/style.css"); ?>
</style>

<form method='post'>
	<?php
		$searchForm->display_front_end();
	?>
</form>

<form action='' method='post' name='widget_action' id='widget_action_form'>

	<h1 class='title'><?php echo $widget_type_title; ?> Widgets
		<?php if ($widget_type_id):?>
			<a class='is-primary pull-right button btn' href='<?php echo Config::uripath();?>/admin/widgets/edit/new/<?php echo $widget_type_id;?>'>New &ldquo;<?php echo $widget_type_title;?>&rdquo; Widget</a>
		<?php else: ?>
			<div class='field pull-right'>
				<label class='label'>New Widget</label>
				<div class='control'>
					<div class='select'>
						<select onchange="choose_new_widget_type();" data-widget_type_id='0' id='new_widget_type_selector'>
							<option value='666'>Make selection:</option>
							<?php foreach ($all_widget_types as $widget_type):?>
							<option value='<?php echo $widget_type->id;?>'><?php echo $widget_type->title;?></option>
							<?php endforeach; ?>
						</select>
						<script>
						function choose_new_widget_type() {
							new_id = document.getElementById("new_widget_type_selector").value;
							window.location.href = "<?php echo Config::uripath();?>/admin/widgets/edit/new/" + new_id;
						}
						</script>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<?php Component::addon_button_group("widget_operations", "widgets"); ?>
	</h1>

	<table class='table'>
		<thead>
			<tr><th>State</th><th>Title</th><th>Type</th><th>Pages/Positions</th><!-- <th>Options</th> --><th>Note</th>
		</thead>
		<tbody>
		<?php foreach ($all_widgets as $widget):?>
			<tr class='widget_admin_row'>
				<td>
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $widget->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::uripath();?>/admin/widgets/action/toggle' name='id[]' value='<?php echo $widget->id; ?>'>
						<?php 
						if ($widget->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<td><a href="<?php echo Config::uripath(); ?>/admin/widgets/edit/<?php echo $widget->id;?>"><?php echo $widget->title; ?></a></td>
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

<script>
	<?php echo file_get_contents(CMSPATH . "/admin/controllers/widgets/views/show/script.js"); ?>
</script>