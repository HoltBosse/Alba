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
span.page_list, td.note_td, .lighter_note, .usage {
	font-size:70%; opacity:0.6;
}

div.pull-right {
	/* display:inline-block; */
}
#content_operations {
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
.content_admin_row.selected {
	background:rgba(200,255,200,0.3);
}

/* table tr.droppable {
	display:none;
}
table.dragging tr.droppable {
	display:table-row;
} */

table tr.droppable td {
	height: 0;
    padding: 0;
    border: none;
    margin: 0;
    transition: all 0.3s ease;
}
table.dragging tr.content_admin_row  {
	/* height:1rem !important; */
}
table.dragging tr.droppable td {
	height:1rem;
	background:rgba(255,255,100,0.2);
}
table.dragging tr.droppable.ready {
	height:2.2rem;
	background:rgba(155,255,100,0.3);
}
.drag_td {
	height: 0;
}
.center_state {
	height: 100%;
    display: flex;
    flex-direction: row;
    align-items: center;
}
.before_after_wrap {
	margin-right:0rem;
	transition:all 0.3s ease;
	/* margin-left:1rem; */
	/* display:none; */
	width:0;
	opacity:0;
}

.order_drop {
	color: white;
    background: #aad;
    padding: 0.3em;
    font-size: 0.7rem;
    border-radius: 0.2rem;
    text-transform: uppercase;
    position: relative;
    overflow: hidden;
    display: inline-block;
}
.order_drop:first-child {
	margin-bottom:0.5rem;
}
.order_drop.ready {
	background:#ada;
}

tr.dragging {
	opacity:0.3;
}

.grip {
	margin-right:1rem;
}
table.dragging .grip {
	/* display:none; */
	/* width:0;
	opacity:0 ; */
}
table.dragging .before_after_wrap {
	display:block;
	margin-right:1rem;
	width:auto;
	max-width:15rem;
	opacity:1;
}

/* state button css */
.state_button {
	padding: 0 !important;
}

.state_button button {
	height: 100%;
	padding: 0 0.75em;
	border: 1px solid transparent;
	width: 100%;
	background-color: #fff;
}

.state_button button:hover {
	background-color: #f5f5f5;
    cursor: pointer;
}

.state_button hr {
	width: 1px;
	height: 70%;
}

.state_button .navbar-link:not(.is-arrowless)::after {
	right: auto;
}

.state_button .navbar-link:not(.is-arrowless) {
	padding-right: 1.5em;
}

.state_button .navbar-item.has-dropdown {
	height: 100%;
}

.state_button .navbar-item {
	display: flex;
	gap: 1em;
}

@media screen and (max-width: 1024px) {
	/* disabled on mobile due to bulma lack of support */
	.state_button .navbar-item.has-dropdown, .state_button hr {
		display: none;
	}
}
</style>

<form id='searchform' action="" method="GET"></form>



<form action='' method='post' name='redirect_action' id='redirect_action_form'>
<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
<h1 class='title is-1'>Redirects
	<a class='is-primary pull-right button btn' href='<?php echo Config::uripath();?>/admin/settings/editredirect/new'>New Redirect</a>
	<!-- content operation toolbar -->
	<div id="content_operations" class="pull-right buttons has-addons">
		<button formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/publish' class='button is-primary' type='submit'>Publish</button>
		<button formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/unpublish' class='button is-warning' type='submit'>Unpublish</button>
		<button formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/duplicate' class='button is-info' type='submit'>Duplicate</button>
		<button formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
	</div>
</h1>

	<?php //CMS::pprint_r ($filters); ?>

	<div id='content_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search URL/Note</label>
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
						<?php
							foreach($custom_fields->states as $state) {
								echo "<option " . ($filters['state']==$state->state ? "selected" : false) . " value='$state->state'>" . ucwords($state->name) . "</option>";
							}
						?>
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


	
	



<?php if (!$redirects):?>
	<h2>No redirects found.</h2>
