const valid_image_types = {
	"image/png": true,
	"image/webp": true,
	"image/jpeg": true,
	"image/svg+xml": true,
	"image/svg": true,
	"image/gif": true,
}

const web_friendly_blacklist = {
	"image/svg+xml": true,
	"image/svg": true,
	"image/gif": true,
}

function do_upload(e) {
	// check method
	let myfiles;
	if (e.type=="drop") {
		myfiles = e.dataTransfer.files;
	} else {
		// assume file input 
		myfiles = e.target.files;
	}
	// RUN THROUGH THE DROPPED FILES + AJAX UPLOAD
	window.formdata = new FormData();

	// check against max upload size
	let uploaded_size_total=0;
	let invalid_counter = 0;
	for (let i = 0; i < myfiles.length; i++) {
		console.log(myfiles[i].type);
		if (!valid_image_types[myfiles[i].type]) {
            // skip anything but png or jpg
			invalid_counter++;
            continue;
        }
		uploaded_size_total += myfiles[i].size;
	}
	if(invalid_counter == myfiles.length) {
		alert("Unsupported media type(s)!");
		return false;
	}
	if (uploaded_size_total > max_upload_size_bytes) {
		alert('Sorry, you must reduce the number of images or their sizes to all fit below the max upload limit shown.');
		return false;
	}

	// add files to form

	const modal = document.createElement("div");
	modal.id="upload_modal";
	modal.classList.add("modal");
	modal.classList.add('is-active');
	let markup = `
		<div class="modal-background"></div>
		<div class="modal-card">
			<header class="modal-card-head">
			<p class="modal-card-title">Upload images</p>
			<button class="delete" aria-label="close"></button>
			</header>
			<section class="modal-card-body" style='overflow: unset;'>
			<form id='image_upload_form' action='/admin/images/uploadv2' method="POST" enctype="multipart/form-data">
			</form>
			</section>
			<footer class="modal-card-foot">
			<button onclick='document.getElementById("image_upload_form_submit").click();' class="button is-success">Upload</button>
			<button class="button cancel">Cancel</button>
			</footer>
		</div>
	`;
	modal.innerHTML = markup;
	modal.querySelector("button.delete").addEventListener("click", (e)=>{
		e.target.closest("#upload_modal").remove();
	});
	document.body.appendChild(modal);

    const upload_form = modal.querySelector('form');
	// empty form except for hidden submit button - this is clicked via js from the 'upload' modal button
	// this allows browser html form checking to trigger
    upload_form.innerHTML = '<button style="display:none !important" class="button" id="image_upload_form_submit" type="submit">Upload</button>';
    for (let i = 0; i < myfiles.length; i++) {
		if (!valid_image_types[myfiles[i].type]) {
            // skip anything but png or jpg
            continue;
        }
		const id = `img_id_${i}`;
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
					<div class='field' style='display: flex;'>
						<label style='flex-shrink: 0;'>Tags</label>
						<select multiple id="upload_dialog_ss_${id}" class="slimselectme" name='itags[]'>

						</select>
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
		const reader = new FileReader();
		reader.onload = (e)=>{
            const src = e.target.result;
			document.getElementById(`img_id_${i}`).src = src;
        }
		reader.readAsDataURL(myfiles[i]);
		window.formdata.append('file-upload[]', myfiles[i]);
	}

	fetch('/image/gettags').then((response)=>response.json()).then((json)=>{
		const data = [];
		json.data.forEach((item)=>{
			data.push({text: item.text, value: item.value})
		});

		document.querySelectorAll(".upload_field .slimselectme").forEach(el=>{
			json.data.forEach((item)=>{
				const option = document.createElement("option");
				option.value = item.value;
				option.innerText = item.text;
				el.appendChild(option);
			});
			
			new SlimSelect({
				select: `#${el.id}`,
			});
		});
	}).catch(()=>{
		alert("error");
		return;
	})

	document.getElementById('image_upload_form').addEventListener('submit',function(e){
		// passed browser checks for fields (alt/title etc) - we'll check those again
		// server side
		// don't actually submit
		e.preventDefault();
		// images already present in window.formdata - add title + text
		const alt_texts_arr = document.getElementsByName('alt[]');
		const title_texts_arr = document.getElementsByName('title[]');
		const tags_values_array = document.getElementsByName('itags[]');
		console.log(tags_values_array);
		/* console.log(alt_texts_arr);
		console.log(title_texts_arr); */
		for (let i=0; i<alt_texts_arr.length; i++) {
			window.formdata.append('alt[]', alt_texts_arr[i].value);
			window.formdata.append('title[]', title_texts_arr[i].value);
			window.formdata.append('tags[]', JSON.stringify(Array.from(tags_values_array[i].selectedOptions).map(v=>v.value)));
		}
		// got all our data - hide form
		document.getElementById('image_upload_form').innerHTML = "<p>Uploading...</p>";
		document.getElementById('upload_modal').closest('.modal').classList.remove('is-active');

		/* for (var pair of window.formdata.entries()) {
			console.log(pair[0], pair[1]); 
			console.log('----');
		} */
		//return false; // early exit for testing

		const upload_dialog = document.createElement("dialog");
		upload_dialog.id = "uploading_progress_dialog";
		upload_dialog.innerHTML = `
			<section>
				<p>Uploading... Please wait</p>
			</section>
		`;
		document.body.appendChild(upload_dialog);
		upload_dialog.showModal();

		// biome-ignore lint: not solving now
		let url = window.hasOwnProperty("upload_endpoint") ? window.upload_endpoint : window.uripath + '/admin/images/uploadv2';
		fetch(url, {
			method: "POST",
			body: window.formdata,
		}).then(response=>response.json()).then((data)=>{
			// OK - Do something
			//console.logconsole.log(xhr.responseText);
			console.log(data);

			// close when done injected in view when filter=upload in place
			// biome-ignore lint: not solving now
			if (window.hasOwnProperty('close_when_done')) {
				window.close();
			// biome-ignore lint: not solving now
			} else if(window.hasOwnProperty('image_upload_el')) {
				upload_dialog.remove();

				//console.log(data);
				//console.log(document.getElementById(window.image_upload_el));

				document.getElementById(window.image_upload_el).value = data.ids.split(",")[0];
				document.getElementById(window.image_upload_el).parentElement.querySelector(".selected_image_wrap img").src = `${data.urls.split(",")[0]}/thumb`;
				document.getElementById(window.image_upload_el).parentElement.querySelector(".selected_image_wrap").classList.add("active");

				// biome-ignore lint: not solving now
				delete window.image_upload_el;
			} else {
				window.location.reload();
			}
			upload_dialog.remove();
		}).catch(()=>{
			upload_dialog.remove();
			alert("Upload error!");
		});

		modal.remove();
	});
}