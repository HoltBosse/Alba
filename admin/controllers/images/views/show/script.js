var uploading_progress_dialog = document.getElementById('uploading_progress_dialog');

	var valid_image_types = {
		"image/png": true,
		"image/webp": true,
		"image/jpeg": true,
		"image/svg+xml": true,
		"image/svg": true,
		"image/gif": true,
	}

	var web_friendly_blacklist = {
		"image/svg+xml": true,
		"image/svg": true,
		"image/gif": true,
	}

	// image click handler
	all_image_containers = document.querySelectorAll('.all_images_image_container');
	//console.log(all_image_containers);
	all_image_containers.forEach(container => {
		container.addEventListener('click',function(e){
			e.target.classList.toggle('active');
		});
	});

	// close modal handler
	document.querySelector('body').addEventListener('click',function(e){
		if (e.target.classList.contains('delete')) {
			e.target.closest('.modal').classList.remove('is-active');
		}
	});


	// top tag click handlers - filter and add
	document.getElementById('top_tags')?.addEventListener('click',function(e){
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
				postAjax(window.uripath + '/admin/images/api', api_data, function(data){
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

	var all_images = document.getElementById('all_images');
	// only applicable if filter not in place to hide all images
	if (all_images) {
		all_images.addEventListener('click',function(e){
			e.preventDefault();
			if (e.target.classList.contains('is-delete')) {
				tag_id = e.target.closest('.tags').dataset.id;
				media_id = e.target.closest('.all_images_image_container').dataset.id;
				console.log('deleting tag ',tag_id,' from image id ',media_id);
				api_data = {"action":"untag_media","id_list":[media_id],"tag_id":tag_id};
				postAjax(window.uripath + '/admin/images/api', api_data, function(data){
					response = JSON.parse(data);
					response.untagged.forEach(item => {
						untag_media_item (tag_id, item);
					});
					console.log(response); 
				});
			}
		});
	}

	// called by 'tag_add' click handler
	function add_tag_to_media_item (tag_id, tag_title, item_id) {
		item = document.getElementById('media_item_id_' + item_id.toString());
		tags_container = item.querySelector('.image_tags');
		new_markup = `
		<div class="control">
			<div data-title="${tag_title}" data-id="${tag_id}" class="tag_id_${tag_id} tags are-small has-addons">
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

	function rename_image() {
		selected = get_selected();
		if (selected.length<1) {
			alert('Select an image');
		}
		else if (selected.length>1) {
			alert('Select a single image');
		}
		else {
			// get vars
			var title = selected[0].querySelector('img').title;
			var alt = selected[0].querySelector('img').alt;
			var image_id = selected[0].dataset.id;
			// show modal
			var modal = document.getElementById('rename_image_modal');
			modal.querySelector('#rename_title').value = title;
			modal.querySelector('#rename_alt').value = alt;
			modal.querySelector('#rename_image_id').value = image_id;
			modal.classList.add('is-active');
		}
	}

	function crop_image() {
		selected = get_selected();
		if (selected.length<1) {
			alert('Select an image');
		}
		else if (selected.length>1) {
			alert('Select a single image');
		}
		else {
			// get vars
			async function handle_img_editor() {
				let title = selected[0].querySelector('img').title;
				let alt = selected[0].querySelector('img').alt;
				let image_id = selected[0].dataset.id;
				const result = await window.load_img_editor(image_id);
				//console.log(result);

				if(result != 0) {
					document.getElementById("image_editor").querySelector(".modal-card-body").innerHTML = `<p>Uploading Edit to the Server. Please Wait ....</p>`;
					document.getElementById("image_editor").querySelector(".modal-card-foot").innerHTML = "";
					//console.log(result);
					const formData = new FormData();
					formData.append("file-upload[]", result);
					formData.append("alt[]", [title]);
					formData.append("title[]", [alt]);
					formData.append("web_friendly[]", [0]);

					fetch(window.uripath + '/admin/images/uploadv2', {
						method: "POST",
						/* headers: {
							"Content-Type": "multipart/form-data"
						}, */
						body: formData,
					}).then((response) => response.json()).then((data) => {
						console.log(data);
						window.location.reload();
					});
				}
			}

			handle_img_editor();
		}
	}

	function rename_image_action() {
		// called by onclick of update button in modal
		selected = get_selected();
		// get vars
		var modal = document.getElementById('rename_image_modal');
		var title = modal.querySelector('#rename_title').value;
		var alt = modal.querySelector('#rename_alt').value ;
		var image_id = modal.querySelector('#rename_image_id').value;
		// update image in view
		selected[0].querySelector('img').title = title;
		selected[0].querySelector('img').alt = alt;
		selected[0].querySelector('span.imgtitle').innerText = title;
		selected[0].querySelector('span.imgalt').innerText = alt;
		// hide modal early
		modal.classList.remove('is-active');
		// save data
		api_data = {"action":"rename_image","title":title,"alt":alt,"image_id":image_id};
		postAjax(window.uripath + '/admin/images/api', api_data, function(data){
			response = JSON.parse(data);
			console.log(response); // todo: handle errors
		});
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
				postAjax(window.uripath + '/admin/images/api', api_data, function(data){
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
				postAjax(window.uripath + '/admin/images/api', api_data, function(data){
					response = JSON.parse(data);
					response.untagged.forEach(item => {
						//clear media_item 
						media_item_container = document.getElementById('media_item_id_' + item.toString());
						media_item_container.closest('.all_images_image_container').innerHTML="";
					});
					window.location.reload();
					//console.log(response); 

				});
			}
		}
		else {
			alert('No images selected');
		}
	}

	// handle image upload submit
	document.getElementById('image_upload_form').addEventListener('submit',function(e){
		var xhr = new XMLHttpRequest();
		// passed browser checks for fields (alt/title etc) - we'll check those again
		// server side
		// don't actually submit
		e.preventDefault();
		// images already present in window.formdata - add title + text
		let alt_texts_arr = document.getElementsByName('alt[]');
		let title_texts_arr = document.getElementsByName('title[]');
		/* console.log(alt_texts_arr);
		console.log(title_texts_arr); */
		for (var i=0; i<alt_texts_arr.length; i++) {
			window.formdata.append('alt[]', alt_texts_arr[i].value);
			window.formdata.append('title[]', title_texts_arr[i].value);
		}
		// got all our data - hide form
		document.getElementById('image_upload_form').innerHTML = "<p>Uploading...</p>";
		document.getElementById('upload_modal').closest('.modal').classList.remove('is-active');
		uploading_progress_dialog.showModal();

		for (var pair of window.formdata.entries()) {
			console.log(pair[0], pair[1]); 
			console.log('----');
		}
		//return false; // early exit for testing

		// send xhr data
		xhr.open('POST', window.uripath + '/admin/images/uploadv2', true);
		xhr.onload = function (e) {
			if (xhr.readyState === 4) {
				if (xhr.status === 200) {
					// OK - Do something
					//console.logconsole.log(xhr.responseText);

					// close when done injected in view when filter=upload in place
					if (window.hasOwnProperty('close_when_done')) {
						window.close();
					}
					else {
						window.location.reload();
					}
				} else {
					// ERROR - Do something
					// console.error(xhr.statusText);
					alert("Upload error!");
				}
				uploading_progress_dialog.close();
			}
		};
		xhr.onerror = function (e) {
			// ERROR - Do something
			// console.error(xhr.statusText);
			uploading_progress_dialog.close();
			alert("Upload error!");
		};
		xhr.send(window.formdata);
	});

  // UPLOAD
  function do_upload(e) {
	// check method
	if (e.type=="drop") {
		var myfiles = e.dataTransfer.files;
	}
	else {
		// assume file input 
		var myfiles = e.target.files;
	}
	// RUN THROUGH THE DROPPED FILES + AJAX UPLOAD
	window.formdata = new FormData();

	// check against max upload size
	var uploaded_size_total=0;
	for (var i = 0; i < myfiles.length; i++) {
		if (!valid_image_types[myfiles[i].type]) {
            // skip anything but png or jpg
            continue;
        }
		uploaded_size_total += myfiles[i].size;
	}
	if (uploaded_size_total > max_upload_size_bytes) {
		alert('Sorry, you must reduce the number of images or their sizes to all fit below the max upload limit shown.');
		return false;
	}

	// show upload modal
	var modal = document.getElementById('upload_modal');  
	var html = document.querySelector('html');
	modal.classList.add('is-active');
	html.classList.add('is-clipped');
	modal.querySelector('.modal-background').addEventListener('click', function(e) {
		e.preventDefault();
		modal.classList.remove('is-active');
		html.classList.remove('is-clipped');
	});
	modal.querySelector('button.cancel').addEventListener('click', function(e) {
		e.preventDefault();
		modal.classList.remove('is-active');
		html.classList.remove('is-clipped');
	});

	// add files to form
    let upload_form = modal.querySelector('form');
	// empty form except for hidden submit button - this is clicked via js from the 'upload' modal button
	// this allows browser html form checking to trigger
    upload_form.innerHTML = '<button style="display:none !important" class="button" id="image_upload_form_submit" type="submit">Upload</button>';
    for (let i = 0; i < myfiles.length; i++) {
		if (!valid_image_types[myfiles[i].type]) {
            // skip anything but png or jpg
            continue;
        }
		let id = "img_id_" + i;
		markup = `
            <div class='upload_field'>
                <div class='upload_preview'>
                    <img id='${id}' src=''>
                </div>
                <div class='upload_details'>
                    <div class='field'>
                        <label>Title</label>
                        <input name='title[]' required/>
                    </div>
                    <div class='field'>
                        <label>Alt</label>
                        <input name='alt[]' required/>
                    </div>
                    <div class='field' style='${(web_friendly_blacklist[myfiles[i].type] ? "display:none;" : "")}'>
                        <label>Web Friendly</label>
                        <select name='web_friendly[]'>
                            <option selected value='1'>Yes</option>
                            <option value='0'>No</option>
                        </select>
                        <p class='help'>Resize large images for web</p>
                    </div>
                </div>
            </div>
            `;
        upload_form.innerHTML = upload_form.innerHTML + markup;
	}
	for (let i = 0; i < myfiles.length; i++) {
		var reader = new FileReader();
		reader.onload = function(e) {
            let src = e.target.result;
			document.getElementById("img_id_"+i).src = src;
        }
		reader.readAsDataURL(myfiles[i]);
		window.formdata.append('file-upload[]', myfiles[i]);
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
	do_upload(e);
  });

  // handle regular file upload

let regular_upload = document.getElementById('regular_upload');
regular_upload.addEventListener('change',function(e){
	do_upload(e);
});

