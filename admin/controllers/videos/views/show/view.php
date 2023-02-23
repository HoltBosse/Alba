<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>

<dialog id="video_preview" class="video_dialog">
	<video controls>
		<source src="" type="video/mp4">
	</video>
	<p onclick="document.getElementById('video_preview').close()">X</p>
</dialog>

<h1 class='title sticky'>
	All Videos

	<!-- todo: make at least deletion work -->
	<div id="tag_operations" class="pull-right buttons has-addons" style="display: none;">
		<button type="button" onclick='clear_selection()' class='button is-primary' >Select None</button>
		<button type="button" onclick='rename_image()' class='button is-info' >Edit</button>
		<button type="button" onclick='clear_tags()' class='button is-warning' >Clear Tags</button>
		<button type="button" onclick='delete_items()' class='button is-danger' >Delete</button>
	</div>
</h1>


<section>

	<form id="searchform" method="GET">
		<div id="content_search_controls" class="flex">
			<div class="field">
				<label class="label">Search Title/Description</label>
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

<style>

.video_dialog {
	max-height: 90vh;
	padding: 0;
    max-width: 75%;
    border-width: 0;
	position: relative'
}
.video_dialog p {
	position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2rem;
	cursor: pointer;
}
#upload_space {
	height:10rem;
	padding:1rem;
	margin:1rem;
	border:2px dashed rgba(0,0,0,0.1);
	display:flex;
	align-items: center;
	justify-content: center;
	transition:all 0.3s ease;
}
#upload_space h1 {
	font-size:2rem;
	opacity:0.3;
	font-weight:900;
}
#upload_space.ready {
	border:2px dashed rgba(0,0,0,0.5);
	background:#cec;
}

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

<div id='all_images'>
	<?php foreach ($all_videos->data as $image):?>
		<?php
			$uri_segments = explode("/", $image->uri);
			$id = $uri_segments[sizeof($uri_segments)-1];
		?>
		<div id="media_item_id_<?php echo $id; ?>" data-id='<?php echo $id; ?>' data-video="<?php echo $image->files[0]->link; ?>" class='all_images_image_container'>
			<img title="<?php echo $image->name; ?>" alt="<?php echo $image->name; ?>" src="<?php echo $image->pictures->sizes[round(sizeof($image->pictures->sizes)/2)]->link_with_play_button;?>">
			<div class='image_info_wrap'>
				<div class='image_info'>
					<span class='bigger imgtitle'><?php echo $image->name; ?></span><br><span class='imgalt'>
					<?php echo $image->width . "x" . $image->height; ?> 
				</div>
			</div>
			<div class='image_tags_wrap'>
				<?php $image_tags = [];//Tag::get_tags_for_content($image->id); ?>
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

<script>
	// image click handler
	all_image_containers = document.querySelectorAll('.all_images_image_container');
	//console.log(all_image_containers);
	all_image_containers.forEach(container => {
		container.addEventListener('click',function(e){
			let modal = document.getElementById("video_preview");
			modal.querySelector("source").src=e.target.dataset.video;
			modal.querySelector("video").load();
			modal.showModal();
		});
	});
</script>