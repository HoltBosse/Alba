<?php

Use HoltBosse\Alba\Core\{CMS, Tag, Component};
Use HoltBosse\Form\Input;

?>

<?php if (!$filter=='upload'):?>

<h1 class='title sticky' style="background-color: var(--bulma-body-background-color);">
	All Images

	<!-- tag operation toolbar -->
	<div id="tag_operations" class="pull-right buttons has-addons">
		<button type="button" data-clickaction='clear_selection' class='button is-primary' >Select None</button>
		<button type="button" data-clickaction='rename_image' class='button is-info' >Edit</button>
		<button type="button" data-clickaction='crop_image' class='button is-info' >Crop</button>
		<button type="button" data-clickaction='clear_tags' class='button is-warning' >Clear Tags</button>
		<button type="button" data-clickaction='delete_items' class='button is-danger' >Delete</button>
	</div>
</h1>


<section>

	<form id="searchform" method="GET">
		<div id="content_search_controls" class="flex">
			<div class="field">
				<label class="label">Search Title/Alt/Filename</label>
				<div class="control">
					<input value="<?=$searchtext?>" name="searchtext" form="searchform" class="input" type="text" placeholder="">
				</div>
			</div>
			<div class="field">
				<label class="label">&nbsp;</label>
				<div class="control">
					<button form="searchform" type="submit" class="button is-info">
						Search
					</button>
				</div>
			</div>
			<div class="field">
				<label class="label">&nbsp;</label>
				<div class="control">
					<button form="searchform" type="button" value="" onclick="window.location = window.location.href.split(&quot;?&quot;)[0]; return false;" class="button is-default">
						Clear
					</button>
				</div>
			</div>
		</div>
	</form>
</section>

<?php else :?>
	<script>window.close_when_done = true;</script>
	<style>nav {display:none !important;}</style>
	<h1 class='title sticky'>Upload</h1>
<?php endif; ?>


<p class='help'>Max upload total size: <?php echo $max_upload_size; ?> (<?php echo $max_upload_size_bytes; ?> bytes)</p>
<script>
	window.max_upload_size_bytes = <?php echo $max_upload_size_bytes;?>;
	window.uripath = "<?php echo $_ENV["uripath"]; ?>";
</script>
<div id='upload_space'><h1>Drag & Drop New Images Here</h1></div>

<section class='section'>
	<input accept="image/*" id='regular_upload' type="file" multiple/>
</section>

<?php if (!$filter=='upload'):?>
	<div class="tags_menu_options">
		<p>Available Tags</p>
		<?php
		//CMS::pprint_r ($image_tags);
		?>
		<div id="top_tags" class="field is-grouped is-grouped-multiline">
			<?php foreach ($image_tags as $tag):?>
			<div class="control">
				<div data-title="<?php echo Input::stringHtmlSafe($tag->title);?>" data-id="<?php echo $tag->id;?>" class="tags has-addons">
					<a href='#' class='tag_filter tag is-link is-light is-info'><?php echo Input::stringHtmlSafe($tag->title);?></a>
					<a class="tag_add tag is-add is-primary">+</a>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<br>

	<div id='rename_image_modal' class="modal">
	<div class="modal-background"></div>
	<div class="modal-content">
		<form class='form'>
			<input type='hidden' value='' id='rename_image_id' name='rename_image_id'/>
			<div class='field'>
				<label class='label'  for='rename_title'>Title</label>
				<div class='control'>
					<input placeholder='Title Text' class='input' name='rename_title' id='rename_title' type='text' required/>
				</div>
			</div>
			<div class='field'>
				<label class='label' for='rename_alt'>Alt Text</label>
				<div class='control'>
					<input placeholder='Alt Text' class='input' name='rename_alt' id='rename_alt' type='text' required/>
				</div>
			</div>
			<div class='field'>
				<button id='update_image_values_trigger' onclick="rename_image_action();" type='button' class='btn button is-success'>Update</button>
				<button id='close_image_modal' onclick='this.closest(".modal").classList.remove("is-active");' type='button' class='btn button is-danger'>Cancel</button>
			</div>
		</form>
	</div>
	<button class="modal-close is-large" aria-label="close"></button>
	</div>

<?php endif; ?>

<style>
	<?php echo file_get_contents(__DIR__ . "/style.css"); ?>
</style>


<?php if (!$filter=='upload'):?>

<div id='all_images'>
	<?php foreach ($all_images as $image):?>
		<div id="media_item_id_<?php echo $image->id;?>" data-id='<?php echo $image->id;?>' class='all_images_image_container'>
			<img title="<?php echo $image->title;?>" alt="<?php echo $image->alt;?>" src="<?php echo $_ENV["uripath"] . '/image/' . $image->id;?>/thumb">
			<div class='image_info_wrap'>
				<div class='image_info'>
					<span class='bigger imgtitle'><?php echo $image->title; ?></span><br><span class='imgalt'><?php echo $image->alt; ?></span><br>
					<?php echo $image->width . "x" . $image->height; ?> / <?php echo $image->mimetype; ?> 
				</div>
			</div>
			<div class='image_tags_wrap'>
				<?php $image_tags = Tag::get_tags_for_content($image->id); ?>
				<div class="image_tags field is-grouped is-grouped-multiline">
					<?php foreach ($image_tags as $tag):?>
						<div class="control">
							<div data-title="<?php echo Input::stringHtmlSafe($tag->title);?>" data-id="<?php echo $tag->id;?>" class="tags tag_id_<?php echo $tag->id;?> are-small has-addons">
								<span class='tag is-light is-info'><?php echo Input::stringHtmlSafe($tag->title);?></span>
								<a class="remove_tag tag_add tag is-delete is-warning"></a>
							</div>
						</div>
					<?php endforeach; ?>
				
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<?php Component::create_pagination($images_count, $pagination_size, $cur_page); ?>

<?php endif; // skipped display of all images if filter==upload ?>

<?php if ($autoclose):?>
	<script>window.autoclose = true;</script>
<?php else: ?>
	<script>window.autoclose = false;</script>
<?php endif; ?>
<?php
	$cropperCss = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.css"/>';
	if(!in_array($cropperCss, CMS::Instance()->head_entries)) {
		CMS::Instance()->head_entries[] = $cropperCss;
	}
?>
<script type="module">
	import {initGraphicalUploaderEventListeners, initInputFileUploaderEventListeners} from "/js/image_uploading.js?v=1";
	import {loadImgEditor} from "/js/image_editing.js";

	window.load_img_editor = loadImgEditor;
	<?php
		echo file_get_contents(__DIR__ . "/script.js");
	?>

	initGraphicalUploaderEventListeners("#upload_space");
	initInputFileUploaderEventListeners("#regular_upload");
</script>