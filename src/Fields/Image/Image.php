<?php
namespace HoltBosse\Alba\Fields\Image;

Use HoltBosse\Form\{Field, FormBuilderAttribute, FormBuilderDataType};
Use HoltBosse\Alba\Core\{CMS, File, Image as CmsImage, Hook};
Use HoltBosse\DB\DB;
Use HoltBosse\Alba\Components\CssFile\CssFile;

class Image extends Field {

	public $select_options;
	public $mimetypes;
	public $image_id;
	public $images_per_page;
	public $tags;
	public $coltype;
	public $upload_endpoint;
	public $listing_endpoint;
	#[FormBuilderAttribute(fieldType: "Select", dataType: FormBuilderDataType::Bool, required: true, description: "Is this field publicly accessible on the front-end?")]
	public bool $public_accessible = false;

	public function display($repeatable_template=false) {
		if(!$this->public_accessible) {
			$this->trustedDisplay($repeatable_template);
		} else {
			$this->unTrustedDisplay($repeatable_template);
		}
	}

	public function unTrustedDisplay($repeatable_template=false) {
		$_SESSION["public_accessible_image_field_loaded"] = true;

		(new CssFile())->loadFromConfig((object)[
            "filePath"=>__DIR__ . "/untrusted.css",
            "injectIntoHead"=>false,
        ])->display();
		
		echo "<div class='field " . ($this->required ? "required" : "") . "'>";
			echo "<label class='label'>" . $this->label . "</label>";
		echo "</div>";
		echo "<div>";
			echo "<div class='image_dropzone'><span style='pointer-events: none;'>Drag & Drop New Images Here</span></div>";
			echo "<input type='hidden' oninvalid='this.setCustomValidity(\"A valid image is required\")' value='{$this->default}' " . ($this->required ? " required " : "") . " id='{$this->id}' {$this->getRenderedName()} {$this->getRenderedForm()} data-repeatableindex='{{replace_with_index}}'>";
		echo "</div>";
		?>
			<script type="module">
				const imageInput = document.querySelector(`[<?php echo $this->getRenderedName(); ?>][data-repeatableindex="{{replace_with_index}}"]`);
				const dropZone = imageInput.parentNode.querySelector(".image_dropzone");

				dropZone.addEventListener("dragover", (e) => {
					e.preventDefault();

					if(e.target.classList.contains("submission_complete")) {
						return;
					}

					dropZone.classList.add("dragover");
				});
				dropZone.addEventListener("dragleave", (e) => {
					e.preventDefault();

					if(e.target.classList.contains("submission_complete")) {
						return;
					}

					dropZone.classList.remove("dragover");
				});
				dropZone.addEventListener("drop", (e) => {
					e.preventDefault();
					dropZone.classList.remove("dragover");

					if(e.target.classList.contains("submission_complete")) {
						return;
					}

					const files = e.dataTransfer.files;
					if (files.length > 0) {
						const formData = new FormData();
						for (let i = 0; i < files.length; i++) {
							formData.append("file-upload[]", files[i]);
						}
						function updateField() {
							dropZone.classList.add("submission_complete");
							dropZone.querySelector("span").innerHTML = "Upload Complete!";
						}

						fetch('<?php echo $this->upload_endpoint; ?>', {
							method: "POST",
							body: formData,
						}).then((response) => response.json()).then((data) => {
							console.log(data);
							imageInput.value = data.ids;
							updateField();
						}).catch((error) => {
							console.error("Error uploading image:", error);
							updateField();
						});
					}
				});
			</script>
		<?php
	}

