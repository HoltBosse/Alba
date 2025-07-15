<?php
	Use HoltBosse\Alba\Core\{Content, Component, CMS};
	Use HoltBosse\Form\Input;
?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>

<form id='searchform' action="" method="GET"></form>

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
					<?php foreach ($all_content_types as $content_type):?>
						<option value='<?php echo $content_type->id;?>'><?php echo $content_type->title;?></option>
					<?php endforeach; ?>
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
	$header = "All Categories";
	Component::addon_page_title($header, null, $rightContent);
?>

	<div class="field has-addons">
		<div class="control">
			<input value="<?php echo $search; ?>" name="search" form="searchform" class="input" type="text" placeholder="Search title/note">
		</div>
		<div class="control">
			<button form="searchform" type="submit" class="button is-info">
			Search
			</button>
		</div>
		<div class="control">
			<button form="searchform" type="button" value="" onclick="window.location = window.location.href.split(&quot;?&quot;)[0]; return false;" class="button is-default">
				Clear
			</button>
		</div>
	</div>

<form action='' method='post' name='content_action' id='content_action_form'>
<input type='hidden' name='content_type' value='<?=$content_type_filter;?>'/>
<?php
	$addonButtonGroupArgs = ["content_operations", "categories"];
	Component::addon_button_toolbar($addonButtonGroupArgs);
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
				<?php if ($search) {
					if (stripos($content_item->title,$search)===false) {
						// skip, nothing matching 
						continue;
					}
				}
				?>
				<?php CMS::Instance()->listing_content_id = $content_item->id; ?>
				<tr id='row_id_<?php echo $content_item->id;?>' data-itemid="<?php echo $content_item->id;?>" data-ordering="<?php echo $content_item->ordering;?>" class='content_admin_row'>
					<td class='drag_td'>
						<?php
							Component::state_toggle($content_item->id, $content_item->state, "categories", NULL, -1);
						?>
					</td>
					<td>
						<?php $title_prefix="";
						for ($n=0; $n<$content_item->depth; $n++) {
							$title_prefix .= "&nbsp;-&nbsp;";
						}?>
						<a href="<?php echo $_ENV["uripath"]; ?>/admin/categories/edit/<?php echo $content_item->id;?>"><?php echo $title_prefix . Input::stringHtmlSafe($content_item->title); ?></a>
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

<script type="module">
	import {handleAdminRows} from "/js/admin_row.js";
	handleAdminRows(".content_admin_row");
</script>