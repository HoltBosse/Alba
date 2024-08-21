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

</style>

<form id='searchform' action="" method="GET"></form>

<form action='' method='post' name='content_action' id='content_action_form'>
<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
<h1 class='title is-1'>All <?php if ($content_type_filter) { echo "&ldquo;" . Content::get_content_type_title($content_type_filter) . "&rdquo; ";}?>Categories
	<?php if ($content_type_filter):?>
	<a class='is-primary pull-right button btn' href='<?php echo Config::uripath();?>/admin/categories/edit/new/<?php echo $content_type_filter;?>'>New &ldquo;<?php echo Content::get_content_type_title($content_type_filter);?>&rdquo; Category</a>
	
	<?php else: ?>
		<div class='field pull-right'>
			<label class='label'>New Category</label>
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
						window.location.href = "<?php echo Config::uripath();?>/admin/categories/edit/new/" + new_id;
					}
					</script>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<!-- content operation toolbar -->
	<div id="content_operations" class="pull-right buttons has-addons">
		<button formaction='<?php echo Config::uripath();?>/admin/categories/action/publish' class='button is-primary' type='submit'>Publish</button>
		<button formaction='<?php echo Config::uripath();?>/admin/categories/action/unpublish' class='button is-warning' type='submit'>Unpublish</button>
		<button formaction='<?php echo Config::uripath();?>/admin/categories/action/delete' onclick='return window.confirm("Are you sure?")' class='button is-danger' type='submit'>Delete</button>
	</div>
</h1>

	<div class="field has-addons">
		<div class="control">
			<input value="<?php echo $search; ?>" name="search" form="searchform" class="input" type="text" placeholder="Search title/note">
		</div>
		<div class="control">
			<button form="searchform" type="submit" class="button is-info">
			Search
			</button>
		</div>
	</div>



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
				<?php CMS::Instance()->listing_content_id = $content_item->id; ?>
				<tr id='row_id_<?php echo $content_item->id;?>' data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
					<div class="center_state">
						<input class='hidden_multi_edit' type='checkbox' name='id[]' value='<?php echo $content_item->id; ?>'/>
						<button class='button' type='submit' formaction='<?php echo Config::uripath();?>/admin/categories/action/toggle' name='id[]' value='<?php echo $content_item->id; ?>'>
							<?php
							if ($content_item->state==1) {
								echo '<i class="state1 is-success fas fa-check-circle" aria-hidden="true"></i>';
							}
							else {
								echo '<i class="state0 fas fa-times-circle" aria-hidden="true"></i>';
							} ?>
						</button>
						</div>
					</td>
					<td>
						<?php $title_prefix="";
						for ($n=0; $n<$content_item->depth; $n++) {
							$title_prefix .= "&nbsp;-&nbsp;";
						}?>
						<a href="<?php echo Config::uripath(); ?>/admin/categories/edit/<?php echo $content_item->id;?>"><?php echo $title_prefix . $content_item->title; ?></a>
						<br><span class='unimportant'><?php echo $content_item->alias; ?></span>
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