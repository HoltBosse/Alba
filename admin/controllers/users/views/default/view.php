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

<form id='searchform' action="" method="GET"></form>

<form action='' method='post' name='user_action' id='user_action_form'>
	<h1 class='title is-1'>
		Users: <?php echo $group_name; ?>
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

	<div id='content_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search Name/Email</label>
			<div class="control">
				<input value="<?php echo $search; ?>" name="search" form="searchform" class="input" type="text" placeholder="">
			</div>
		</div>

		<div class='field'>
			<label class="label">State</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[2][key]' value='state' form='searchform'/>
					<select name="filters[2][value]" form="searchform">
						<option value=''>State</option>
						<option <?php if ($filters['state']==='1') { echo " selected "; }?> value='1'>Published</option>
						<option <?php if ($filters['state']==='0') { echo " selected "; }?> value='0'>Unpublished</option>
						<option <?php if ($filters['state']==='-1') { echo " selected "; }?> value='-1'>Deleted</option>
					</select>
				</div>
			</div>
		</div>

		

		<div class='field'>
			<label class="label">Group</label>
			<div class='control'>
				<div class="select">
					<select  name="groups[]" form="searchform">
						<option value=''>Select Group</option>
						<?php foreach ($all_groups as $g):?>
							<option <?php if (in_array($g->id,$groups)) { echo " selected "; }?> value='<?=$g->id?>'><?=$g->display?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class='field' id='content_search_tags_wrap'>
			<label class="label">Tagged</label>
			<div class='control'>
				<div class="select">
					<select id="content_search_tags" name="coretags[]" form="searchform" multiple>
						<?php foreach ($applicable_tags as $t):?>
							<option <?php if (in_array($t->id, $coretags)) { echo " selected "; }?> value='<?=$t->id?>'><?=$t->title?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<script>
		new SlimSelect({
			select:'#content_search_tags'
		});
		</script>
		
		<div class='field'>
			<label class="label">&nbsp;</label>
			<div class='control'>
				<button form="searchform" type="submit" class="button is-info">
					Search
				</button>
			</div>
		</div>

		<div class='field'>
			<label class="label">&nbsp;</label>
			<div class='control'>
				<button form="searchform" type="button" value="" onclick='window.location = window.location.href.split("?")[0]; return false;' class="button is-default">
					Clear
				</button>
			</div>
		</div>
	</div>

	<table id='all_users_table' class="table">
		<thead>
			<th>Status</th>
			<th>Name</th>
			<th>Email</th>
			<?php if (!$group_id):?><th>Group(s)</th><?php endif; ?>
			<th>Tags</th>
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
				<?php if (!$group_id):?>
					<td>
						<?php 
						$groups = User::get_all_groups_for_user($user->id);
						echo '<div class="tags are-small are-light">';
						foreach ($groups as $group) {
							echo '<span class="tag is-info is-light">' . $group->display . '</span>';
						}
						echo '</div>';
						?>
					</td>
				<?php endif; ?>
				<td><?php 
					$tags = Tag::get_tags_for_content($user->id, -2);
					echo '<div class="tags are-small are-light">';
					foreach ($tags as $tag) {
						echo '<span class="tag is-info is-light">' . $tag->title . '</span>';
					}
					echo '</div>';
					?>
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

<?php 

$num_pages = ceil($user_count/$pagination_size);

//$url_query_params = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$url_query_params = $_GET;
$url_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($cur_page) {
	// not ordering view and page url is either 1 or no passed and assumed to be 1 in model
	$url_query_params['page'] = $cur_page+1;
	$next_url_params = http_build_query($url_query_params);
	$url_query_params['page'] = $cur_page-1;
	$prev_url_params = http_build_query($url_query_params);
}
?>

<?php if ($user_count>$pagination_size && !$order_by):?>
<nav class="pagination is-centered" role="navigation" aria-label="pagination">
	<?php if ($cur_page>1):?>
		<a href='<?=$url_path . "?" . $prev_url_params;?>' class="pagination-previous">Previous</a>
	<?php endif;?>
	<?php if ( ($user_count>sizeof($all_content)) && !$order_by && ( ($cur_page*$pagination_size)<$user_count ) ):?>
		<a href='<?=$url_path . "?" . $next_url_params;?>' class="pagination-next">Next page</a>
	<?php endif; ?>
	<ul class="pagination-list">
		<?php for ($n=1; $n<=$num_pages; $n++):?>
			<?php 
			$url_query_params['page'] = $n;
			$url_params = http_build_query($url_query_params);
			?>
		<li> 
			<a class='pagination-link <?php if ($n==$cur_page) {echo "is-current";}?>' href='<?=$url_path . "?" . $url_params?>'><?php echo $n;?></a>
		</li>
		<?php endfor; ?>
	</ul>
</nav>
<?php endif; ?>

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