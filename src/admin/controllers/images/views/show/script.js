// image click handler
document.querySelectorAll('.all_images_image_container').forEach(container => {
	container.addEventListener('click',(e)=> {
		e.target.classList.toggle('active');
	});
});

// close modal handler
document.querySelector('body').addEventListener('click',(e)=> {
	if (e.target.classList.contains('delete')) {
		e.target.closest('.modal').classList.remove('is-active');
	}
});


// top tag click handlers - filter and add
document.getElementById('top_tags')?.addEventListener('click',(e)=> {
	e.preventDefault();
	if (e.target.classList.contains('tag_filter')) {
		tag_id = e.target.closest('.tags').dataset.id;
		console.log('filtering on tag_id: ',tag_id);
		
	}
	if (e.target.classList.contains('tag_add')) {
		const ids = get_selected_ids();
		const tag_id = e.target.closest('.tags').dataset.id;
		const tag_title = e.target.closest('.tags').querySelector("a.tag_filter").innerHTML;
		if (ids.length>0) {
			// do ajax call to /admin/images/api
			// action: tag, media ids: ids, tag id: tag_id
			const api_data = {"action":"tag_media","id_list":ids,"tag_id":tag_id};
			const formData = new FormData();
			for (const [key, value] of Object.entries(api_data)) {
				formData.append(key, value);
			}

			fetch(`${window.uripath}/admin/images/api`, {
				method: "POST",
				body: formData,
			}).then(response=>response.json()).then((response)=>{
				console.log(response);
				response.tagged.forEach(item => {
					add_tag_to_media_item (tag_id, tag_title, item);
				});
				console.log(response); 
			}).catch((e)=>{
				console.log(e);
				console.log("error");
			});
		}
		else {
			alert('No media items selected');
		}
	}
});

const all_images = document.getElementById('all_images');
// only applicable if filter not in place to hide all images
if (all_images) {
	all_images.addEventListener('click',(e)=> {
		e.preventDefault();
		if (e.target.classList.contains('is-delete')) {
			const tag_id = e.target.closest('.tags').dataset.id;
			const media_id = e.target.closest('.all_images_image_container').dataset.id;
			console.log('deleting tag ',tag_id,' from image id ',media_id);
			const api_data = {"action":"untag_media","id_list":[media_id],"tag_id":tag_id};
			const formData = new FormData();
			for (const [key, value] of Object.entries(api_data)) {
				formData.append(key, value);
			}

			fetch(`${window.uripath}/admin/images/api`, {
				method: "POST",
				body: formData,
			}).then(response=>response.json()).then((response)=>{
				response.untagged.forEach(item => {
					untag_media_item (tag_id, item);
				});
				console.log(response);
			}).catch((e)=>{
				console.log(e);
				console.log("error");
			});
		}
	});
}

