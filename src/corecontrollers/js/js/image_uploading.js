import SlimSelect from 'https://cdnjs.cloudflare.com/ajax/libs/slim-select/2.12.0/slimselect.es.js'; //WHEN UPDATING, CHANGE CSS AS WELL

import { aiTextGenerator } from './ai_text_generator.js';
let aiAvailable = false;

// Initialize AI when the module loads
(async () => {
    try {
        aiAvailable = await aiTextGenerator.initialize();
        if (aiAvailable) {
            const status = aiTextGenerator.getAvailability();
            console.log('✨ AI text generation is available');
            console.log('   Status:', status);
            
            if (status === 'readily') {
                console.log('   ✅ Model ready to use immediately');
            } else if (status === 'after-download') {
                console.log('   ⏳ Model will download on first use (~1.5GB)');
            }
            else if (status === 'downloadable') {
                console.log('   ⏳ Model will MAYBE download on first use (~1.5GB)');
            }
        } else {
            console.log('ℹ️ AI not available');
            console.log('   Enable at: chrome://flags/#prompt-api-for-gemini-nano');
            console.log('   And: chrome://flags/#optimization-guide-on-device-model');
        }
    } catch (error) {
        console.error('AI initialization error:', error);
        aiAvailable = false;
    }
})();

const validImageTypes = {
	"image/png": true,
	"image/webp": true,
	"image/jpeg": true,
	"image/svg+xml": true,
	"image/svg": true,
	"image/gif": true,
}

const webFriendlyBlacklist = {
	"image/svg+xml": true,
	"image/svg": true,
	"image/gif": true,
}

function getValidImageTypes(webFriendly=false) {
	const imageTypes = [];

	for (const [key, _] of Object.entries(validImageTypes)) {
		if(webFriendly) {
			if(!webFriendlyBlacklist[key]) {
				imageTypes.push(key);
			}
		} else {
			imageTypes.push(key);
		}
	}

	return imageTypes;
}

async function initializeAI() {
    aiAvailable = await aiTextGenerator.initialize();
    return aiAvailable;
}

