<?php
defined('CMSPATH') or die; // prevent unauthorized access
?>
<h1 class='title'>
	All Images

	<!-- tag operation toolbar -->
	<div id="tag_operations" class="pull-right buttons has-addons">
		<button type="button" onclick='clear_selection()' class='button is-primary' >Select None</button>
		<button type="button" onclick='clear_tags()' class='button is-warning' >Clear Tags</button>
		<button type="button" onclick='delete_items()' class='button is-danger' >Delete</button>
	</div>
</h1>

<div id='upload_space'><h1>Drag & Drop New Images Here</h1></div>

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

<style>


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
</style>

<div id='all_images'>
	<?php foreach ($all_images as $image):?>
		<div id="media_item_id_<?php echo $image->id;?>" data-id='<?php echo $image->id;?>' class='all_images_image_container'>
			<img title="<?php echo $image->title;?>" alt="<?php echo $image->alt;?>" src="<?php echo Config::$uripath . '/image/' . $image->id;?>/thumb">
			<div class='image_info_wrap'>
				<div class='image_info'>
					<span class='bigger'><?php echo $image->title; ?></span><br><?php echo $image->alt; ?><br>
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

<script>

	// image click handler
	all_image_containers = document.querySelectorAll('.all_images_image_container');
	//console.log(all_image_containers);
	all_image_containers.forEach(container => {
		container.addEventListener('click',function(e){
			e.target.classList.toggle('active');
		});
	});

	// top tag click handlers - filter and add
	document.getElementById('top_tags').addEventListener('click',function(e){
		e.preventDefault();
		if (e.target.classList.contains('tag_filter')) {
			tag_id = e.target.closest('.tags').dataset.id;
			console.log('filtering on tag_id: ',tag_id);
			
		}
		if (e.target.classList.contains('tag_add')) {
			ids = get_selected_ids();
			tag_id = e.target.closest('.tags').dataset.id;
			tag_title = e.target.closest('.tags').dataset.title;
			if (ids.length>0) {
				// do ajax call to /admin/images/api
				// action: tag, media ids: ids, tag id: tag_id
				api_data = {"action":"tag_media","id_list":ids,"tag_id":tag_id};
				postAjax('<?php echo Config::$uripath;?>/admin/images/api', api_data, function(data){
					response = JSON.parse(data);
					response.tagged.forEach(item => {
						add_tag_to_media_item (tag_id, tag_title, item);
					});
					console.log(response); 
				});
			}
			else {
				alert('No media items selected');
			}
		}
	});

	document.getElementById('all_images').addEventListener('click',function(e){
		e.preventDefault();
		if (e.target.classList.contains('is-delete')) {
			tag_id = e.target.closest('.tags').dataset.id;
			media_id = e.target.closest('.all_images_image_container').dataset.id;
			console.log('deleting tag ',tag_id,' from image id ',media_id);
			api_data = {"action":"untag_media","id_list":[media_id],"tag_id":tag_id};
			postAjax('<?php echo Config::$uripath;?>/admin/images/api', api_data, function(data){
				response = JSON.parse(data);
				response.untagged.forEach(item => {
					untag_media_item (tag_id, item);
				});
				console.log(response); 
			});
		}
	});

	// called by 'tag_add' click handler
	function add_tag_to_media_item (tag_id, tag_title, item_id) {
		item = document.getElementById('media_item_id_' + item_id.toString());
		tags_container = item.querySelector('.image_tags');
		new_markup = `
		<div class="control">
			<div data-title="${tag_title}" data-id="${tag_id}" class="tags are-small has-addons">
				<span class="tag is-light is-info">${tag_title}</span>
				<a class="tag_add tag is-delete is-warning"></a>
			</div>
		</div>
		`;
		tags_container.innerHTML = tags_container.innerHTML + new_markup;
		//console.log('Added ' + tag_title + ' to ' + item_id);
	}

	// called by untag click handler
	function untag_media_item (tag_id, item_id) {
		item = document.getElementById('media_item_id_' + item_id.toString());
		tag = item.querySelector('.tag_id_' + tag_id.toString());
		tag.remove();
	}



	function get_selected() {
		return document.querySelectorAll('.all_images_image_container.active');
	}

	function get_selected_ids() {
		thisarray=[];
		selected = document.querySelectorAll('.all_images_image_container.active');
		selected.forEach(selimage => {
			thisarray.push(selimage.dataset.id);
		});
		return thisarray;
	}

	

	function clear_selection() {
		selected = get_selected();
		selected.forEach(i => {
			i.classList.remove('active');
		});
	}

	function postAjax(url, data, success) {
		var params = typeof data == 'string' ? data : Object.keys(data).map(
				function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
			).join('&');

		var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
		xhr.open('POST', url);
		xhr.onreadystatechange = function() {
			if (xhr.readyState>3 && xhr.status==200) { success(xhr.responseText); }
		};
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(params);
		return xhr;
	}

	function clear_tags() {
		ids = get_selected_ids();
		if (ids.length>0) {
			sure = window.confirm("Are you sure?");
			if (sure) {
				// do ajax call to /admin/images/api
				// action: tag, media ids: ids, tag id: tag_id
				api_data = {"action":"cleartags_media","id_list":ids};
				postAjax('<?php echo Config::$uripath;?>/admin/images/api', api_data, function(data){
					response = JSON.parse(data);
					response.untagged.forEach(item => {
						//clear_tags_media_item (tag_id, tag_title, item);
						media_item_container = document.getElementById('media_item_id_' + item.toString());
						media_item_container.querySelector('.image_tags').innerHTML="";
					});
					//console.log(response); 

				});
			}
		}
		else {
			alert('No images selected');
		}
	}

	function delete_items() {
		ids = get_selected_ids();
		if (ids.length>0) {
			sure = window.confirm("Are you sure?");
			if (sure) {
				// do ajax call to /admin/images/api
				// action: tag, media ids: ids, tag id: tag_id
				api_data = {"action":"delete_media","id_list":ids};
				postAjax('<?php echo Config::$uripath;?>/admin/images/api', api_data, function(data){
					response = JSON.parse(data);
					response.untagged.forEach(item => {
						//clear media_item 
						media_item_container = document.getElementById('media_item_id_' + item.toString());
						media_item_container.closest('.all_images_image_container').innerHTML="";
					});
					//console.log(response); 

				});
			}
		}
		else {
			alert('No images selected');
		}
	}


  // GET THE DROP ZONE
  var uploader = document.getElementById('upload_space');

  // STOP THE DEFAULT BROWSER ACTION FROM OPENING THE FILE
  uploader.addEventListener("dragover", function (e) {
    e.preventDefault();
	e.stopPropagation();
	e.target.classList.add('ready');
  });

  uploader.addEventListener("dragleave", function (e) {
    e.preventDefault();
	e.stopPropagation();
	e.target.classList.remove('ready');
  });

  // ADD OUR OWN UPLOAD ACTION
  uploader.addEventListener("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();

	// RUN THROUGH THE DROPPED FILES + AJAX UPLOAD
	var xhr = new XMLHttpRequest();
	var data = new FormData();
	
    for (var i = 0; i < e.dataTransfer.files.length; i++) {
		data.append('file-upload[]', e.dataTransfer.files[i]);
	}
	xhr.open('POST', '<?php echo Config::$uripath ;?>/admin/images/upload', true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
		if (xhr.status === 200) {
			// OK - Do something
			//console.logconsole.log(xhr.responseText);
			// uploaded ok - go to discover page for naming / processing
			window.location.href = '<?php echo Config::$uripath;?>/admin/images/discover';
		} else {
			// ERROR - Do something
			// console.error(xhr.statusText);
			alert("Upload error!");
		}
		}
	};
	xhr.onerror = function (e) {
		// ERROR - Do something
		// console.error(xhr.statusText);
		alert("Upload error!");
	};
	xhr.send(data);
    
  });

</script>