// called by 'tag_add' click handler
function add_tag_to_media_item (tag_id, tag_title, item_id) {
	console.log(tag_title);
	const item = document.getElementById(`media_item_id_${item_id.toString()}`);
	const tags_container = item.querySelector('.image_tags');
	const new_markup = `
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
	const item = document.getElementById(`media_item_id_${item_id.toString()}`);
	const tag = item.querySelector(`.tag_id_${tag_id.toString()}`);
	tag.remove();
}



function get_selected() {
	return document.querySelectorAll('.all_images_image_container.active');
}

function get_selected_ids() {
	const thisarray=[];
	const selected = document.querySelectorAll('.all_images_image_container.active');
	selected.forEach(selimage => {
		thisarray.push(selimage.dataset.id);
	});
	return thisarray;
}

function rename_image() {
	const selected = get_selected();
	if (selected.length<1) {
		alert('Select an image');
	}
	else if (selected.length>1) {
		alert('Select a single image');
	}
	else {
		// get vars
		const title = selected[0].querySelector('img').title;
		const alt = selected[0].querySelector('img').alt;
		const image_id = selected[0].dataset.id;
		// show modal
		const modal = document.getElementById('rename_image_modal');
		modal.querySelector('#rename_title').value = title;
		modal.querySelector('#rename_alt').value = alt;
		modal.querySelector('#rename_image_id').value = image_id;
		modal.classList.add('is-active');
	}
}

function crop_image() {
	const selected = get_selected();
	if (selected.length<1) {
		alert('Select an image');
	}
	else if (selected.length>1) {
		alert('Select a single image');
	}
	else {
		// get vars
		async function handle_img_editor() {
			const title = selected[0].querySelector('img').title;
			const alt = selected[0].querySelector('img').alt;
			const image_id = selected[0].dataset.id;
			const result = await window.load_img_editor(image_id);
			//console.log(result);

			if(result != 0) {
				document.getElementById("image_editor").querySelector(".modal-card-body").innerHTML = "<p>Uploading Edit to the Server. Please Wait ....</p>";
				document.getElementById("image_editor").querySelector(".modal-card-foot").innerHTML = "";
				//console.log(result);
				const formData = new FormData();
				formData.append("file-upload[]", result);
				formData.append("alt[]", [title]);
				formData.append("title[]", [alt]);

				fetch(`${window.uripath}/admin/images/uploadv2`, {
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
	const selected = get_selected();
	// get vars
	const modal = document.getElementById('rename_image_modal');
	const title = modal.querySelector('#rename_title').value;
	const alt = modal.querySelector('#rename_alt').value ;
	const image_id = modal.querySelector('#rename_image_id').value;
	// update image in view
	selected[0].querySelector('img').title = title;
	selected[0].querySelector('img').alt = alt;
	selected[0].querySelector('span.imgtitle').innerText = title;
	selected[0].querySelector('span.imgalt').innerText = alt;
	// hide modal early
	modal.classList.remove('is-active');
	// save data
	const api_data = {"action":"rename_image","title":title,"alt":alt,"image_id":image_id};
	const formData = new FormData();
	for (const [key, value] of Object.entries(api_data)) {
		formData.append(key, value);
	}

	fetch(`${window.uripath}/admin/images/api`, {
		method: "POST",
		body: formData,
	}).then(response=>response.json()).then((response)=>{
		console.log(response); // todo: handle errors
	}).catch(()=>{
		console.log("Error");
	});
}

document.querySelector("#update_image_values_trigger").addEventListener("click", ()=>{
	rename_image_action();
});

function clear_selection() {
	const selected = get_selected();
	selected.forEach(i => {
		i.classList.remove('active');
	});
}

function clear_tags() {
	const ids = get_selected_ids();
	if (ids.length>0) {
		const sure = window.confirm("Are you sure?");
		if (sure) {
			// do ajax call to /admin/images/api
			// action: tag, media ids: ids, tag id: tag_id
			const api_data = {"action":"cleartags_media","id_list":ids};
			const formData = new FormData();
			for (const [key, value] of Object.entries(api_data)) {
				formData.append(key, value);
			}

			fetch(`${window.uripath}/admin/images/api`, {
				method: "POST",
				body: formData,
			}).then(response=>response.json()).then((response)=>{
				response.untagged.forEach(item => {
					//clear_tags_media_item (tag_id, tag_title, item);
					const media_item_container = document.getElementById(`media_item_id_${item.toString()}`);
					media_item_container.querySelector('.image_tags').innerHTML="";
				});
			}).catch(()=>{
				console.log("Error");
			});
		}
	}
	else {
		alert('No images selected');
	}
}

function delete_items() {
	const ids = get_selected_ids();
	if (ids.length>0) {
		const sure = window.confirm("Are you sure?");
		if (sure) {
			// do ajax call to /admin/images/api
			// action: tag, media ids: ids, tag id: tag_id
			const api_data = {"action":"delete_media","id_list":ids};
			const formData = new FormData();
			for (const [key, value] of Object.entries(api_data)) {
				formData.append(key, value);
			}

			fetch(`${window.uripath}/admin/images/api`, {
				method: "POST",
				body: formData,
			}).then(response=>response.json()).then((response)=>{
				response.untagged.forEach(item => {
					//clear media_item 
					const media_item_container = document.getElementById(`media_item_id_${item.toString()}`);
					media_item_container.closest('.all_images_image_container').innerHTML="";
				});
				window.location.reload();
			}).catch(()=>{
				console.log("error");
			});
		}
	}
	else {
		alert('No images selected');
	}
}

document.querySelector("[data-clickaction='clear_selection']").addEventListener("click", ()=>{
	clear_selection();
});
document.querySelector("[data-clickaction='rename_image']").addEventListener("click", ()=>{
	rename_image();
});
document.querySelector("[data-clickaction='crop_image']").addEventListener("click", ()=>{
	crop_image();
});
document.querySelector("[data-clickaction='clear_tags']").addEventListener("click", ()=>{
	clear_tags();
});
document.querySelector("[data-clickaction='delete_items']").addEventListener("click", ()=>{
	delete_items();
});

window.addEventListener('load',()=> {
	document.body.addEventListener('click',(e)=> {
		if (e.target.classList.contains('state_indicator')) {
			const submit_url = e.target.dataset.submiturl;
			// do fetch to toggle state
			fetch(submit_url, {
				method: "POST",
			}).then(response=>response.json()).then((response)=>{
				console.log(response);
				// update icon
				if (response.new_state==1) {
					e.target.innerHTML = '<i class="fa-solid fa-eye"></i>';
				} else if(response.new_state==0) {
					e.target.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
				}
			}).catch((e)=>{
				console.log(e);
				console.log("error");
			});
		}
	});
});