<?php else:?>

	<table class='table'>
		<thead>
			<tr>
				<th>State</th>
				<?php
					if(property_exists("Admin_Config", "show_ids_in_tables") ? Admin_Config::$show_ids_in_tables : false) {
						echo "<th>Id</th>";
					}
				?>
				<th>Old URL</th>
				<th>New URL</th>
				<th>Referer</th>
				<th>Created</th>
				<th>Created By</th>
				<th>Updated</th>
				<th>Updated By</th>
				<th>Hits</th>
				<th>Header</th>
				<th>Note</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($redirects as $content_item):?>
				<?php CMS::Instance()->listing_content_id = $content_item->id; ?>
				<tr id='row_id_<?php echo $content_item->id;?>' data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
					<div class="center_state">
						<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $content_item->id; ?>'/>
						<?php if ($order_by && $content_type_filter):?>
						<div draggable="true"  data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>"  ondragend="dragend_handler(event)" ondragstart="dragstart_handler(event)" class="grip"><i class="fas fa-grip-lines"></i></div>
						<div class='before_after_wrap'>
							<span droppable='true' class='drop_before order_drop'  ondrop="drop_handler(event)" ondragover="dragover_handler(event)" ondragleave="dragleave_handler(event)">Before</span><br>
							<span droppable='true' class='drop_after order_drop'  ondrop="drop_handler(event)" ondragover="dragover_handler(event)" ondragleave="dragleave_handler(event)">After</span>
						</div>
						<?php endif; ?>
						<div class='button state_button'>
							<button <?php if($content_item->state==0 || $content_item->state==1) { echo "type='submit' formaction='" . Config::uripath() . "/admin/settings/redirectaction/toggle' name='id[]' value='$content_item->id'"; } else { echo "style='pointer-events: none;'"; } ?>>
								<?php 
									if ($content_item->state==1) { 
										echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
									}
									elseif ($content_item->state==0) {
										echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
									} else {
										foreach($custom_fields->states as $state) {
											if($content_item->state==$state->state) {
												echo "<i style='color:$state->color' class='fas fa-times-circle' aria-hidden='true'></i>";
												$ok = true;
											}
										}
										if(!$ok) {
											echo "<i class='fas fa-times-circle' aria-hidden='true'></i>"; //default grey color if state not found
										}
									}
								?>
							</button>
							<hr>
							<div class="navbar-item has-dropdown is-hoverable">
								<a class="navbar-link"></a>
								<div class="navbar-dropdown">
									<form action='<?php echo Config::uripath();?>/admin/settings/redirectaction/togglestate' method="post">
										<input type='hidden' name='content_type' value='<?= $content_item->content_type;?>'/>
										<input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $content_item->id; ?>'/>
										<button type='submit' formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/togglestate' name='togglestate[]' value='0' class="navbar-item">
											<i class="state0 fas fa-times-circle" aria-hidden="true"></i>Unpublished
										</button>
									</form>
									<form action='<?php echo Config::uripath();?>/admin/settings/redirectaction/togglestate' method="post">
										<input type='hidden' name='content_type' value='<?= $content_item->content_type;?>'/>
										<input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $content_item->id; ?>'/>
										<button type='submit' formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/togglestate' name='togglestate[]' value='1' class="navbar-item">
											<i class="state1 is-success fas fa-times-circle" aria-hidden="true"></i>Published
										</button>
									</form>
									
									<hr class="dropdown-divider">
									<?php foreach($custom_fields->states as $state) { ?>
										<form action='<?php echo Config::uripath();?>/admin/settings/redirectaction/togglestate' method="post">
											<input type='hidden' name='content_type' value='<?= $content_item->content_type;?>'/>
											<input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $content_item->id; ?>'/>
											<button type='submit' formaction='<?php echo Config::uripath();?>/admin/settings/redirectaction/togglestate' name='togglestate[]' value='<?php echo $state->state; ?>' class="navbar-item">
												<i style="color:<?php echo $state->color; ?>" class="fas fa-times-circle" aria-hidden="true"></i><?php echo $state->name; ?>
											</button>
										</form>
									<?php } ?>
									
								</div>
							</div>
						</div>
						</div>
					</td>
					<?php
						if(property_exists("Admin_Config", "show_ids_in_tables") ? Admin_Config::$show_ids_in_tables : false) {
							echo "<td>$content_item->id</td>";
						}
					?>

					
					<td class=''><a href='<?php echo Config::uripath(); ?>/admin/settings/editredirect/<?php echo $content_item->id;?>/'><?php echo $content_item->old_url; ?></a></td>
					<td class=''><?php echo $content_item->new_url; ?></td>
					<td class='unimportant'><?php echo $content_item->referer; ?></td>
					<td class='unimportant'><?php echo $content_item->created; ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($content_item->created_by); ?></td>
					<td class='unimportant'><?php echo $content_item->updated; ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($content_item->updated_by); ?></td>
					<td class='unimportant'><?php echo $content_item->hits; ?></td>
					<td class='unimportant'><?php echo $content_item->header; ?></td>
					<td class='unimportant'><?php echo $content_item->note; ?></td>
				</tr>
				
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

</form>

<?php 
/* CMS::pprint_r ($content_count);
CMS::pprint_r ($pagination_size);
CMS::pprint_r ($order_by);
CMS::pprint_r (sizeof($all_content)); */
$num_pages = ceil($content_count/$page_size);

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

