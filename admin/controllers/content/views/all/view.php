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
	padding: 1em 1em;
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
<form id='orderform' action="" methpd="GET"></form>

<form action='' method='post' name='content_action' id='content_action_form'>
<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
<h1 class='title is-1'>All <?php if ($content_type_filter) { echo "&ldquo;" . Content::get_content_type_title($content_type_filter) . "&rdquo; ";}?>Content
	<?php if ($content_type_filter):?>
	<a class='is-primary pull-right button btn' href='<?php echo Config::uripath();?>/admin/content/edit/new/<?php echo $content_type_filter;?>'>New &ldquo;<?php echo Content::get_content_type_title($content_type_filter);?>&rdquo; Content</a>
	<span class='unimportant subheading'><?php $content_type_fields = Content::get_content_type_fields($content_type_filter);  echo $content_type_fields->description; ?></span>
	<?php else: ?>
		<div class='field pull-right'>
			<label class='label'>New Content</label>
			<div class='control'>
				<div class='select'>
					<select onchange="choose_new_content_type();" data-widget_type_id='0' id='new_content_type_selector'>
						<option value='666'>Make selection:</option>
						<?php foreach ($all_content_types as $content_type):?>
						<option value='<?php echo $content_type->id;?>'><?php echo $content_type->title;?></option>
						<?php endforeach; ?>
					</select>
					<script>
					function choose_new_content_type() {
						new_id = document.getElementById("new_content_type_selector").value;
						window.location.href = "<?php echo Config::uripath();?>/admin/content/edit/new/" + new_id;
					}
					</script>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php Component::addon_button_group("content_operations", "content", ["publish"=>"primary","unpublish"=>"warning","duplicate"=>"info","delete"=>"danger"]); ?>
</h1>

	<?php //CMS::pprint_r ($filters); ?>

	<div id='content_search_controls' class='flex'>

		<div class="field">
			<label class="label">Search Title/Note</label>
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

		<div class='field'>
			<label class="label">Category</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[1][key]' value='category' form='searchform'/>
					<select name="filters[1][value]" form="searchform">
						<option value=''>Select Category</option>
						<?php foreach ($applicable_categories as $cat):?>
							<option <?php if ($filters['category']==$cat->id) { echo " selected "; }?> value='<?=$cat->id?>'><?=$cat->title?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div class='field'>
			<label class="label">Creator</label>
			<div class='control'>
				<div class="select">
					<input type='hidden' name='filters[3][key]' value='created_by' form='searchform'/>
					<select name="filters[3][value]" form="searchform">
						<option value=''>Select Creator</option>
						<?php foreach ($applicable_users as $u):?>
							<option <?php if ($filters['created_by']==$u->id) { echo " selected "; }?> value='<?=$u->id?>'><?=$u->username?></option>
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

		<?php Hook::execute_hook_actions('render_custom_content_search_filters', $content_type_filter); ?>
		
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

<?php if (!$all_content):?>
	<h2>No content to show!</h2>