	public function trustedDisplay($repeatable_template=false) {
		echo "<script>";
			echo "window.max_upload_size_bytes = " . File::get_max_upload_size_bytes() . ";";
		echo "</script>";

		// repeatable template boolean initiated in Repeatable.php if inside repeatable form

		$required="";
		if ($this->required) {$required=" required ";}
		// if id needs to be unique for scripting purposes, make sure replacement text inserted
		// this will be replaced during repeatable template literal js injection when adding new
		// repeatable form item
		if ($this->in_repeatable_form===null) {
			$repeatable_id_suffix='';
		}
		else {
			if ($repeatable_template) {
				$repeatable_id_suffix='{{repeatable_id_suffix}}'; // injected via JS at repeatable addition time
			}
			else {
				$repeatable_id_suffix = "_" . uniqid();
			}
			$this->id = $this->id . $repeatable_id_suffix;
		}

		echo "<hr class='image_field_hr image_field_top'>";

		echo "<div class='field " . ($this->required ? "required" : "") . "'>";
			echo "<label class='label'>" . $this->label . "</label>";
		echo "</div>";

		echo "<p>Selected Image</p>";
		if ($this->default) {
			$active = ' active ';
		}
		else {
			$active = '';
		}
		$previewsrc = $this->default;
		$previewalt = "";
		$previewtitle = "";
		if(is_numeric($this->default)) {
			$previewimagedetails = DB::fetch("SELECT * FROM media WHERE id=?", $this->default);
			
			$previewsrc = $_ENV["uripath"] . '/image/' . $this->default . "/thumb";
			$previewalt = $previewimagedetails->alt;
			$previewtitle = $previewimagedetails->title;
		}

		$previewsrc = is_numeric($this->default) ? $_ENV["uripath"] . '/image/' . $this->default . "/thumb" : $this->default;
		echo "<div class='selected_image_wrap {$active}' id='selected_image_{$this->id}'><p>No Image Selected</p><img alt='$previewalt' title='$previewtitle' style='max-width: 20rem; max-height: 20rem;' src='$previewsrc' id='image_selector_chosen_preview_{$this->id}'?></div>";
		

		echo "<div style='display: flex; gap: 0.25rem; flex-wrap: wrap;'>";
			ob_start();
				echo "<button type='button' id='trigger_image_selector_{$this->id}' class='button btn is-primary'>Choose New Image</button>";
				echo "<button type='button' id='trigger_image_crop_{$this->id}' class='button btn is-primary'>Crop Image</button>";
				echo "<button type='button' id='trigger_image_upload_{$this->id}' class='button btn is-info is-light upload_new_image_button'>Upload New Image</button>";
			$imageButtons = ob_get_clean();
			echo Hook::execute_hook_filters('render_image_field_buttons', $imageButtons, $this);
			echo "<button id='trigger_image_clear_{$this->id}' type='button' onclick='(function() { let e=document.getElementById(\"selected_image_" . $this->id . "\");  let wr=e.closest(\".selected_image_wrap\"); let input=document.getElementById(\"" . $this->id . "\"); input.value=\"\"; wr.classList.remove(\"active\"); console.log(e);})(); return false; '  class='button btn is-warning'>Clear</button>";
		echo "</div>";
		echo "<input oninvalid='this.setCustomValidity(\"A valid image is required\")' style='position:absolute; width:0px; opacity:0;' value='{$this->default}' {$required} id='{$this->id}' {$this->getRenderedName()} {$this->getRenderedForm()}>";
		
		
		
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}


		echo "<hr class='image_field_hr image_field_bottom'>";

		$cropperCss = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1/dist/cropper.min.css"/>';
		if(!in_array($cropperCss, CMS::Instance()->head_entries)) {
			CMS::Instance()->head_entries[] = $cropperCss;
		}
		?>

		<script type="module">
		import {addImageUploadDialog} from "/js/image_uploading.js?v=1";
		import {loadImgEditor} from "/js/image_editing.js";
		import {openMediaSelector} from "/js/media_selector.js?v=2";

		
		document.getElementById("trigger_image_crop_<?php echo $this->id; ?>").addEventListener("click", (e)=>{
			let img_wrapper = document.getElementById("selected_image_<?php echo $this->id; ?>");
			if(!img_wrapper.closest(".selected_image_wrap").classList.contains("active")) {
				alert("no image selected");
				return false;
			}
			let imageUrlChunks = img_wrapper.querySelector("img").getAttribute("src").split("/");
			imageUrlChunks = imageUrlChunks.filter((el)=>{return el!="";});
			if(imageUrlChunks[imageUrlChunks.length-1]=="thumb") {
				imageUrlChunks.pop();
			}
			let id = imageUrlChunks[imageUrlChunks.length-1];

			async function handle_img_editor() {
				const result = await loadImgEditor(id);
				//console.log(result);

				if(result != 0) {
					let preview = document.getElementById('image_selector_chosen_preview_<?php echo $this->id; ?>');

					document.getElementById("image_editor").querySelector(".modal-card-body").innerHTML = `<p>Uploading Edit to the Server. Please Wait ....</p>`;
					document.getElementById("image_editor").querySelector(".modal-card-foot").innerHTML = "";
					console.log(result);
					const formData = new FormData();
					formData.append("file-upload[]", result);
					formData.append("alt[]", [preview.alt]);
					formData.append("title[]", [preview.title]);

					fetch('<?php echo $_ENV["uripath"]; ?>/admin/images/uploadv2', {
						method: "POST",
						body: formData,
					}).then((response) => response.json()).then((data) => {
						console.log(data);
						img_wrapper.querySelector("img").setAttribute("src", "/image/"+data.ids);
						document.getElementById("<?php echo $this->id; ?>").value=data.ids;
						document.getElementById("image_editor").remove();
						//window.location.reload();
					});
				}
			}

			handle_img_editor();
		});

