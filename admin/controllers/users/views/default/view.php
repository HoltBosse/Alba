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


div.pull-right {
	/* display:inline-block; */
}
#user_operations {
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
.user_admin_row.selected {
	background:rgba(200,255,200,0.3);
}
</style>

<form action='' method='post' name='user_action' id='user_action_form'>
	<h1 class='title is-1'>
		All Users
		<a href='<?php echo Config::$uripath . "/admin/users/edit"?>' class="button is-primary pull-right">
			<span class="icon is-small">
				<i class="fas fa-check"></i>
			</span>
			<span>New User</span>
		</a>
		<!-- user operation toolbar -->
		<div id="user_operations" class="pull-right buttons has-addons">
			<button formaction='<?php echo Config::$uripath;?>/admin/users/action/publish' class='button is-primary' type='submit'>Publish</button>
			<button formaction='<?php echo Config::$uripath;?>/admin/users/action/unpublish' class='button is-warning' type='submit'>Unpublish</button>
			<button formaction='<?php echo Config::$uripath;?>/admin/users/action/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
		</div>
	</h1>
	<table id='all_users_table' class="table">
		<thead>
			<th>Status</th>
			<th>Name</th>
			<th>Email</th>
			<th>Group(s)</th>
			<!-- <th>Created</th>
			<th>ID</th> -->
		</thead>
		<tbody>
			<?php foreach($all_users as $user):?>
			<?php if ($user->state<0) {continue;}?>
			<tr class='user_admin_row'>
				<td>
					<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $user->id; ?>'/>
					<button class='button' type='submit' formaction='<?php echo Config::$uripath;?>/admin/users/action/toggle' name='id[]' value='<?php echo $user->id; ?>'>
						<?php 
						if ($user->state==1) { 
							echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
						}
						else {
							echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
						} ?>
					</button>
				</td>
				<td>
					<a class='edit_user' href='<?php echo Config::$uripath;?>/admin/users/edit/<?php echo $user->id;?>'><?php echo $user->username; ?></a>
				</td>
				<td>
					<?php echo $user->email; ?>
				</td>
				<td>
					<?php echo $user->groups; ?>
				</td>
				<!-- <td>
					<?php echo $user->created; ?>
				</td>
				<td>
					<?php echo $user->id; ?>
				</td> -->
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

</form>

<script>
	admin_rows = document.querySelectorAll('.user_admin_row');
	admin_rows.forEach(row => {
		row.addEventListener('click',function(e){
			tr = e.target.closest('tr');
			tr.classList.toggle('selected');
			hidden_checkbox = tr.querySelector('.hidden_multi_edit');
			hidden_checkbox.checked = !hidden_checkbox.checked;
		});
	});
</script>