function doUpload(e) {
    (async () => {
        await initializeAI();
    })();
	// check method
	let myfiles;
	if (e.type=="drop") {
		myfiles = e.dataTransfer.files;
	} else if(e.type=="customUploadImages") {
		myfiles = e.detail.files;
	} else {
		// assume file input 
		myfiles = e.target.files;
	}

	//for later
	const rootEventDetail = e?.detail;

	// RUN THROUGH THE DROPPED FILES + AJAX UPLOAD
	window.formdata = new FormData();

	// check against max upload size
	let uploaded_size_total=0;
	let invalid_counter = 0;
	for (let i = 0; i < myfiles.length; i++) {
		console.log(myfiles[i].type);
		if (!validImageTypes[myfiles[i].type]) {
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
		if (!validImageTypes[myfiles[i].type]) {
            // skip anything but png or jpg
            continue;
        }
		const id = `img_id_${i}`;
        const aiButtonsMarkup = aiAvailable ? `
            <button class='button is-small is-info ai-generate-title' type='button' data-target='title_${id}' title='Generate with AI'>
                <i class='fa fa-wand-magic-sparkles'></i> AI
            </button>
            <button class='button is-small is-info ai-generate-alt' type='button' data-target='alt_${id}' title='Generate with AI'>
                <i class='fa fa-wand-magic-sparkles'></i> AI
            </button>
        ` : '';
		markup = `
            <div class='upload_field' data-field-index='${i}'>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/2.12.0/slimselect.min.css"/>
                <div class='upload_preview'>
                    <img id='${id}' src=''>
                </div>
                <div class='upload_details'>
                    <div class='field'>
                        <label>Title</label>
                        <div style='display: flex; gap: 0.5rem;'>
                            <input name='title[]' id='title_${id}' style='flex: 1;' required/>
                            ${aiAvailable ? `<button class='button is-small is-info ai-generate-title' type='button' data-target='title_${id}' data-field-index='${i}' title='Generate with AI'>
                                <i class='fa fa-wand-magic-sparkles'></i> AI
                            </button>` : ''}
                        </div>
                    </div>
                    <div class='field'>
                        <label>Alt</label>
                        <div style='display: flex; gap: 0.5rem;'>
                            <input name='alt[]' id='alt_${id}' style='flex: 1;' required/>
                            ${aiAvailable ? `<button class='button is-small is-info ai-generate-alt' type='button' data-target='alt_${id}' data-field-index='${i}' title='Generate with AI'>
                                <i class='fa fa-wand-magic-sparkles'></i> AI
                            </button>` : ''}
                        </div>
                    </div>
                    <div class='field' style='display: flex;'>
                        <label style='flex-shrink: 0;'>Tags</label>
                        <select multiple id="upload_dialog_ss_${id}" class="slimselectme" name='itags[]'>
                        </select>
                    </div>
                </div>
            </div>
        `;
        upload_form.innerHTML = upload_form.innerHTML + markup;

        // After the modal is appended to document.body, add AI button handlers
        if (aiAvailable) {
            // Handle AI generate buttons
            document.querySelectorAll('.ai-generate-title, .ai-generate-alt').forEach(button => {
                button.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const btn = e.target.closest('button');
                    const targetInputId = btn.dataset.target;
                    const fieldIndex = btn.dataset.fieldIndex;
                    const input = document.getElementById(targetInputId);
                    const isTitle = btn.classList.contains('ai-generate-title');
                    
                    // Show loading state
                    const originalHTML = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                    
                    try {
                        // Get the image data
                        const imgElement = document.getElementById(`img_id_${fieldIndex}`);
                        const filename = myfiles[fieldIndex].name;
                        
                        // Generate text
                        let generatedText;
                        if (isTitle) {
                            generatedText = await aiTextGenerator.generateTitle(imgElement.src, filename);
                        } else {
                            generatedText = await aiTextGenerator.generateAltText(imgElement.src, filename);
                        }
                        
                        // Fill the input
                        input.value = generatedText;
                        
                        // Visual feedback
                        input.classList.add('is-success');
                        setTimeout(() => input.classList.remove('is-success'), 2000);
                        
                    } catch (error) {
                        console.error('AI generation failed:', error);
                        alert('Failed to generate text. Please try again or enter manually.');
                    } finally {
                        // Restore button
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    }
                });
            });
            
            // Optional: Add a "Generate All" button
            const generateAllBtn = document.createElement('button');
            generateAllBtn.className = 'button is-primary';
            generateAllBtn.type = 'button';
            generateAllBtn.innerHTML = '<i class="fa fa-wand-magic-sparkles"></i> Generate All Alt & Title';
            generateAllBtn.style.margin = '1rem';
            
            generateAllBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                const btn = e.target.closest('button');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';
                
                try {
                    const fields = upload_form.querySelectorAll('.upload_field');
                    for (let i = 0; i < fields.length; i++) {
                        const titleInput = document.getElementById(`title_img_id_${i}`);
                        const altInput = document.getElementById(`alt_img_id_${i}`);
                        const imgElement = document.getElementById(`img_id_${i}`);
                        const filename = myfiles[i].name;
                        
                        if (!titleInput.value) {
                            titleInput.value = await aiTextGenerator.generateTitle(imgElement.src, filename);
                        }
                        if (!altInput.value) {
                            altInput.value = await aiTextGenerator.generateAltText(imgElement.src, filename);
                        }
                    }
                } catch (error) {
                    console.error('Batch generation failed:', error);
                    alert('Some generations failed. Please check and fill in missing fields.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-wand-magic-sparkles"></i> Generate All Alt & Title';
                }
            });
            
            upload_form.insertBefore(generateAllBtn, upload_form.firstChild);
        }
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

	fetch(`${window.uripath}/image/gettags`).then((response)=>response.json()).then((json)=>{
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

	document.getElementById('image_upload_form').addEventListener('submit',(e)=>{
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

				const upload_el = document.getElementById(window.image_upload_el);

				upload_el.value = data.ids.split(",")[0];
				upload_el.parentElement.querySelector(".selected_image_wrap img").src = `${data.urls.split(",")[0]}/thumb`;
				upload_el.parentElement.querySelector(".selected_image_wrap").classList.add("active");
				upload_el.setCustomValidity('');

				delete window.image_upload_el;
			} else if(rootEventDetail?.callback) {
				rootEventDetail.callback(data);
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

function initGraphicalUploaderEventListeners(elementSelector) {
    // GET THE DROP ZONE
    const uploader = document.querySelector(elementSelector);

    // STOP THE DEFAULT BROWSER ACTION FROM OPENING THE FILE
    uploader.addEventListener("dragover", (e)=>{
        e.preventDefault();
        e.stopPropagation();
        e.target.classList.add('ready');
    });

    uploader.addEventListener("dragleave", (e)=>{
        e.preventDefault();
        e.stopPropagation();
        e.target.classList.remove('ready');
    });

    // ADD OUR OWN UPLOAD ACTION
    uploader.addEventListener("drop", (e)=>{
        e.preventDefault();
        e.stopPropagation();
        e.target.classList.remove('ready');
        document.querySelector("#image_uploader")?.remove();
        doUpload(e);
    });
}

function initInputFileUploaderEventListeners(elementSelector) {
    document.querySelector(elementSelector).addEventListener('change', (e)=>{
        document.querySelector("#image_uploader")?.remove();
        doUpload(e);
    });
}

function addImageUploadDialog() {
	const markup = `
		<style>
			#upload_space {
                height:10rem;
                padding:1rem;
                margin:1rem;
                border:2px dashed #aaa;
                display:flex;
                align-items: center;
                justify-content: center;
                transition:all 0.3s ease;
                
                h1 {
                    font-size:2rem;
                    opacity:0.3;
                    font-weight:900;
                }
                
                &.ready {
                    border:2px dashed #aaa;
                    background:#cec;
                }
            }
		</style>
		<div class="modal-background"></div>
		<div class="modal-card">
			<header class="modal-card-head">
				<p class="modal-card-title">Upload Image</p>
				<button class="delete" aria-label="close"></button>
			</header>
			<section class="modal-card-body">
				<div id="upload_space"><h1>Drag & Drop New Images Here</h1></div>
				<br>
				<input accept="image/*" id="regular_upload" type="file" multiple="">
			</section>
			<footer class="modal-card-foot"></footer>
		</div>
	`;

	const modal = document.createElement("div");
	modal.id="image_uploader";
	modal.classList.add("modal");
	modal.classList.add("is-active");
	modal.innerHTML = markup;
	modal.querySelector("button.delete").addEventListener("click", (e)=>{
		e.target.closest("#image_uploader").remove();
	});
	document.body.appendChild(modal);

	initGraphicalUploaderEventListeners("#upload_space");
	initInputFileUploaderEventListeners("#regular_upload");
}

export {
	initGraphicalUploaderEventListeners,
	initInputFileUploaderEventListeners,
	doUpload,
	addImageUploadDialog,
	getValidImageTypes,
}