<?php
defined('CMSPATH') or die; // prevent unauthorized access

class Image {
	public $id;
    public $filename;
    public $width;
    public $height;
    public $title;
    public $alt;
    public $mimetype;
    public static $image_sizes = [
		"thumb"=>200,
		"web"=>1920
	];
    // modified also available, but almost certainly never needed front-end
    
    public function __construct($id) {
        if (!is_numeric($id)) {
            CMS::Instance()->show_error('Cannot create image object from non-numerical id');
        }
        else {
            $this->id = $id;
            $db_image = DB::fetch('select * from media where id=?', $this->id);
            $this->filename = $db_image->filename;
            $this->width = $db_image->width;
            $this->height = $db_image->height;
            $this->title = $db_image->title;
            $this->alt = $db_image->alt;
            $this->mimetype = $db_image->mimetype;
        }       
    }
    
    public function render($size="", $class="", $output_immediately=true, $attributes=[]) {
        // size and class used in v <= 2.4.77
        // $w attribute supercedes $size
        // kept for back compat - class param and class passed via attribute are combined
        // handle attributes
        $class = $class . " " . ($attributes['class'] ?? ''); 
        $w = $attributes['w'] ?? null;
        if (!$w) {
            if ($size && !is_numeric($size)) {
                // no w attribute, string size - figure out or default to 1920
                $w = $this->image_sizes[$size] ?? '1920';
            }
            elseif ($size) {
                // no $w attr, but got numeric $size
                $w = $size;
            }
            else {
                $w = $this->width; // default to og size if no $size or $w attr
            }
        }
        $q = $attributes['q'] ?? null;
        $fmt = $attributes['fmt'] ?? null;
        $loading = $attributes['loading'] ?? "lazy"; // use eager for headings
        $width_param = $this->width;
        $height_param = $this->height;
        if ($w && is_numeric($w)) {
            if ($w < $this->width) {
                $width_param = $w;
                // scale height
                $height_param = floor(($w/$this->width)*$this->height);
            }
        }

        // build url
        $url_domain_path = Config::uripath() . "/image/" . $this->id . "?";
        $url_params = [];
        if ($w) {$url_params['w'] = $w; }
        if ($q) {$url_params['q'] = $q; }
        if ($fmt) {$url_params['fmt'] = $fmt; }
        $url_params_string = http_build_query($url_params);
        $url = $url_domain_path . $url_params_string;
        $markup = "<img decode='async' width='{$width_param}' height='{$height_param}' loading='{$loading}' class='rendered_img {$class}' src='".$url."' alt='{$this->alt}' title='{$this->title}'/>";
        if ($output_immediately) {
            echo $markup;
        }
        else {
            return $markup;
        }
    }

    public static function add_image_js_editor() {
        ?>
            <style>
                #active_editing_image {
                    display: block;
                    max-width: 100%;
                }

                .cropper_controls {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 1rem;
                    justify-content: center;
                }

                .buttons.has-addons .button:not(:last-child) {
                    border-right: 2px solid rgb(0 0 0 / 25%);
                }

