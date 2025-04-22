function fetchImages(searchtext, page, perPage, mimetypes, listingEndpoint, tags, mediaSelector) {
    const fetchParams = {
        "action": "list_images",
        "page": page,
        "imagesPerPage": perPage ?? 50,
        "searchtext": searchtext ?? null,
        ...(mimetypes ? { mimetypes } : {}),
        ...(tags ? { tags } : {})
    };

    // console.log("fetchParams: ");
    // console.log(fetchParams);

    const fetchFormData = new FormData();
    Object.keys(fetchParams).forEach(key => fetchFormData.append(key, fetchParams[key]));

    // fetch images
    fetch(listingEndpoint, {
        method: "POST",
        body: fetchFormData,
    }).then((res) => res.json()).then((data) => {
        let imageListMarkup = "<ul class='media_selector_list single'>";
        if (data.images.length == 0) {
            imageListMarkup += `<li style='display:block; width:100%;'><h5 class='is-5 title' style='text-align:center;'>No images found - please try another search</h2></li>`;
        }
        data.images.forEach(image => {
            const datasetattribute = image.imageurl ? " data-hasimageurl='true'" : "";
            imageListMarkup += `
            <li>
                <a style='position:relative;' class='media_selector_selection' data-id='${image.id}'>
                <aside style='font-size:0.75em; display:block; position:absolute; top:0px; right:0px; padding:0.25em 0.5em; background:rgba(0,0,0,0.5); color:#ddd;' class='media_size'>${image.width} x ${image.height}</aside>
                <img title='${image.title}' alt='${image.alt}' ${datasetattribute} src='${image.imageurl ? image.imageurl : `${window.uripath}/image/${image.id}/thumb`}'>
                <span class='media_selector_info'>${image.title}</span>
                </a>
            </li>`;
        });
        imageListMarkup += "</ul>";
        mediaSelector.querySelector('.media_selector').innerHTML = imageListMarkup;

        // update page buttons
        if (data.images.length < perPage) {
            mediaSelector.querySelector('#next_page').setAttribute('disabled', true); 
        } else {
            mediaSelector.querySelector('#next_page').removeAttribute('disabled');
        }
        
        if (page == 1) {
            mediaSelector.querySelector('#prev_page').setAttribute('disabled', true);
        } else {
            mediaSelector.querySelector('#prev_page').removeAttribute('disabled');
        }
    }).catch((error) => {
        console.error("An error occurred:", error.name, error.message, error.stack);
    });
}

export function openMediaSelector(elementId, imagesPerPage, mimetypes, tags, listingEndpoint) {
    let curMediaPage = 1;
    let curMediaSearchtext = null;

    // launch image selector
    const mediaSelector = document.createElement('div');
    mediaSelector.id = "media_selector";
    mediaSelector.classList.add(`media_selector_for_${elementId}`);
    mediaSelector.innerHTML =`
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
    document.body.appendChild(mediaSelector);
    
    // handle click close - set up once when creating the selector
    mediaSelector.querySelector('#media_selector_modal_close').addEventListener('click', (e) => {
        e.target.closest("#media_selector").remove();
    });

    // add click event handler to capture child selection clicks - only need ONE handler
    mediaSelector.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
    
        const selected_image = e.target.closest('.media_selector_selection');
        if (selected_image !== null) {
            const mediaId = selected_image.dataset.id;
            const alt = selected_image.querySelector('img').alt;
            const title = selected_image.querySelector('img').title;
            // biome-ignore lint: needs to stay as "let" since it is used in the event handler
            let url = e.target.dataset.hasimageurl ? e.target.src : `${window.uripath}/image/${mediaId}`;

            const mediaItemSelected = new CustomEvent("mediaItemSelected", {
                bubbles: true,
                target: mediaSelector,
                detail: {
                    url: url,
                    mediaId: mediaId,
                    alt: alt,
                    title: title,
                    hasImageUrl: e.target.dataset.hasimageurl ?? false,
                },
            });

            mediaSelector.dispatchEvent(mediaItemSelected);
    
            // remove the modal
            const modal = selected_image.closest('.media_selector_modal');
            modal.parentNode.removeChild(modal);
        }
    });

    // search handler
    mediaSelector.querySelector('#trigger_media_selector_search').addEventListener('click', (e) => {
        const searchtext = mediaSelector.querySelector('#media_selector_modal_search').value;
        curMediaPage = 1;
        curMediaSearchtext = searchtext ?? null;
        fetchImages(searchtext, curMediaPage, imagesPerPage, mimetypes, listingEndpoint, tags, mediaSelector); // string, no tags
    });
    
    // press return
    mediaSelector.querySelector('#media_selector_modal_search').addEventListener('keyup', (e) => {
        if (e.key === "Enter") {
            curMediaPage = 1;
            const searchtext = mediaSelector.querySelector('#media_selector_modal_search').value;
            curMediaSearchtext = searchtext ?? null;
            fetchImages(searchtext, curMediaPage, imagesPerPage, mimetypes, listingEndpoint, tags, mediaSelector); // string, no tags
        }
    });
    
    // escape key to close
    document.addEventListener('keyup', (e) => {
        const mediaSelector = document.getElementById('media_selector');
        if (mediaSelector) {
            if (e.key == "Escape") {
                mediaSelector.parentNode.removeChild(mediaSelector);
            }
        }
    });
    
    // handle clear
    mediaSelector.querySelector('#clear_media_selector_search').addEventListener('click', (e) => {
        mediaSelector.querySelector('#media_selector_modal_search').value = "";
        curMediaSearchtext = null;
        curMediaPage = 1;
        fetchImages(null, curMediaPage, imagesPerPage, mimetypes, listingEndpoint, tags, mediaSelector); // string, no tags, num pages, always page 1
    });
    
    // handle pages
    mediaSelector.querySelector('#next_page').addEventListener('click', (e) => {
        curMediaPage++;
        fetchImages(curMediaSearchtext, curMediaPage, imagesPerPage, mimetypes, listingEndpoint, tags, mediaSelector); // updated to include current page
    });
    
    mediaSelector.querySelector('#prev_page').addEventListener('click', (e) => {
        curMediaPage--;
        if (curMediaPage == 0) {
            curMediaPage = 1;
            mediaSelector.querySelector('#prev_page').setAttribute('disabled', true);
        }
        fetchImages(curMediaSearchtext, curMediaPage, imagesPerPage, mimetypes, listingEndpoint, tags, mediaSelector); // updated to include current page
    });

    // Do initial image load
    fetchImages(curMediaSearchtext, curMediaPage, imagesPerPage, mimetypes, listingEndpoint, tags, mediaSelector); // no search, all tags

    return mediaSelector;
}