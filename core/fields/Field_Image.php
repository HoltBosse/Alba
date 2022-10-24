<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Field_Image extends Field {

	public $select_options;

	function __construct($id="") {
		$this->id = $id;
		$this->name = $id;
		$this->image_id = null;
		$this->default = null;
	}

	public function display($repeatable_template=false) {

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
		echo "<div class='selected_image_wrap {$active}' id='selected_image_{$this->id}'><p>No Image Selected</p><img  src='".Config::uripath() . '/image/' . $this->default ."/thumb' id='image_selector_chosen_preview_{$this->id}'?></div>";
		

		echo "<button type='button' id='trigger_image_selector_{$this->id}' class='button btn is-primary'>Choose New Image</button>";
		echo "&nbsp;<a href='" . Config::uripath() . "/admin/images/show?filter=upload' target='_blank' type='button' id='trigger_image_upload_{$this->id}' class='button btn is-small is-info is-light'>Upload New Image</a>";
		echo "<button type='button' onclick='(function() { let e=document.getElementById(\"selected_image_" . $this->id . "\");  let wr=e.closest(\".selected_image_wrap\"); let input=document.getElementById(\"" . $this->id . "\"); input.value=\"\"; wr.classList.remove(\"active\"); console.log(e);})(); return false; '  class='button btn is-warning'>Clear</button>";	
		
		
		
		echo "<input type='hidden' value='{$this->default}' {$required} id='{$this->id}' {$this->get_rendered_name()}>";
		if ($this->description) {
			echo "<p class='help'>" . $this->description . "</p>";
		}


		echo "<hr class='image_field_hr image_field_bottom'>";


		?>

		

		<script>

		
	

		// choose new image button event listener
		var trigger_image_selector_<?php echo $this->id; ?> = document.getElementById('trigger_image_selector_<?php echo $this->id;?>');
		trigger_image_selector_<?php echo $this->id; ?>.addEventListener('click',function(e){
			// launch image selector
			var media_selector = document.createElement('div');
			media_selector.id = "media_selector";
			media_selector.innerHTML =`
			<div class='media_selector_modal' style='position:fixed;width:100vw;height:100vh;background:black;padding:1em;left:0;top:0;z-index:99;'>
				<div style='display:flex; gap:1rem; margin:2rem; position:sticky; top:0px;'>
					<button id='media_selector_modal_close' class="modal-close is-large" aria-label="close"></button>
					<h1 style='color:white;'>Click image or search: </h1>
					<div class='form-group' style='display:flex; gap:2rem;'>
						<input id='media_selector_modal_search'/>
						<button class='button btn is-small is-primary' type='button' id='trigger_media_selector_search'>Search</button>
						<button class='button btn is-small' type='button' id='clear_media_selector_search'>Clear</button>
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
				fetch_images(searchtext, null); // string, no tags
			});
			// press return
			document.getElementById('media_selector_modal_search').addEventListener('keyup',function(e){
				if (e.key==="Enter") {
					var searchtext = document.getElementById('media_selector_modal_search').value;
					fetch_images(searchtext, null); // string, no tags
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
				fetch_images(null, null); // string, no tags
			});

			fetch_images (null, null); // no search, all tags

			function fetch_images(searchtext, taglist) {
			
				// fetch images
				postAjax('<?php echo Config::uripath();?>/admin/images/api', {"action":"list_images","searchtext":searchtext}, function(data) { 
					var image_list = JSON.parse(data);
					var image_list_markup = "<ul class='media_selector_list single'>";
					if (image_list.images.length==0) {
						image_list_markup += `<li style='display:block; width:100%;'><h5 class='is-5 title' style='text-align:center;'>No images found - please try another search</h2></li>`;
					}
					image_list.images.forEach(image => {
						image_list_markup += `
						<li>
							<a class='media_selector_selection' data-id='${image.id}'>
							<img title='${image.title}' alt='${image.alt}' src='<?php echo Config::uripath();?>/image/${image.id}/thumb'>
							<span>${image.title}</span>
							</a>
						</li>`;
					});
					image_list_markup += "</ul>";
					media_selector.querySelector('.media_selector').innerHTML = image_list_markup;
					// handle click close
					document.getElementById('media_selector_modal_close').addEventListener('click',function(e){
						var modal = e.target.closest('.media_selector_modal');
						modal.parentNode.removeChild(modal);
					});
					
					// add click event handler to capture child selection clicks
					media_selector.addEventListener('click',function(e){
						//console.log(e.target);
						e.preventDefault();
						e.stopPropagation();
						var selected_image = e.target.closest('.media_selector_selection');
						if (selected_image!==null) {
							var media_id = selected_image.dataset.id;
							var url = `<?php echo Config::uripath();?>/image/${media_id}/web`;
							var image_markup = `<img class="rich_image" data-media_id="${media_id}" data-size="web" src="${url}"/>`;
							console.log(image_markup);
							// this is only for rich editor
							//document.execCommand('insertHTML',false, image_markup);
							var modal = selected_image.closest('.media_selector_modal');
							modal.parentNode.removeChild(modal);

							// this is only for image field class
							var preview = document.getElementById('image_selector_chosen_preview_<?php echo $this->id; ?>');
							preview.src = '<?php echo Config::uripath() . '/image/';?>' + media_id + '/thumb/';
							preview.closest('.selected_image_wrap').classList.add('active');

							hidden_input = document.getElementById('<?php echo $this->id;?>');
							hidden_input.value = media_id;

						} // else clicked on container not on an anchor or it's children
					});
				});
			}
		});
		</script>
		<?php
		if ($this->in_repeatable_form===null) {
			//echo "</script>"; // no need anymore
		}
		
	}

	public function get_friendly_value() {
		if (is_numeric($this->id)) {
			$img = new Image($this->id);
			$img->render('thumb','backend');
		}
		else {
			echo "<span>No Image</span>";
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
	}

	public function validate() {
		if ($this->is_missing()) {
			return false;
		}
		return true;
	}
}