<?php else:?>

	<?php if ($content_type_filter):?>
		<?php if ($order_by):?>
			<a class='button is-primary is-outlined is-small' href='<?php echo $_SERVER['HTTP_REFERER'];?>'>FINISH ORDERING</a>
		<?php else: ?>
			<a class='button is-primary is-outlined is-small' href='?order_by=ordering'>MANAGE ORDERING</a>
		<?php endif; ?>
	<?php else: ?>
		<p class='help'>To manually manage ordering, please choose a specific content type from the content menu.</p>
	<?php endif; ?>

	<table class='table'>
		<thead>
			<tr>
				<th>State</th>
				<?php
					if(property_exists("Admin_Config", "show_ids_in_tables") ? Admin_Config::$show_ids_in_tables : false) {
						echo "<th>Id</th>";
					}
				?>
				<style>
					.orderablerow {
						display: flex;
						justify-content: space-between;
						align-items: center;
						gap: 1rem;
						
						& * {
							
							pointer-events: none;
						}
					}

					th:has(.orderablerow) input {
						display: none;
					}

					th:has(.orderablerow) i {
						display: none;
					}

					th:has(input[value="regular"]:checked) i.fas.fa-sort {
						display: inline-block;
					}

					th:has(input[value="asc"]:checked) i.fas.fa-sort-up {
						display: inline-block;
					}

					th:has(input[value="desc"]:checked) i.fas.fa-sort-down {
						display: inline-block;
					}
				</style>
				<?php
					if($order_by || $_GET["filters"]) {
						echo "<style>
								.orderablerow{
									pointer-events: none;

									i {
										display: none !important;
									}
								}
							</style>";
					}
				?>
				<?php make_sortable_header("title"); ?>
				<script>
					window.addEventListener("load", ()=>{
						document.addEventListener("click", (e)=>{
							if(e.target.classList.contains("orderablerow")) {
								let wrapper = e.target.closest("th");
								let selectedRadio = wrapper.querySelector("input:checked");
								//selectedRadio.checked = false;

								document.querySelectorAll('th:has(.orderablerow) input[value="regular"]').forEach(item=>{
									item.checked = true;
								});

								if(selectedRadio.nextElementSibling) {
									selectedRadio.nextElementSibling.checked = true;
								} else {
									wrapper.querySelector("input").checked = true;
								}
								
								document.getElementById(selectedRadio.getAttribute("form")).submit();
							}
						});
					});
				</script>

				<?php if ($content_list_fields):?>
					<?php foreach ($content_list_fields as $content_list_field):?>
						<th>
						<?php echo $content_list_field->label; ?>
						<?php if (is_array($custom_fields->filters) && in_array ($content_list_field->name, $custom_fields->filters)): ?>
							<br/>
							<select class='auto_filter'>
								
							</select>
						<?php endif ;?>
						</th>
					<?php endforeach; ?>
				<?php endif; ?>

				<th>Tags</th>
				<?php
					make_sortable_header("category");
					make_sortable_header("start");
					make_sortable_header("end");
					make_sortable_header("created by");
					make_sortable_header("updated by");
				?>
				<th>Note</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($all_content as $content_item):?>
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
							<button <?php if($content_item->state==0 || $content_item->state==1) { echo "type='submit' formaction='" . Config::uripath() . "/admin/content/action/toggle' name='id[]' value='$content_item->id'"; } else { echo "style='pointer-events: none;'"; } ?>>
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
									<form action='<?php echo Config::uripath();?>/admin/content/action/togglestate' method="post">
										<input type='hidden' name='content_type' value='<?= $content_item->content_type;?>'/>
										<input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $content_item->id; ?>'/>
										<button type='submit' formaction='<?php echo Config::uripath();?>/admin/content/action/togglestate' name='togglestate[]' value='0' class="navbar-item">
											<i class="state0 fas fa-times-circle" aria-hidden="true"></i>Unpublished
										</button>
									</form>
									<form action='<?php echo Config::uripath();?>/admin/content/action/togglestate' method="post">
										<input type='hidden' name='content_type' value='<?= $content_item->content_type;?>'/>
										<input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $content_item->id; ?>'/>
										<button type='submit' formaction='<?php echo Config::uripath();?>/admin/content/action/togglestate' name='togglestate[]' value='1' class="navbar-item">
											<i class="state1 is-success fas fa-times-circle" aria-hidden="true"></i>Published
										</button>
									</form>
									
									<hr class="dropdown-divider">
									<?php foreach($custom_fields->states as $state) { ?>
										<form action='<?php echo Config::uripath();?>/admin/content/action/togglestate' method="post">
											<input type='hidden' name='content_type' value='<?= $content_item->content_type;?>'/>
											<input style="display:none" checked type='checkbox' name='togglestate[]' value='<?php echo $content_item->id; ?>'/>
											<button type='submit' formaction='<?php echo Config::uripath();?>/admin/content/action/togglestate' name='togglestate[]' value='<?php echo $state->state; ?>' class="navbar-item">
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
					<td>
						<a href="<?php echo Config::uripath(); ?>/admin/content/edit/<?php echo $content_item->id;?>/<?php echo $content_item->content_type;?>"><?php echo $content_item->title; ?></a>
						<br>
						<span class='unimportant'>
							<?php
								echo Hook::execute_hook_filters('display_alias_override', $content_item->alias, $content_item);
							?>
						</span>
					</td>

					<?php if ($content_list_fields):?>
						<?php foreach ($content_list_fields as $content_list_field):?>
							<td><?php 
								$propname = "{$content_list_field->name}"; 
								$classname = "Field_" . $content_list_field->type;
								$curfield = new $classname($content_item->$propname);
								$curfield->load_from_config($named_custom_fields[$propname]); // load config - useful for some fields
								$curfield->default = $content_item->$propname; // set temp field value to current stored value
								// TODO: pass precalc array of table names for content types to aid in performance of lookups 
								// some fields will currently parse json config files to determine tables to query for friendly values
								// PER row/field. not ideal.
								echo $curfield->get_friendly_value($named_custom_fields[$propname]); // pass named field custom field config to help determine friendly value
								?></td>
						<?php endforeach; ?>
					<?php endif; ?>
					
					<td><?php 
					$tags = Tag::get_tags_for_content($content_item->id, $content_item->content_type);
					echo '<div class="tags are-small are-light">';
					foreach ($tags as $tag) {
						echo '<span class="tag is-info is-light">' . $tag->title . '</span>';
					}
					echo '</div>';
					?>
					</td>

					<td><?php echo $content_item->catname;?></td>
					<td class='unimportant'><?php echo $content_item->start; ?></td>
					<td class='unimportant'><?php echo $content_item->end; ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($content_item->created_by); ?></td>
					<td class='unimportant'><?php echo User::get_username_by_id($content_item->updated_by); ?></td>
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
$num_pages = ceil($content_count/$pagination_size);

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
	if (!$order_by) {
		Component::create_pagination($content_count, $pagination_size, $cur_page);
	}
?>

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
		postAjax('<?php echo Config::uripath();?>/admin/content/api', api_data, function(data){
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
