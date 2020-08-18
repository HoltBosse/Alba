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
#plugin_operations {
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
.plugin_admin_row.selected {
	background:rgba(200,255,200,0.3);
}
</style>

<form action='' method='post' name='plugin_action' id='plugin_action_form'>

	<h1 class='title'>Plugins
		
		<!-- plugin operation toolbar -->
		<div id="plugin_operations" class="pull-right buttons has-addons">
			<button formaction='<?php echo Config::$uripath;?>/admin/plugins/action/publish' class='button is-primary' type='submit'>Publish</button>
			<button formaction='<?php echo Config::$uripath;?>/admin/plugins/action/unpublish' class='button is-warning' type='submit'>Unpublish</button>
			<button formaction='<?php echo Config::$uripath;?>/admin/plugins/action/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
		</div>
	</h1>

	<table class='table'>
		<thead>
			<tr><th>State</th><th>Title</th><th>Description</th><th>Version</th><th>Author</th><th>Website</th>
		</thead>
		<tbody>
		<?php foreach ($all_plugins as $a_plugin):?>
			<tr class='plugin_admin_row'>
				<td>
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $a_plugin->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::$uripath;?>/admin/plugins/action/toggle' name='id[]' value='<?php echo $a_plugin->id; ?>'>
						<?php 
						if ($a_plugin->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<td><a href="<?php echo Config::$uripath; ?>/admin/plugins/edit/<?php echo $a_plugin->id;?>"><?php echo $a_plugin->title; ?></a></td>
				<td><?php echo $a_plugin->description; ?></td>
				<td><?php echo $a_plugin->version; ?></td>
				<td><?php echo $a_plugin->author; ?></td>
				<td><a href='<?php echo $a_plugin->website; ?>'>link</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<script>
	admin_rows = document.querySelectorAll('.plugin_admin_row');
	admin_rows.forEach(row => {
		row.addEventListener('click',function(e){
			tr = e.target.closest('tr');
			tr.classList.toggle('selected');
			hidden_checkbox = tr.querySelector('.hidden_multi_edit');
			hidden_checkbox.checked = !hidden_checkbox.checked;
		});
	});
</script>