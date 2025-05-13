<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Rich extends Field {

	public $mimetypes;
	public $tags;

	public function display() {
		?>
		<style>
		.editor a {
			position:relative;
		}
		.editor a[target='_blank']::before {
			content: "🔗";
			font-weight:400;
			display:block;
			position:absolute;
			transform:translateY(-1em);
			text-align:right;
			font-size:12px;
			opacity:0.5;
			right:0;
			top:0;
			min-width:16px;
		}
		.editor .internal_anchor {
			display:inline-block;
			width:2em;
			height:2em;
			text-align:center;
			aspect-ratio:1/1;
			content:"A";
			background:black;
			color:white;
			margin:0.25em;
			font-size:10px;
			padding:0.25em;
			/* text-indent:-9999px; */
		}
		
		.editor { border:2px dashed #aaa; padding:1rem; max-height:25rem; overflow:auto;}
		.editor_button {margin-left:1rem;}
		.editor h1, .editor h2, .editor h3, .editor h4, .editor h5,.editor p, .editor ul, .editor ol {
			padding:0.5rem;
			margin:0.5rem;
			background:rgba(0,0,0,0.05);
			border:2px dotted rgba(0,0,0,0.05);
			position:relative;
		}
		.editor p::before, .editor h1::before, .editor h2::before, .editor h3::before, .editor h4::before, .editor h5::before, .editor ul::before, .editor ol::before {
			font-size:60%;
			background:white;
			padding:0.5rem;
			margin:0.5rem;
			box-shadow:0 0 5px rgba(0,0,0,0.2);
			display:inline-block;
			position:absolute;
			top:-1rem;
			right:-1rem;
		}
		.editor ul::before {
			content:"UL";
		}
		.editor ol::before {
			content:"UL";
		}
		.editor p::before {
			content:"P";
		}
		.editor p {
			overflow:hidden;
		}
		.editor h1::before {
			content:"H1";
		}
		.editor h2::before {
			content:"H2";
		}
		.editor h3::before {
			content:"H3";
		}
		.editor h4::before {
			content:"H4";
		}
		.editor h5::before {
			content:"H5";
		}
		.editor .rich_image {
			max-width:10em;
			padding:1rem;
			margin:1rem;
			border:2px dotted rgba(0,0,0,0.2);
		}
		.editor .rich_image:hover {
			cursor:pointer;
		}
		.editor .rich_image_active {
			outline:2px dotted green;
		}
		.editor figure.rich_image_figure {
			margin: 1rem;
			padding: 1rem;
			border: 2px solid rgba(0,0,0,0.1);
			display: inline-block;
		}
		.editor [aria-hidden="true"] {
			pointer-events:none;
		}

		[data-tooltip] {
			position:relative;
		}
		[data-tooltip]:hover::before {
			content: "";
			position: absolute;
			top:-0.25rem;
			left:50%;
			transform: translateX(-50%);
			border-width: 4px 6px 0 6px;
			border-style: solid;
			border-color: rgba(0,0,0,0.7) transparent transparent transparent;
			z-index: 100;
		}
		[data-tooltip]:hover::after {
			content: attr(data-tooltip);
			position: absolute;
			left:50%;
			top:-0.25rem;
			transform: translateX(-50%) translateY(-100%);
			background: rgba(0,0,0,0.7);
			text-align: center;
			color: #fff;
			padding: 0.5rem 0.25rem;
			font-size: 0.75rem;
			min-width: 5rem;
			border-radius: 0.25rem;
			pointer-events: none;
		}
		.editorfieldwrapper:has(textarea:invalid) .editor.content {
			border: 2px dashed red;
		}
		</style>
		<script type="module">
		import {openMediaSelector} from "/core/js/media_selector.js";
			// TODO: make id/agnostic for repeatable + live additions
			//document.addEventListener("DOMContentLoaded", function(){

				if (!window.hasOwnProperty('editor_code_already_exists')) {
					// editor code only needs to exist once for a given page
					// with multiple editor fields / repeatables
					document.addEventListener('input',function(e){
						if (e.target.classList.contains('editor')) {
							// console.log('INPUT DETECTED IN EDITOR');
							// move markup to hidden textarea
							let raw = e.target.innerHTML; 
							let textarea = e.target.closest('.control').querySelector('textarea');
							textarea.value = raw; 
						}
						if (e.target.classList.contains('editor_raw')) {
							// move textarea to markup in editable on any change
							let raw = e.target.value;
							e.target.closest('.control').querySelector('.editor').innerHTML = raw;
						}
					});

					/* 
						we have this here to save users who think they are so smart from themselves
						as the raw html is being edited, it is being put into the dom, which is quite forgiving.
						however when it is saved, the literal text is what is saved rather than the dom version
						so on page reload, it will load the broken version which can lead to weirdness such as the textarea in the editor, etc
						this of course would not be an issue if we properly iframed, etc the content of the editor.
						this work around updates the textarea after focus leaves from the dom so that a "fixed" version is saved and avoids the above issues
					*/
					document.addEventListener("focusout", (e)=>{
						if(e.target.classList.contains("editor_raw")) {
							let raw = e.target.closest('.control').querySelector('.editor').innerHTML;
							e.target.value = raw;
						}
					});

					document.addEventListener('click', (e)=>{
						// click event handler for editor 

						//check that we are being called from inside the editor
						if(!e.target.closest(".editorfieldwrapper")) {
							return;
						}

						if(!window.getSelection().focusNode || !window.getSelection().focusNode.parentElement.closest(".editor.content[contenteditable='true']")) {
							//only show the alert if we're not interacting with the code view toggle or raw editor
							if(!e.target.classList.contains('toggle_editor_raw') && !e.target.closest('.toggle_editor_raw') && !e.target.classList.contains('editor_raw')) {
								alert("Please select in editor where you want to apply this command!");
								return;
							}
						}

						if (e.target.nodeName==='A') {
							
							// only work on anchors inside editor (i.e. not toolbar or elswhere... :) )
							let in_editor = e.target.closest('.editor');
							if (in_editor) {
								if(e.target.classList.contains('internal_anchor')) {
									return;
								}
								window.currentAnchorToEdit = e.target;
								e.target.closest(".editorfieldwrapper").querySelector('[data-command="createlink"]').click();
								return;
							}
						}

						if (e.target.classList.contains('rich_image')) {
							// handle rich image click
							// clear any active rich image
							var current_active = e.target.closest('.control').querySelector('.editor .rich_image_active');
							if (current_active!==null) {
								current_active.classList.remove('rich_image_active');
							}
							// make clicked active
							var img = e.target;
							img.classList.add('rich_image_active');
						}
						else {
							// remove rich_image_active state
							let editor = e.target.closest('.editor');
							if (editor) {
								let all_editor_active_images = editor.querySelectorAll('img.rich_image_active');
								all_editor_active_images.forEach(img => {
									img.classList.remove('rich_image_active');
								});
							}
						}

						if (e.target.classList.contains('internal_anchor')) {
							let kill_anchor = window.confirm('Remove anchor?');
							if (kill_anchor) {
								e.target.remove();
							}
						}

						let closest_editor_toolbar = e.target.closest('.hbcms_editor_toolbar');
						if (closest_editor_toolbar) {
						
							// handle editor toolbar clicks

							e.preventDefault();

							// remember dynamic editor/textarea
							window.this_editor = e.target.closest('.control').querySelector('.editor');
							window.this_textarea = e.target.closest('.control').querySelector('textarea');

							let editor_button;
							if (e.target.classList.contains('fa')) {
								editor_button = e.target.closest('.editor_button');
							}
							else {
								editor_button = e.target;
							}
							const command = editor_button.dataset.command;
							console.log('Command: ',command);

							if (editor_button.classList.contains('toggle_editor_raw')) {
								let control = editor_button.closest('.control');
								let raw = control.querySelector('textarea.editor_raw');
								if (raw.style.display=='block') {
									raw.style.display='none';
								}
								else {
									raw.style.display='block';
								}
								return false;
							}

							if (command == 'h1' || command == 'h2' || command == 'h3' || command == 'h4' || command == 'p') {
								document.execCommand('formatBlock', false, command);
							}

							else if (command=="em") {
								document.execCommand('italic', false, command);
							}

							else if (command=="small") {
								document.execCommand('superscript', false, command);
							}
							
							else if (command == 'createlink') {
								var selection = window.currentAnchorToEdit ? window.currentAnchorToEdit.innerText : window.getSelection().toString();
								console.log("selection: ", selection);

								let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

								let href=window.currentAnchorToEdit ? window.currentAnchorToEdit.getAttribute('href') : "";
								let classes=window.currentAnchorToEdit ? window.currentAnchorToEdit.classList.value : "";
								let target=window.currentAnchorToEdit ? (window.currentAnchorToEdit.getAttribute('target') ? window.currentAnchorToEdit.getAttribute('target') : "") : "";

								const fields = [
									{
										type: "input",
										id: "a_url",
										label: "URL",
										pattern: `https?:\/\/(?:(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)(?:\.(?:[a-zA-Z\u00a1-\uffff0-9]+-?)*[a-zA-Z\u00a1-\uffff0-9]+)*(?:\.(?:[a-zA-Z\u00a1-\uffff]{2,}))(?::\d{2,5})?(?:\/[^\s]*)?`,
										value: href,
									},
									{
										type: "input",
										id: "a_text",
										label: "Display Text",
										value: selection,
									},
									{
										type: "input",
										id: "a_class",
										label: "Class",
										value: classes,
									},
									{
										type: "select",
										id: "a_target",
										label: "Open in",
										value: target,
										options: [
											{
												value: "",
												text: "Current Window",
											},
											{
												value: "_blank",
												text: "New Window/Tab",
											}
										],
									}
								];

								if(!window.currentAnchorToEdit) {
									let link_html = `<a data-foo='bar' target='${target}' class='${classes}' id='newly_created_link_for_<?php echo $this->name;?>'>${selection}</a>`;
									document.execCommand('insertHTML', false, link_html);							
									let link = document.getElementById('newly_created_link_for_<?php echo $this->name;?>');
									link.removeAttribute('style');	// get rid of any styling that might be preserved from user copy/paste
								} else if(window.currentAnchorToEdit) {
									window.currentAnchorToEdit.id = "newly_created_link_for_<?php echo $this->name;?>";
								}

								window.live_editor = this_editor;
								const modal = createModal(fields);
								modal.addEventListener("modalFormAdd", (e)=>{
									// get values and update link
									let link = document.getElementById('newly_created_link_for_<?php echo $this->name;?>');
									link.href = document.getElementById(fields[0].id).value;
									link.innerHTML = document.getElementById(fields[1].id).value;
									link.classList.value = document.getElementById(fields[2].id).value;
									link.target = document.getElementById(fields[3].id).value;
									link.removeAttribute('id');		// remove id so future links not messed up

									window.currentAnchorToEdit = undefined;
								});
								modal.addEventListener("modalFormCancel", (e)=>{
									if(!window.currentAnchorToEdit) {
										// replace anchor with original text
										let link = document.getElementById('newly_created_link_for_<?php echo $this->name;?>');
										link.parentNode.replaceChild(document.createTextNode(selection), link);
									}

									window.currentAnchorToEdit = undefined;
								});
							}

							else if (command == 'createanchor') {
								//capture the current selection and save the range - need to do this to properly insert anchor into text later
								const selection = document.getSelection();
								let savedRange = null;
								
								if (selection.getRangeAt && selection.rangeCount) {
									savedRange = selection.getRangeAt(0).cloneRange(); //store a copy of the range immediately
								}
								
								let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids
								
								const fields = [
									{
										type: "input",
										id: `anchor_id_for_${uniq}`,
										label: "Anchor ID",
										value: "",
										helpText: "Enter a unique ID for this anchor. Will be used in URLs like #your-id",
									}
								];
								
								window.live_editor = this_editor;
								const modal = createModal(fields);

								modal.addEventListener("modalFormAdd", (e)=>{
									// get values and update link
									let anchorid = document.getElementById(fields[0].id).value;
									if (anchorid && savedRange) {
										//restore the range (so the anchor is inserted at the correct position in the text editor)
										var frag = savedRange.createContextualFragment(`<a title='#${anchorid}' class='internal_anchor' id='${anchorid}' ><i class='fa fa-anchor' aria-hidden='true'></i></a>`);
										savedRange.insertNode(frag);
									}
								});
								
							}

							else if (command=='floatleft') {
								var active_image = this_editor.querySelector('.rich_image_active');
								if (active_image!==null) {
									active_image.classList.add('pull-left');
									active_image.classList.remove('rich_image_active','pull-right');

									// do the same for parent figure if active_image captioned
									if (active_image.parentElement.tagName == 'FIGURE') {
										active_image.parentElement.classList.add('pull-left');
										active_image.parentElement.classList.remove('pull-right');
									}
								}
								else {
									alert('No image selected');
								}
							}

							else if (command=='edit_image_props') {
								
								var active_image = this_editor.querySelector('.rich_image_active');
								
								if (active_image!==null) {
									
									// get current image props
									let alt = active_image.alt;
									let title = active_image.title;

									let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

									const fields = [
										{
											type: "input",
											id: `alt_text_for_${uniq}`,
											label: "Alt Text",
											value: alt,
											helpText: "Alternative text for the visually impaired, will also display when the browser cannot render the image.",
										},
										{
											type: "input",
											id: `title_for_${uniq}`,
											label: "Image Title",
											value: title,
											helpText: "Title will appear in a tooltip on hover of the image. It can also be used to provide a broader description than the alt text.",
										},
									];

									window.live_editor = this_editor;
									const modal = createModal(fields);
									modal.addEventListener("modalFormAdd", (e)=>{
										// set to image
										let new_alt = document.getElementById(fields[0].id).value;
										let new_title = document.getElementById(fields[1].id).value;
										active_image.alt = new_alt;
										active_image.title = new_title;
										active_image.classList.remove('rich_image_active');
										
										// push updated content to textarea
										active_image.closest('.control').querySelector('textarea').value = active_image.closest('.editor').innerHTML;
									});
								}
								else {
									alert('No image selected');
								}
							}

							else if (command=='edit_image_attribution') {
								var active_image = this_editor.querySelector('.rich_image_active');
								if (active_image!==null) {

									// check data attributes if present to load into modal
									let author = active_image.dataset.author ? active_image.dataset.author : "";
									let source = active_image.dataset.source ? active_image.dataset.source : "";
									let license = active_image.dataset.license ? active_image.dataset.license : "";
									let licenselink = active_image.dataset.licenselink ? active_image.dataset.licenselink : "";

									let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

									const fields = [
										{
											type: "input",
											id: `image_author_for_${uniq}`,
											label: "Image Author",
											value: author,
										},
										{
											type: "input",
											id: `image_source_for_${uniq}`,
											label: "Image Source",
											value: source,
										},
										{
											type: "input",
											id: `license_name_for_${uniq}`,
											label: "License Name",
											value: license,
										},
										{
											type: "input",
											id: `license_link_for_${uniq}`,
											label: "License Link",
											value: licenselink,
											helpText: "Enter full link including https://",
										},
									];

									window.live_editor = this_editor;
									const modal = createModal(fields);
									modal.addEventListener("modalFormAdd", (e)=>{
										// get new values for information
										let new_author = document.getElementById(fields[0].id).value;
										let new_source = document.getElementById(fields[1].id).value;
										let new_license = document.getElementById(fields[2].id).value;
										let new_licenselink = document.getElementById(fields[3].id).value;
										
										// update image dataset
										active_image.dataset.author = new_author;
										active_image.dataset.source = new_source
										active_image.dataset.license = new_license
										active_image.dataset.licenselink = new_licenselink

										// will be used to determine if there will be captions
										let new_data = [new_author, new_source, new_license, new_licenselink];

										function allEmpty(array) {
											for (const item of array) {
												if (item!="") return false;
											}
											return true;
										}

										let hasFigure = active_image.parentElement.nodeName==="FIGURE";
										let hasCaptions = !allEmpty(new_data);

										function createFigure() {
											let image = active_image;
											let figure = document.createElement('FIGURE');
											figure.classList.add('rich_image_figure');
											
											// deal with pull left and right
											if (image.classList.contains('pull-right')) { 
												figure.classList.add('pull-right');
											}
											if (image.classList.contains('pull-left')) {
												figure.classList.add('pull-left');
											}

											active_image.replaceWith(figure);
											figure.appendChild(image);
										}

										function removeFigure() {
											let figure = active_image.parentElement;
											let image = active_image;
											figure.replaceWith(image);
										}

										function addCaptions() {

											let figure = active_image.parentElement;

											function appendFigcaptionToFigure(label, content, figure) {
												let figCaption = document.createElement('FIGCAPTION');
												let uniq = label.replace(/\s+/g, '_').toLowerCase();
												let div = `<div class="image_${uniq}"><span class="attrib_label">${label}:&nbsp;</span><span class="attrib_value">${content}</span></div>`;
												figCaption.innerHTML = div; 
												console.log(figCaption);
												figure.appendChild(figCaption);
											} 

											// create only needed captions
											if (new_author) {
												appendFigcaptionToFigure(`Photo By`, new_author, figure);
											}
											if (new_source) {
												appendFigcaptionToFigure(`Source`, new_source, figure);
											}
											if (new_license || new_licenselink) {

												function makeLinkHTML(href, content) {
													return `<a href="${href}">${content}</a>`;
												}

												// sort out whether a link should/shouldn't be included
												let new_content = "";
												if (new_licenselink && new_license) {
													new_content = makeLinkHTML(new_licenselink, new_license);
												} 
												else if (new_licenselink && !new_license) {
													new_content = makeLinkHTML(new_licenselink, new_licenselink);
												}
												else if (!new_licenselink && new_license) {
													new_content = new_license;
												}

												appendFigcaptionToFigure(`License`, new_content, figure);
											}
										}

										// logical schema for when to add/rem figure and captions
										if (!hasFigure && hasCaptions) {
											createFigure();
											addCaptions();
										}
										else if (hasFigure && hasCaptions) {
											removeFigure();
											createFigure();
											addCaptions();
										} 
										else if (hasFigure && !hasCaptions) {
											removeFigure();
										}
										else if (!hasFigure && !hasCaptions) {
											// all is well, do nothing
										}
									});
									
								}
								else {
									alert('No image selected');
								}
							}

							else if (command=='floatright') {
								var active_image = this_editor.querySelector('.rich_image_active');
								if (active_image!==null) {
									active_image.classList.add('pull-right');
									active_image.classList.remove('rich_image_active','pull-left');

									// do the same for parent figure if active_image captioned
									if (active_image.parentElement.tagName == 'FIGURE') {
										active_image.parentElement.classList.add('pull-right');
										active_image.parentElement.classList.remove('pull-left');
									}

								}
								else {
									alert('No image selected');
								}
							}

							else if (command=='floatclear') {
								var active_image = this_editor.querySelector('.rich_image_active');
								if (active_image!==null) {
									active_image.classList.remove('pull-left','pull-right');
									active_image.classList.remove('rich_image_active');

									// do the same for parent figure if active_image captioned
									if (active_image.parentElement.tagName == 'FIGURE') {
										active_image.parentElement.classList.remove('pull-left', 'pull-right');
									}
								}
								else {
									alert('No image selected');
								}
							}
							
							else if (command=='img') {
								// get variables for openMediaSelector()
								let elementId = "<?php echo $this->id; ?>";
								let imagesPerPage = <?php echo $this->images_per_page ?? 50;?>;
								let mimetypes = <?php echo json_encode($this->mimetypes); ?>;
								let tags = <?php echo json_encode($this->tags);?>;
								let listingEndpoint = '<?php echo Config::uripath();?>/image/list_images';
								
								// set up rich editor variables
								let lastEditor = document.querySelector(`#editor_toolbar_for_${elementId}`);
								let selected = document.getSelection(); 
								let saved = [selected.focusNode, selected.focusOffset];

								// launch image selector
								const mediaSelector = openMediaSelector(elementId, imagesPerPage, mimetypes, tags, listingEndpoint);
								
								mediaSelector.addEventListener("mediaItemSelected", (mediaE) => {
									const url = mediaE.detail.url + "/web";
									const imageMarkup = `<img alt="${mediaE.detail.alt}" title="${mediaE.detail.title}" class="rich_image" data-mediaId="${mediaE.detail.mediaId}" data-size="web" src="${url}"/>`;
									
									// focus back on the editor and insert the HTML at the saved position
									lastEditor.focus();
									selected.collapse(saved[0], saved[1]);
									document.execCommand('insertHTML', false, imageMarkup);
								});
							}

							else if (command=='toggle_external_link') {
								let this_sel = document.getSelection(); 
								let this_parent = this_sel.focusNode.parentElement;
								if (this_parent && this_parent.nodeName=="A") {
									// caret inside anchor!
									if (this_parent.target=="_blank") {
										this_parent.target="_self";
									}
									else {
										this_parent.target="_blank";
									}
								}
							}

							else if (command == 'ul' || command == 'ol') {
								if (command=='ol') {
									document.execCommand('insertorderedlist', false, command);
								}
								else {
									document.execCommand('insertunorderedlist', false, command);
								}
							}
							else if (command == 'addclass') {
								
								// get parent model to potentially use later
								let parent = window.getSelection().focusNode.parentNode;

								let uniq = ("<?php echo $this->name;?>").replace(/\s+/g, '_');		// for ids

								const fields = [
									{
										type: "input",
										id: `new_class_for_${uniq}`,
										label: "Add Class",
										value: "",
										helpText: "Must be alphanumeric with no spaces or will not be added.",
									},
								];
								
								window.live_editor = this_editor;
								const modal = createModal(fields);
								modal.addEventListener("modalFormAdd", (e)=>{
									// add class
									let new_class_name = document.getElementById(fields[0].id).value;
									var modal = event.target.closest('.modal');
									if (new_class_name) {
										parent.classList.add(new_class_name);
									}
								});

							}

							else if (command=="removeFormat") {
								// remove all inline style from all elements and
								// then do default remove formatting
								let editor_els = this_editor.querySelectorAll('*');
								editor_els.forEach(el => {
									el.removeAttribute('style');
								});
								document.execCommand(command, false, null);
							}

							else document.execCommand(command, false, null);

							// force update of editor contents
							let markup = this_editor.innerHTML;
							this_textarea.value = markup;
						}
					});


					document.addEventListener('paste',function(e){
						if (e.target.classList.contains('editor')) {
							// remove styles on paste
							let found_image = false;
							pasteEvent.preventDefault();
							//console.log((pasteEvent.clipboardData || pasteEvents.originalEvent.clipboardData).items);
							let items = (pasteEvent.clipboardData || pasteEvents.originalEvent.clipboardData).items;
							for (var i = 0; i < items.length; i++) {
								// Skip content if not image
								if (items[i].type.indexOf("image") == -1) continue;
								// Retrieve image on clipboard as blob
								let blob = items[i].getAsFile();
								var reader = new FileReader();
								reader.onload = function(event) {
									//let pasted_img = new Image;
									//pasted_img.src = event.target.result;
									let img_markup = `<img class="pasted rich_image" src="${event.target.result}"/>`;
									document.execCommand("insertHTML", false, img_markup);
								};
								reader.readAsDataURL(blob);
								found_image = true;
							}
							
							if (!found_image) {
								console.log('not image - cleaning paste');
								if (pasteEvent.clipboardData.types.includes('text/html')) {
									var text = pasteEvent.clipboardData.getData("text/html");
									text = text.replace(/style="[^"]*"/gi,"");
									document.execCommand("insertHTML", false, text);
								}
								else if (pasteEvent.clipboardData.types.includes('text/plain')) {
									// attempt plaintext paste
									var text = pasteEvent.clipboardData.getData("text/plain");
									document.execCommand("insertText", false, text);
								}
								else {
									alert('Unknown content type pasted');
								}
							}
						}
					});

					// set global flag to ensure event listeners only added once
					window.editor_code_already_exists = true;
				}

				//takes a js object and converts it to a form field
				function renderField(field) {
					let html = `<div class="field">`;
						if(field.label) {
							html+=`<label class="label">${field.label}</label>`;
						}
						switch (field.type) {
							case "input":
								html+=`<input id="${field.id}" ${field.pattern ? `pattern="${field.pattern}"` : ""} class="input" type="text" value="${field.value}">`;
								break;

							case "select":
								html+=`<div class="select"><select style="width: 100%;" id="${field.id}">`;
									field.options.forEach((option)=>{
										html+=`<option ${option.value==field.value ? "selected" : ""} value="${option.value}">${option.text}</option>`;
									});
								html+=`</select></div>`;
								break;

							default:
								html+=`<p>INVALID FIELD TYPE!!!`;
								break;
						}
						if(field.helpText) {
							html+=`<p class='help'>${field.helpText}</p>`;
						}
					html += `</div>`;

					return html;
				}

				//creates a sane modal from fields provided, returns a modal. listen to custom events modalFormAdd, and modalFormCancel
				function createModal(fields=[]) {
					// create and show modal based on desired user inputs
					let modal = document.createElement('div');
					// modal.id = "add_info_modal_for_<?php echo $this->name;?>";
					modal.classList = "modal is-active";
					let modal_html = `
						<div class="modal-background"></div>
						<div class="modal-content">
							<div class="box">
					`;
					fields.forEach((field)=>{
						modal_html+=renderField(field);
					})
					modal_html += `
								<button class="button is-primary" data-modal-action="add">Add</button>
								<button class="button is-warning" data-modal-action="cancel">Cancel</button>
								
							</div>
						</div>						
					`;

					modal.innerHTML = modal_html;
					document.body.appendChild(modal);

					// make first modal input focus
					let first_input = modal.querySelector('input');
					if (first_input) {
						first_input.focus();
					}

					// listener for modal
					modal.addEventListener('click', function(e){
						e.preventDefault();

						function closeModal() {
							modal = e.target.closest('.modal.is-active');
							parent = modal.parentNode;
							parent.removeChild(modal);	
							// update editor raw textarea with changes
							let markup = window.live_editor.innerHTML;
							window.live_editor.closest('.control').querySelector('textarea').value = markup;
						}

						switch (e.target.dataset.modalAction) {
							
							case "add":
								let validity = true;
								modal.querySelectorAll("input, select").forEach((el)=>{
									if(el.validity.valid==false) {
										validity = false;
									}
								});

								if(validity==false) {
									alert("Invalid Field Entry!");
									return;
								}

								modal.dispatchEvent(new CustomEvent("modalFormAdd", { target: modal }));
								closeModal();
								break;

							case "cancel":
								modal.dispatchEvent(new CustomEvent("modalFormCancel", { target: modal }));
								closeModal();
								break;
						}

					});

					return modal;
				}
			//});
		</script>
		<?php
		if (!Config::debug()) {
			echo "<style>.editor_raw {display:none;}</style>";
		}
		$required="";
		if ($this->required) {$required=" required ";}
		echo "<div class='field {$required} editorfieldwrapper'>";
			echo "<label class='label'>{$this->label}</label>";
			echo "<div class='control'>";
				?>
				<!-- toolbar -->
				<div class='hbcms_editor_toolbar' id='editor_toolbar_for_<?php echo $this->name; ?>'>
					<a class='editor_button' href="#" data-command='h1' data-tooltip="Heading 1">H1</a>
					<a class='editor_button' href="#" data-command='h2' data-tooltip="Heading 2">H2</a>
					<a class='editor_button' href="#" data-command='h3' data-tooltip="Heading 3">H3</a>
					<a class='editor_button' href="#" data-command='h4' data-tooltip="Heading 4">H4</a>
					<a class='editor_button' href="#" data-command='p' data-tooltip="Paragraph">P</a>
					<a class='editor_button' href="#" data-command='ul' data-tooltip="Unordered List">UL</a>
					<a class='editor_button' href="#" data-command='ol' data-tooltip="Numbered List">OL</a>
					<a class='editor_button' href="#" data-command='bold' data-tooltip="Bold"><i class="fa fa-bold"></i></a>
					<a class='editor_button' href="#" data-command='em' data-tooltip="Italics"><i class="fa fa-italic"></i></a>
					<a class='editor_button' href="#" data-command='small' data-tooltip="Small"><i class="fa fa-compress"></i></a>
					<a class='editor_button' href="#" data-command='underline' data-tooltip="Underline"><i class="fa fa-underline"></i></a>
					<a class='editor_button' href="#" data-command='addclass' data-tooltip="Add Class">Cls+</a>
					<a class='editor_button' href="#" data-command='img' data-tooltip="Image"><i class="fa fa-images"></i></a>
					<a class='editor_button' href="#" data-command='undo' data-tooltip="Undo"><i class='fa fa-undo'></i></a>
					<a class='editor_button' href="#" data-command='createlink' data-tooltip="Create Link"><i class='fa fa-link'></i></a>
					<a class='editor_button' href="#" data-command='unlink' data-tooltip="Delete Link"><i class='fa fa-unlink'></i></a>
					<a class='editor_button' href="#" data-command='createanchor' data-tooltip="Add Anchor"><i class='fa fa-anchor'></i></a>
					<a class='editor_button' href="#" data-command='toggle_external_link' title='Toggle external link' data-tooltip="Toggle External Link"><i class='fa fa-external-link'></i></a>
					<a class='editor_button' href="#" data-command='justifyLeft' data-tooltip="Justify Left"><i class='fa fa-align-left'></i></a>
					<a class='editor_button' href="#" data-command='superscript' data-tooltip="Super Script"><i class='fa fa-superscript'></i></a>
					<a class='editor_button' href="#" data-command='removeFormat' data-tooltip="Remove Formating"><i class='fa fa-broom'></i></a>
					<a class='editor_button image_selected' href='#' data-command='floatleft' data-tooltip="Float Left">FL</a>
					<a class='editor_button image_selected' href='#' data-command='floatright' data-tooltip="Float Right">FR</a>
					<a class='editor_button image_selected' href='#' data-command='floatclear' data-tooltip="Clear Float">FC</a>
					<a class='editor_button image_selected' href='#' data-command='edit_image_props' data-tooltip="Image Atrributes">IMG ALT/TITLE</a>
					<a class='editor_button image_selected' href='#' data-command='edit_image_attribution' data-tooltip="Image Author">IMG ATTRIB</a>
					<a class='editor_button toggle_editor_raw' href="#" data-command='none' data-tooltip="Code View"><i class='fa fa-edit'></i></a>
				</div>
				<?php
				echo "<div class='editor content' contentEditable='true' id='editor_for_{$this->name}'>{$this->default}</div>";
				echo "<h6 class='editor_raw'>Raw Markup</h6>";
				echo "<textarea value='' maxlength={$this->maxlength} minlength={$this->minlength} class='filter_{$this->filter} input editor_raw' {$required} type='text' id='{$this->id}' {$this->get_rendered_name()}>{$this->default}</textarea>";
			echo "</div>";
			if ($this->description) {
				echo "<p class='help'>" . $this->description . "</p>";
			}
		echo "</div>";
	}

	public function set_from_submit() {
		$value = Input::getvar($this->name, $this->filter);
		if (is_string($value)||is_numeric($value)) {
			// strip empty tags / nonsense made by ol/ul creation
			// they are illegal html ;) but they are
			// produced by UL/OL creation in contenteditable
			$value = str_replace("<p></p>","",$value);
			$value = str_replace("<p><ul>","<ul>",$value);
			$value = str_replace("</ul></p>","</ul>",$value);
			$this->default = $value;
		}
	}

	public function load_from_config($config) {
		parent::load_from_config($config);
		
		//this space is inbetween the the p tags specifically to prevent the set_from_submit stripping out the default p
		$this->default = $config->default ?? '<p> </p>';
	}

	public function validate() {
		// TODO: enhance validation
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}