		document.getElementById("trigger_image_upload_<?php echo $this->id; ?>").addEventListener("click", (e)=>{
			window.image_upload_el = "<?php echo $this->id; ?>";
			window.upload_endpoint = "<?php echo $this->upload_endpoint; ?>";
			addImageUploadDialog();
		});

		// get variables for openMediaSelector()
		let elementId = "<?php echo $this->id; ?>";
		let imagesPerPage = <?php echo $this->images_per_page; ?>;
		let mimetypes = <?php echo json_encode($this->mimetypes); ?>;
		let tags = <?php echo json_encode($this->tags);?>;
		let listingEndpoint = "<?php echo $this->listing_endpoint; ?>";

		document.getElementById('trigger_image_selector_' + elementId).addEventListener('click', e => {
			// open media selector (choose new image)
			const mediaSelector = openMediaSelector(elementId, imagesPerPage, mimetypes, tags, listingEndpoint);
			mediaSelector.addEventListener("mediaItemSelected", (mediaE) => {
				const preview = document.getElementById(`image_selector_chosen_preview_${elementId}`);
				const url = mediaE.detail.hasImageUrl ? mediaE.detail.url : `${mediaE.detail.url}/thumb`;
				preview.src = url;
				preview.alt = mediaE.detail.alt;
				preview.title = mediaE.detail.title;
				preview.closest('.selected_image_wrap').classList.add('active');

				const hiddenInput = document.getElementById(elementId);
				hiddenInput.setCustomValidity('');
				hiddenInput.value = mediaE.detail.hasImageUrl ? url : mediaE.detail.mediaId;	
			});
		});
		</script>
	<?php
	} // end display

	public function getFriendlyValue($helpful_info) {
		if($helpful_info && $helpful_info->return_in_text_form==true) {
			if (is_numeric($this->default)) {
				return "https://" . $_SERVER["HTTP_HOST"] . "/image/" . $this->default;
			} else {
				return "No Image";
			}
		} else {
			if (is_numeric($this->default)) {
				if($this->public_accessible) {
					if($helpful_info->return_in_email_html_form ?? false) {
						return "Please visit admin to view the image.";
					}

					$image = DB::fetch("SELECT * FROM media WHERE id=?", $this->default);

					//this images are uploaded to state 0, also prevents xss
					$img_data = base64_encode(file_get_contents($_ENV["images_directory"] . "/processed/" . $image->filename));
					$img_src = "data:" . $image->mimetype . ";base64," . $img_data;
					echo "<img src='" . $img_src . "'>";
				} else {
					$img = new CmsImage($this->default);
					return $img->render('thumb','backend', false);
				}
			}
			else {
				return "<span>No Image</span>";
			}
		}
	}


	public function loadFromConfig($config) {
		parent::loadFromConfig($config);
		
		$this->coltype = $config->coltype ?? '';
		$this->mimetypes = $config->mimetypes ?? null;
		$this->images_per_page = $config->images_per_page ?? 50;
		$this->tags = $config->tags ?? null;
		$this->public_accessible = $config->public_accessible ?? false;
		$this->upload_endpoint = $config->upload_endpoint ?? ($config->public_accessible ? $_ENV["uripath"] . "/image/frontend_upload" : $_ENV["uripath"] . "/admin/images/uploadv2");
		$this->listing_endpoint = $config->listing_endpoint ?? $_ENV["uripath"] . "/image/list_images";

		return $this;
	}

	public function validate() {
		if ($this->isMissing()) {
			return false;
		}
		return true;
	}
}