<?php if ($content_count>$page_size && !$order_by):?>
	<style>
		.small-pagination-list a {
			margin: 0;
		}
		.small-pagination-list li:not(:last-child) a {
			border-right: 0px solid transparent;
			border-top-right-radius: 0px;
			border-bottom-right-radius: 0px;
		}
		.small-pagination-list li:not(:first-child) a {
			border-top-left-radius: 0px;
			border-bottom-left-radius: 0px;
		}
		.small-pagination-list li a.is-current {
			font-weight: bold;
    		font-size: 1.075em;
		}
	</style>
	<nav class="pagination is-centered" role="navigation" aria-label="pagination">
		<ul class="pagination-list small-pagination-list">
			<?php
				$url_query_params['page'] = 1;
				$page_one_url_params = http_build_query($url_query_params);

				$url_query_params['page'] = $num_pages;
				$page_last_url_params = http_build_query($url_query_params);
			?>
			<li> 
				<a class='pagination-link' href='<?=$url_path . "?" . $page_one_url_params?>'><<</a>
			</li>
			<li> 
				<a class='pagination-link' href='<?=$url_path . "?" . ($cur_page!=1 ? $prev_url_params : $page_one_url_params)?>'><</a>
			</li>
			<?php for ($n=($cur_page-2>0 ? $cur_page-2 : 1); $n<=$num_pages && $n<=$cur_page+2; $n++):?>
				<?php 
					$url_query_params['page'] = $n;
					$url_params = http_build_query($url_query_params);
				?>
				<li> 
					<a class='pagination-link <?php if ($n==$cur_page) {echo "is-current";}?>' href='<?=$url_path . "?" . $url_params?>'><?php echo $n;?></a>
				</li>
			<?php endfor; ?>
			<li> 
				<a class='pagination-link' href='<?=$url_path . "?" . ($cur_page!=$num_pages ? $next_url_params : $page_last_url_params)?>'>></a>
			</li>
			<li> 
				<a class='pagination-link' href='<?=$url_path . "?" . $page_last_url_params?>'>>></a>
			</li>
		</ul>
	</nav>
<?php endif; ?>

<script>
	admin_rows = document.querySelectorAll('.content_admin_row');
	admin_rows.forEach(row => {
		row.addEventListener('click',function(e){
			tr = e.target.closest('tr');
			tr.classList.toggle('selected');
			hidden_checkbox = tr.querySelector('.hidden_multi_edit');
			hidden_checkbox.checked = !hidden_checkbox.checked;
		});
	});

	// ordering js

	function dragstart_handler(e) {
		//e.preventDefault();
		data = e.target.dataset.itemid;
		console.log(data);
		e.dataTransfer.dropEffect = "move";
		e.dataTransfer.setData("text/plain", data);
		e.target.closest('table').classList.add('dragging');
		e.target.closest('tr').classList.add('dragging');
		//console.log(e);
	}

	function dragover_handler(e) {
		e.preventDefault();
		e.dataTransfer.dropEffect = "move";
		e.target.classList.add('ready');
	}

	function dragleave_handler(e) {
		e.preventDefault();
		//e.dataTransfer.dropEffect = "move";
		e.target.classList.remove('ready');
	}

	function drop_handler(e) {
		e.preventDefault();
		//console.log(e);
		e.preventDefault();
		// get required info
		var source_id = e.dataTransfer.getData('text/plain');
		var dest_id = e.target.closest('tr').dataset.itemid;
		if (e.target.classList.contains('drop_before')) {
			var insert_position = 'before';
		}
		else {
			var insert_position = 'after';
		}
		//console.log('Insert',source_id, insert_position, dest_id);
		// perform ajax action silently
		api_data = {"action":"insert","sourceid":source_id,"destid":dest_id,"insert_position":insert_position,"content_type":'<?php echo $content_type_filter; ?>'};
		postAjax('<?php echo Config::uripath();?>/admin/redirects/api', api_data, function(data){
			response = JSON.parse(data);
			if (response.success=='1') {
				// do nothing - assume it worked
			}
			else {
				console.log(response); 
				alert('Ordering failed.');
			}
		});

		// move dom rows - regardless of success of ajax - report failures
		source_row = document.getElementById('row_id_' + source_id);
		dest_row = document.getElementById('row_id_' + dest_id);
		tbody = source_row.closest('tbody');
		tbody.removeChild(source_row);
		if (insert_position=='after') {
			tbody.insertAfter(source_row, dest_row);
		}
		else {
			tbody.insertBefore(source_row, dest_row);
		}
		// clean up grips - TODO: cleaner version for single grip in drop_handler
		var grips = document.querySelectorAll('.grip');
		grips.forEach(grip => {
			grip.classList.remove('ready');
		});
	}

	function dragend_handler(e) {
		e.preventDefault();
		console.log(e);
		e.target.closest('table').classList.remove('dragging');
		e.target.closest('tr').classList.remove('dragging');
	}

	// end ordering js
</script>
