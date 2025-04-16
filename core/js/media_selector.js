export function open_media_selector(element_id, images_per_page, mimetypes, tags, listing_endpoint, from_richtext=false) {
    let cur_media_page = 1;
    let cur_media_searchtext = null;

    // launch image selector
    const media_selector = document.createElement('div');
    media_selector.id = "media_selector";
    media_selector.classList.add(`media_selector_for_${element_id}`);
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

    let last_editor = null;
    let selected = null;
    let saved = null;

    // set up rich text editor variables if needed
    if(from_richtext) {
        last_editor = document.querySelector(`#editor_toolbar_for_${element_id}`);
        console.log("last editor");
        console.log(last_editor);
        selected = document.getSelection(); 
        saved = [ selected.focusNode, selected.focusOffset ];
    }
    
    // handle click close - set up once when creating the selector
    document.getElementById('media_selector_modal_close').addEventListener('click', (e) => {
            e.target.closest("#media_selector").remove();
    });

    // add click event handler to capture child selection clicks - only need ONE handler
    media_selector.addEventListener('click', (e) => {
        console.log('e.target: ' , e.target);
        e.preventDefault();
        e.stopPropagation();
    
        const selected_image = e.target.closest('.media_selector_selection');
        if (selected_image !== null) {
            const media_id = selected_image.dataset.id;
            const alt = selected_image.querySelector('img').alt;
            const title = selected_image.querySelector('img').title;
            const url = e.target.dataset.hasimageurl ? e.target.src : `${window.uripath}/image/${media_id}/thumb`;
            
            if (from_richtext) {
                // Handle rich text editor behavior
                const url = e.target.dataset.hasimageurl ? e.target.src : `${window.uripath}/image/${media_id}/web`;
                const image_markup = `<img alt="${alt}" title="${title}" class="rich_image" data-media_id="${media_id}" data-size="web" src="${url}"/>`;
                // console.log("Image markup: ", image_markup);

                last_editor.focus();
                selected.collapse(saved[0], saved[1]);
                console.log("Image markup: ", image_markup);
                document.execCommand('insertHTML',false, image_markup);
            } else {
                // Handle non-rich text cases
                const preview = document.getElementById(`image_selector_chosen_preview_${element_id}`);
                preview.src = url;
                preview.alt = alt;
                preview.title = title;
                preview.closest('.selected_image_wrap').classList.add('active');
    
                const hidden_input = document.getElementById(element_id);
                hidden_input.setCustomValidity('');
                hidden_input.value = e.target.dataset.hasimageurl ? url : media_id;
            }
    
            // remove the modal
            const modal = selected_image.closest('.media_selector_modal');
            modal.parentNode.removeChild(modal);
        }
    });

    // search handler
    document.getElementById('trigger_media_selector_search').addEventListener('click', (e) => {
        const searchtext = document.getElementById('media_selector_modal_search').value;
        cur_media_page = 1;
        cur_media_searchtext = searchtext ?? null;
        fetch_images(searchtext); // string, no tags
    });
    
    // press return
    document.getElementById('media_selector_modal_search').addEventListener('keyup', (e) => {
        if (e.key === "Enter") {
            cur_media_page = 1;
            const searchtext = document.getElementById('media_selector_modal_search').value;
            cur_media_searchtext = searchtext ?? null;
            fetch_images(searchtext); // string, no tags
        }
    });
    
    // escape key to close
    document.addEventListener('keyup', (e) => {
        const media_selector = document.getElementById('media_selector');
        if (media_selector) {
            if (e.key == "Escape") {
                media_selector.parentNode.removeChild(media_selector);
            }
        }
    });
    
    // handle clear
    document.getElementById('clear_media_selector_search').addEventListener('click', (e) => {
        document.getElementById('media_selector_modal_search').value = "";
        cur_media_searchtext = null;
        cur_media_page = 1;
        fetch_images(); // string, no tags, num pages, always page 1
    });
    
    // handle pages
    document.getElementById('next_page').addEventListener('click', (e) => {
        cur_media_page++;
        fetch_images(cur_media_searchtext);
    });
    
    document.getElementById('prev_page').addEventListener('click', (e) => {
        cur_media_page--;
        if (cur_media_page == 0) {
            cur_media_page = 1;
            document.getElementById('prev_page').setAttribute('disabled', true);
        }
        fetch_images(cur_media_searchtext);
    });

    // Do initial image load
    fetch_images(); // no search, all tags

    function fetch_images(searchtext=null, taglist=null) {
        const fetchParams = {
            "action": "list_images",
            "page": cur_media_page,
            "images_per_page": images_per_page ?? 50,
            "searchtext": searchtext ?? null,
            ...(mimetypes ? { mimetypes } : {}),
            ...(tags ? { tags } : {})
        };

        // console.log("fetching images");
        // console.log(fetchParams);

        const fetchFormData = new FormData();
        Object.keys(fetchParams).forEach(key => fetchFormData.append(key, fetchParams[key]));
    
        // fetch images
        fetch(listing_endpoint, {
            method: "POST",
            body: fetchFormData,
        })
        .then((res) => res.json())
        .then((data) => {
            // console.log(data);
            const image_list = data;
            let image_list_markup = "<ul class='media_selector_list single'>";
            if (image_list.images.length == 0) {
                image_list_markup += `<li style='display:block; width:100%;'><h5 class='is-5 title' style='text-align:center;'>No images found - please try another search</h2></li>`;
            }
            image_list.images.forEach(image => {
                const datasetattribute = image.imageurl ? " data-hasimageurl='true'" : "";
                image_list_markup += `
                <li>
                    <a style='position:relative;' class='media_selector_selection' data-id='${image.id}'>
                    <aside style='font-size:0.75em; display:block; position:absolute; top:0px; right:0px; padding:0.25em 0.5em; background:rgba(0,0,0,0.5); color:#ddd;' class='media_size'>${image.width} x ${image.height}</aside>
                    <img title='${image.title}' alt='${image.alt}' ${datasetattribute} src='${image.imageurl ? image.imageurl : `${window.uripath}/image/${image.id}/thumb`}'>
                    <span class='media_selector_info'>${image.title}</span>
                    </a>
                </li>`;
            });
            image_list_markup += "</ul>";
            media_selector.querySelector('.media_selector').innerHTML = image_list_markup;

            // update page buttons
            if (image_list.images.length < images_per_page) {
                document.getElementById('next_page').setAttribute('disabled', true); 
            }
            else {
                document.getElementById('next_page').removeAttribute('disabled');
            }
            if (cur_media_page == 1) {
                document.getElementById('prev_page').setAttribute('disabled', true);
            }
            else {
                document.getElementById('prev_page').removeAttribute('disabled');
            }
        })
        .catch((error) => {
            console.error("An error occurred:", error.name, error.message, error.stack);
        });
    } //end fetch_images()
}