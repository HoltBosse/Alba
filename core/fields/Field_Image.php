<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Image extends Field {

	public $select_options;
	public $mimetypes;
	public $image_id;
	public $images_per_page;
	public $tags;
	public $coltype;
	public $upload_endpoint;
	public $listing_endpoint;

	function __construct($id="") {
		$this->id = $id;
		$this->name = $id;
		$this->image_id = null;
		$this->default = null;
		$this->mimetypes = null;
	}

	public function display($repeatable_template=false) {
		//add the image editor
		Image::add_image_js_editor();
		Image::add_image_upload_dialog();
		echo "<script>";
			echo "window.max_upload_size_bytes = " . File::get_max_upload_size_bytes() . ";";
			echo file_get_contents(CMSPATH . "/admin/controllers/images/views/show/image_upload_handling.js");
		echo "</script>";

		// repeatable template boolean initiated in Field_Repeatable.php if inside repeatable form

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

		echo "<label class='label'>" . $this->label . "</label>";

		echo "<p>Selected Image</p>";
		if ($this->default) {
			$active = ' active ';
		}
		else {
			$active = '';
		}
		$previewsrc = is_numeric($this->default) ? Config::uripath() . '/image/' . $this->default . "/thumb" : $this->default;
		echo "<div class='selected_image_wrap {$active}' id='selected_image_{$this->id}'><p>No Image Selected</p><img style='max-width: 20rem; max-height: 20rem;' src='$previewsrc' id='image_selector_chosen_preview_{$this->id}'?></div>";
		

		echo "<button type='button' id='trigger_image_selector_{$this->id}' class='button btn is-primary'>Choose New Image</button>";
		echo "<button type='button' id='trigger_image_crop_{$this->id}' class='button btn is-primary'>Crop Image</button>";
		//href='{$this->upload_endpoint}'
		echo "<button type='button' id='trigger_image_upload_{$this->id}' class='button btn is-info is-light upload_new_image_button'>Upload New Image</a>";
		echo "<button type='button' onclick='(function() { let e=document.getElementById(\"selected_image_" . $this->id . "\");  let wr=e.closest(\".selected_image_wrap\"); let input=document.getElementById(\"" . $this->id . "\"); input.value=\"\"; wr.classList.remove(\"active\"); console.log(e);})(); return false; '  class='button btn is-warning'>Clear</button>";	
		echo "<input oninvalid='this.setCustomValidity(\"A valid image is required\")' style='position:absolute; width:0px; opacity:0;' value='{$this->default}' {$required} id='{$this->id}' {$this->get_rendered_name()}>";
		
		
		
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}


		echo "<hr class='image_field_hr image_field_bottom'>";


		?>

		

		<script>

		
		document.getElementById("trigger_image_crop_<?php echo $this->id; ?>").addEventListener("click", (e)=>{
			let img_wrapper = document.getElementById("selected_image_<?php echo $this->id; ?>");
			if(!img_wrapper.closest(".selected_image_wrap").classList.contains("active")) {
				alert("no image selected");
				return false;
			}
			let id = img_wrapper.querySelector("img").getAttribute("src").split("/")[2];

			async function handle_img_editor() {
				const result = await window.load_img_editor(id);
				//console.log(result);

				if(result != 0) {
					document.getElementById("image_editor").querySelector(".modal-card-body").innerHTML = `<p>Uploading Edit to the Server. Please Wait ....</p>`;
					document.getElementById("image_editor").querySelector(".modal-card-foot").innerHTML = "";
					console.log(result);
					const formData = new FormData();
					formData.append("file-upload[]", result);
					formData.append("alt[]", ["system cropped image"]);
					formData.append("title[]", ["system cropped image"]);
					formData.append("web_friendly[]", [0]);

					fetch('<?php echo Config::uripath(); ?>/admin/images/uploadv2', {
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
			window.load_img_uploader();
		});
	
		window.cur_media_page = 1;
		window.cur_media_searchtext = null;
		window.images_per_page = <?php echo $this->images_per_page; ?>;

		// choose new image button event listener
		var trigger_image_selector_<?php echo $this->id; ?> = document.getElementById('trigger_image_selector_<?php echo $this->id;?>');
		trigger_image_selector_<?php echo $this->id; ?>.addEventListener('click',function(e){
			// launch image selector
			var media_selector = document.createElement('div');
			media_selector.id = "media_selector";
			media_selector.classList.add("media_selector_for_<?php echo $this->id; ?>");
			media_selector.innerHTML =`
			<div class='media_selector_modal' style='position:fixed;width:100vw;height:100vh;background:black;padding:1em;left:0;top:0;z-index:99;'>
				<div style='display:flex; gap:1rem; margin:2rem; position:sticky; top:0px;'>
					<button style="right: 1rem;" id='media_selector_modal_close' class="modal-close is-large" aria-label="close"></button>
					<h1 style='color:white;'>Click image or search: </h1>
					<div class='form-group' style='display:flex; gap:2rem;'>
						<input id='media_selector_modal_search'/>
						<button class='button btn is-small is-primary' type='button' id='trigger_media_selector_search'>Search</button>
						<button class='button btn is-small' type='button' id='clear_media_selector_search'>Clear</button>
						|
						<button class='button btn is-small is-info' disabled id='prev_page'>Prev Page</button>
						<button class='button btn is-small is-info' id='next_page'>Next Page</button>
					</div>
				</div>
				<div class='media_selector'><h2>LOADING</h2></div>
			</div>
			`;
			document.body.appendChild(media_selector); 

			// todo: DRY below two event listeners
			//click button
			document.getElementById('trigger_media_selector_search').addEventListener('click',function(e){
				var searchtext = document.getElementById('media_selector_modal_search').value;
				window.cur_media_page = 1;
				window.cur_media_searchtext = searchtext ?? null;
				fetch_images(searchtext); // string, no tags
			});
			// press return
			document.getElementById('media_selector_modal_search').addEventListener('keyup',function(e){
				if (e.key==="Enter") {
					window.cur_media_page = 1;
					var searchtext = document.getElementById('media_selector_modal_search').value;
					window.cur_media_searchtext = searchtext ?? null;
					fetch_images(searchtext); // string, no tags
				}
			});
			document.addEventListener('keyup',function(e){
				let media_selector = document.getElementById('media_selector');
				if (media_selector) {
					if (e.key=="Escape") {
						media_selector.parentNode.removeChild(media_selector);
					}
				}
			});
			// handle clear
			document.getElementById('clear_media_selector_search').addEventListener('click',function(e){
				document.getElementById('media_selector_modal_search').value="";
				window.cur_media_searchtext = null;
				window.cur_media_page = 1;
				fetch_images(); // string, no tags, num pages, always page 1
			});
			// handle pages
			document.getElementById('next_page').addEventListener('click',function(e){
				window.cur_media_page++;
				fetch_images(window.cur_media_searchtext);
			});
			document.getElementById('prev_page').addEventListener('click',function(e){
				window.cur_media_page--;
				if (window.cur_media_page==0) {
					window.cur_media_page=1;
					document.getElementById('prev_page').setAttribute('disabled',true);
				}
				fetch_images(window.cur_media_searchtext);
			});

			fetch_images (); // no search, all tags

			function fetch_images(searchtext=null, taglist=null) {

				let fetchParams = {
					"action":"list_images",
					"page":window.cur_media_page,
					"images_per_page":<?php echo $this->images_per_page;?>,
					"searchtext":searchtext
					<?php echo $this->mimetypes ? ',"mimetypes":' . json_encode($this->mimetypes) : "";?>
					<?php echo $this->tags ? ',"tags": "' . "$this->tags" . '"' : "";?>
				};

				let fetchFormData = new FormData();
				Object.keys(fetchParams).forEach(key => fetchFormData.append(key, fetchParams[key]));
			
				// fetch images
				fetch('<?php echo $this->listing_endpoint;?>',
					{
						method: "POST",
						body: fetchFormData,
					}
				).then((res)=>res.json()).then((data)=>{
					console.log(data);
					var image_list = data;//JSON.parse(data);
					var image_list_markup = "<ul class='media_selector_list single'>";
					if (image_list.images.length==0) {
						image_list_markup += `<li style='display:block; width:100%;'><h5 class='is-5 title' style='text-align:center;'>No images found - please try another search</h2></li>`;
					}
					image_list.images.forEach(image => {
						let datasetattribute = image.imageurl ? " data-hasimageurl='true'" : "";
						image_list_markup += `
						<li>
							<a class='media_selector_selection' data-id='${image.id}'>
							<img title='${image.title}' alt='${image.alt}' ${datasetattribute} src='${image.imageurl ? image.imageurl : `<?php echo Config::uripath();?>/image/${image.id}/thumb`}'>
							<span>${image.title}</span>
							</a>
						</li>`;
					});
					image_list_markup += "</ul>";
					media_selector.querySelector('.media_selector').innerHTML = image_list_markup;
					// handle click close
					document.getElementById('media_selector_modal_close').addEventListener('click',(e)=>{
						e.target.closest("#media_selector").remove();
					});

					// update page buttons
					if (image_list.images.length < window.images_per_page) {
						document.getElementById('next_page').setAttribute('disabled',true); 
					}
					else {
						document.getElementById('next_page').removeAttribute('disabled');
					}
					if (window.cur_media_page==1) {
						document.getElementById('prev_page').setAttribute('disabled',true);
					}
					else {
						document.getElementById('prev_page').removeAttribute('disabled');
					}
					
					// add click event handler to capture child selection clicks
					media_selector.addEventListener('click',function(e){
						//console.log(e.target);
						e.preventDefault();
						e.stopPropagation();
						var selected_image = e.target.closest('.media_selector_selection');
						if (selected_image!==null) {
							console.log(e.target);
							var media_id = selected_image.dataset.id;
							var url = e.target.dataset.hasimageurl ? e.target.src  : `<?php echo Config::uripath();?>/image/${media_id}/thumb/`;

							var modal = selected_image.closest('.media_selector_modal');
							modal.parentNode.removeChild(modal);

							// this is only for image field class
							var preview = document.getElementById('image_selector_chosen_preview_<?php echo $this->id; ?>');
							preview.src = url;
							preview.closest('.selected_image_wrap').classList.add('active');

							hidden_input = document.getElementById('<?php echo $this->id;?>');
							hidden_input.setCustomValidity('');
							hidden_input.value = e.target.dataset.hasimageurl ? url : media_id;

						} // else clicked on container not on an anchor or it's children
					});
				}).catch((error) => {
					console.log(error);
				});
			}
		});
		</script>
		<?php
		if ($this->in_repeatable_form===null) {
			//echo "</script>"; // no need anymore
		}
		
	}

	public function get_friendly_value($helpful_info) {
		if (is_numeric($this->default)) {
			$img = new Image($this->default);
			return $img->render('thumb','backend', false);
		}
		else {
			return "<span>No Image</span>";
		}
	}


	public function load_from_config($config) {
		//CMS::pprint_r ($config);
		$this->name = $config->name ?? 'error!!!';
		$this->id = $config->id ?? $this->name;
		$this->label = $config->label ?? '';
		$this->required = $config->required ?? false;
		$this->description = $config->description ?? '';
		$this->filter = $config->filter ?? 'NUMBER';
		$this->missingconfig = $config->missingconfig ?? false;
		$this->default = $config->default ?? null;
		$this->type = $config->type ?? 'error!!!';
		$this->logic = $config->logic ?? '';
		$this->coltype = $config->coltype ?? '';
		$this->mimetypes = $config->mimetypes ?? null;
		$this->images_per_page = $config->images_per_page ?? 50;
		$this->tags = $config->tags ?? null;
		$this->upload_endpoint = $config->upload_endpoint ?? Config::uripath() . "/admin/images/uploadv2";
		$this->listing_endpoint = $config->listing_endpoint ?? Config::uripath() . "/image/list_images";
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}