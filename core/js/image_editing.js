export function loadImgEditor(id) {
    return new Promise(resolve => {
        let markup = `
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
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <p class="modal-card-title">Image Editor</p>
                    <button onclick="close_img_editor()" class="delete" aria-label="close"></button>
                </header>
                <section class="modal-card-body">
                    <div>
                        <img id="active_editing_image" src="${window.uripath}/image/${id}">
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
            resolve(dataURLtoFile(cropper.getCroppedCanvas().toDataURL('image/png'), "test.png"));
        }
    });
}