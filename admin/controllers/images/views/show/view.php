<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<?php if (!$filter=='upload'):?>

<h1 class='title sticky'>
	All Images

	<!-- tag operation toolbar -->
	<div id="tag_operations" class="pull-right buttons has-addons">
		<button type="button" onclick='clear_selection()' class='button is-primary' >Select None</button>
		<button type="button" onclick='rename_image()' class='button is-info' >Edit</button>
		<button type="button" onclick='crop_image()' class='button is-info' >Crop</button>
		<button type="button" onclick='clear_tags()' class='button is-warning' >Clear Tags</button>
		<button type="button" onclick='delete_items()' class='button is-danger' >Delete</button>
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
	window.uripath = "<?php echo Config::uripath(); ?>";
</script>
<div id='upload_space'><h1>Drag & Drop New Images Here</h1></div>

<section class='section'>
	<input accept="image/*" id='regular_upload' type="file" multiple/>
</section>

<?php if (!$filter=='upload'):?>
	<p>Available Tags</p>
	<?php
	//CMS::pprint_r ($image_tags);
	?>
	<div id="top_tags" class="field is-grouped is-grouped-multiline">
		<?php foreach ($image_tags as $tag):?>
		<div class="control">
			<div data-title="<?php echo $tag->title;?>" data-id="<?php echo $tag->id;?>" class="tags has-addons">
				<a href='#' class='tag_filter tag is-link is-light is-info'><?php echo $tag->title;?></a>
				<a class="tag_add tag is-add is-primary">+</a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

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


<?php echo Image::get_uploader_zone_css(); ?>

#all_images {
	display:flex;
	flex-wrap:wrap;
}

.all_images_image_container {
	width:16vw;
	height:18vw;
	min-width:100px;
	position:relative;
	transition:all 0.3s ease;
	background-color:black;
	margin-right:1em;
	margin-bottom:1em;
}
.all_images_image_container:hover {
	cursor:pointer;
}
.all_images_image_container.active {
	background-color:#447;
	background-color:#afa;
}
.all_images_image_container.active img {
	transform:scale(0.9);
}
.all_images_image_container:hover .image_info_wrap {
	opacity:0;
	height:0;
}

.all_images_image_container img {
	width: 100%;
    height: 100%;
    object-fit: cover;
	overflow: hidden;
	transition:all 0.3s ease;
	pointer-events: none;
	object-fit:contain;
}
.all_images_image_container:hover img {
	object-fit:contain;
}

.image_info_wrap {
	transition:all 0.3s ease;
	position:absolute;
	width:100%;
	top:0;
	left:0;
	background:rgba(0,0,0,0.8);
	pointer-events: none;
}
.all_images_image_container .tag {
	font-size:0.6rem !important;
}
.image_info {
	font-size:70%;
	padding:1em;
	color:white;
}
.image_tags_wrap {
	position:absolute;
	font-size:75%;
	padding:0.5em;
	width:100%;
	bottom:0;
	left:0;
	background:rgba(255,255,255,0.7);
}
.bigger {
	font-size:120%;
}
dialog::backdrop {
	backdrop-filter:blur(5px);
	/* background: repeating-linear-gradient(
		45deg,
		rgba(0, 0, 0, 0.2),
		rgba(0, 0, 0, 0.2) 1px,
		rgba(0, 0, 0, 0.3) 1px,
		rgba(0, 0, 0, 0.3) 20px
	); */
}
</style>


<?php if (!$filter=='upload'):?>

<div id='all_images'>
	<?php foreach ($all_images as $image):?>
		<div id="media_item_id_<?php echo $image->id;?>" data-id='<?php echo $image->id;?>' class='all_images_image_container'>
			<img title="<?php echo $image->title;?>" alt="<?php echo $image->alt;?>" src="<?php echo Config::uripath() . '/image/' . $image->id;?>/thumb">
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
							<div data-title="<?php echo $tag->title;?>" data-id="<?php echo $tag->id;?>" class="tags tag_id_<?php echo $tag->id;?> are-small has-addons">
								<span class='tag is-light is-info'><?php echo $tag->title;?></span>
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
<script>
	<?php
		echo file_get_contents(CMSPATH . "/admin/controllers/images/views/show/image_upload_handling.js");
		echo file_get_contents(CMSPATH . "/admin/controllers/images/views/show/script.js");
		echo file_get_contents(CMSPATH . "/admin/controllers/images/views/show/upload_space_handling.js");
	?>
</script>