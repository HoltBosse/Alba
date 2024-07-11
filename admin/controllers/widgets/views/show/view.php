<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<style>
table.table {
	width:100%;
}
.position_single_wrap {
	font-size:70%;
	padding-top:0.3rem;
	border-top:1px solid rgba(0,0,0,0.1);
	margin-top:0.3rem;
	opacity:0.6;
}
span.position_single {
	font-weight:bold;
}
span.page_list, td.note_td, .lighter_note {
	font-size:70%; opacity:0.6;
}

div.pull-right {
	/* display:inline-block; */
}
#widget_operations {
	margin-right:2rem;
}
.state1 {
	color:#00d1b2;
}
.state0 {
	color:#f66;
}
.hidden_multi_edit {
	display:none;
	/* display:inline-block; */
}
.widget_admin_row.selected {
	background:rgba(200,255,200,0.3);
}
</style>

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
	admin_rows = document.querySelectorAll('.widget_admin_row');
	admin_rows.forEach(row => {
		row.addEventListener('click',function(e){
			tr = e.target.closest('tr');
			tr.classList.toggle('selected');
			hidden_checkbox = tr.querySelector('.hidden_multi_edit');
			hidden_checkbox.checked = !hidden_checkbox.checked;
		});
	});
</script>