                .cropper_controls .buttons {
                    margin-bottom: 1rem;
                }
                .cropper_controls i {
                    pointer-events: none;
                }
            </style>
            <script>
                window.load_img_editor = function(id) {
                    return new Promise(resolve => {
                        let markup = `
                            <div class="modal-background"></div>
                            <div class="modal-card">
                                <header class="modal-card-head">
                                    <p class="modal-card-title">Image Editor</p>
                                    <button onclick="close_img_editor()" class="delete" aria-label="close"></button>
                                </header>
                                <section class="modal-card-body">
                                    <div>
                                        <img id="active_editing_image" src="/image/${id}">
                                    </div>
                                    <br>
                                    <div class="cropper_controls">
                                        <div class="buttons has-addons">
                                            <button data-action="rotate_left" class="button is-info"><i class='fa fa-rotate-left'></i></button>
                                            <button data-action="rotate_right" class="button is-info"><i class='fa fa-rotate-right'></i></button>
                                        </div>
                                        <div class="buttons has-addons">
                                            <button data-action="zoom_in" class="button is-info"><i class='fa fa-plus'></i></button>
                                            <button data-action="zoom_out" class="button is-info"><i class='fa fa-minus'></i></button>
                                        </div>
                                        <div class="buttons has-addons">
                                            <button data-action="reset" class="button is-info"><i class='fa fa-retweet'></i></button>
                                        </div>
                                        <div class="buttons has-addons">
                                            <button data-action="ar_16_9" class="button is-info">16:9</button>
                                            <button data-action="ar_4_3" class="button is-info">4:3</button>
                                            <button data-action="ar_1_1" class="button is-info">1:1</button>
                                            <button data-action="ar_2_3" class="button is-info">2:3</button>
                                            <button data-action="ar_free" class="button is-info">Free</button>
                                        </div>
                                    </div>
                                </section>
                                <footer class="modal-card-foot">
                                    <button onclick="save_img_editor()" class="button is-success">Save</button>
                                    <button onclick="close_img_editor()" class="button cancel">Cancel</button>
                                </footer>
                            </div>
                        `;

                        //create and add modal to page
                        let modal = document.createElement("div");
                        modal.id="image_editor";
                        modal.classList.add("modal");
                        modal.classList.add("is-active");
                        modal.innerHTML = markup;
                        document.body.appendChild(modal);

                        const cropper = new Cropper(document.getElementById('active_editing_image'), {
                            initialAspectRatio: 16 / 9, //todo: maybe make this match current image size?
                            aspectRatio: NaN, //allows us to use initial aspect ratio
                            viewMode: 1, //prevent them from having dead space
                            /*crop(event) {
                                console.log(event.detail.x);
                                console.log(event.detail.y);
                                console.log(event.detail.width);
                                console.log(event.detail.height);
                                console.log(event.detail.rotate);
                                console.log(event.detail.scaleX);
                                console.log(event.detail.scaleY);
                            }, */
                        });

                        //make controls work
                        modal.querySelector(".cropper_controls").addEventListener("click", (e)=>{
                            let actions = {
                                "rotate_left": function() {cropper.rotate(-45/2)},
                                "rotate_right": function() {cropper.rotate(45/2)},
                                "zoom_in": function() {cropper.zoom(0.1)},
                                "zoom_out": function() {cropper.zoom(-0.1)},
                                "reset": function() {cropper.reset()},
                                "ar_16_9": function() {cropper.setAspectRatio(16/9)},
                                "ar_4_3": function() {cropper.setAspectRatio(4/3)},
                                "ar_1_1": function() {cropper.setAspectRatio(1/1)},
                                "ar_2_3": function() {cropper.setAspectRatio(2/3)},
                                "ar_free": function() {cropper.setAspectRatio(NaN)},
                            }
                            //console.log(e.target);
                            if(e.target.dataset.action && actions[e.target.dataset.action]) {
                                actions[e.target.dataset.action]();
                            }
                        });

                        //close logic
                        window.close_img_editor = function() {
                            document.getElementById('image_editor').remove();
                            resolve(0);
                        };

                        //util func
                        function dataURLtoFile(dataurl, filename) {
 
                            var arr = dataurl.split(','),
                                mime = arr[0].match(/:(.*?);/)[1],
                                bstr = atob(arr[1]), 
                                n = bstr.length, 
                                u8arr = new Uint8Array(n);
                                
                            while(n--){
                                u8arr[n] = bstr.charCodeAt(n);
                            }
                            
                            return new File([u8arr], filename, {type:mime});
                        }

                        //save logic
                        window.save_img_editor = function() {
                            //console.log(cropper.getCroppedCanvas().toDataURL());

                            /* cropper.getCroppedCanvas().toBlob((blob) => {
                                resolve(blob);
                            }); */
                            resolve(dataURLtoFile(cropper.getCroppedCanvas().toDataURL('image/png'), "test.png"));
                        }
                    });
                }
            </script>
        <?php
    }
}
