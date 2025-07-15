// preview widget
function preview_widget(el) {
    fetch(`${window.uripath}/admin/pages/edit/widget_preview/${el.dataset.widgetid}`).then((response)=>{
        return response.text();
    }).then((html)=>{
        // create temp overlay
        const preview_el = document.createElement("DIV");
        preview_el.classList.add('preview');
        preview_el.innerHTML = "<button id='preview_close' class='delete' aria-label='close'></button><h2 style='text-align:center;' class='title is-2'>PREVIEW</h2><p style='text-align:center;'>Click/tap anywhere to close - note, styling may not be 100% accurate without front end template</p><hr>";
        const preview_content_el = document.createElement("DIV");
        preview_content_el.classList.add('preview_contents');
        preview_content_el.innerHTML = html;
        preview_el.appendChild(preview_content_el);
        preview_el.addEventListener('click',(e)=> {
            e.target.remove();
        });
        document.body.appendChild(preview_el);
    }).catch((err) => {
        console.warn('Error generating widget preview', err);
    });
}

// handle widget title filter
function update_widget_title_filter() {
    const search_value = document.querySelector('#widget_title_filter').value;
    // set visibility of add widget buttons
    document.querySelectorAll('.add_widget_to_override').forEach(add_widget_button => {
        const wrap_el = add_widget_button.closest('.widget_controls_wrap');
        const info_text = wrap_el.querySelector('.widget_title_and_type').innerText.toLowerCase();
        if (info_text.includes(search_value.toLowerCase()) || !search_value) {
            // show
            wrap_el.style.display="flex";
        }
        else {
            wrap_el.style.display="none";
        }
    });
}
document.querySelector('#widget_title_filter')?.addEventListener('input', (e)=>{
    update_widget_title_filter();
});

function validate_view_options() {
    view_options = document.getElementById('content_type_controller_view_options');
    return true;
}


content_type = document.getElementById("content_type");
content_type_controller_view = document.getElementById('content_type_controller_view');
//window.content_type_id = content_type.value;
//window.loaded_content_type_id = content_type.value;
//content_type_wrap = document.getElementById("content_type_wrap");


// switch views based on content type
content_type.addEventListener('change',(e)=> {
    content_type_value = e.target.value;
    if (content_type_value) {
        window.new_url = `${window.uripath}/admin/pages/edit/${window.pageid}/${content_type_value}/-1`;
        serialize_form('page_form'); // save form to localstorage so user doesn't have to retype any main fields
        window.location = window.new_url;
        //alert(window.new_url);
    }
});

// switch options based on view choice if available
if (content_type_controller_view) {
    content_type_controller_view.addEventListener('change',(e)=> {
        view_value = e.target.value;
        if (view_value) {
            window.new_url = `${window.uripath}/admin/pages/edit/${window.pageid}/${content_type.value}/${view_value}`;
            serialize_form('page_form'); // save form to localstorage so user doesn't have to retype any main fields
            window.location = window.new_url;
        }
    });
}


// TODO - fix multiselects for localstorage

function unserialize_form(id) {
    const form_json = window.localStorage.getItem(id);
    if (!form_json) {
        console.warn('No saved details from change of content_type / view');
        return false;
    }
    const form = document.getElementById(id);
    if (!form) {
        return false;
    } 
    form_data = JSON.parse(form_json);
    form_data.forEach(form_item => {
        //console.log('Looking for form element with name: ', form_item.field_name);
        matching_form_element = document.querySelector(`[name="${form_item.field_name}"]`);
        if (matching_form_element) {
            //console.log('Inserting stored item: ', form_item);
            matching_form_element.value = form_item.field_value;
        } else {
            console.warn('Error deserializing form. No element with name matching: ',form_item.field_name);
        }
    });
    window.localStorage.removeItem(id);
}

function serialize_form(id) {
    const form = document.getElementById(id);
    if (!form) {
        return false;
    }
    // Setup our serialized data
    const serialized = [];
    // Loop through each field in the form
    for (let i = 0; i < form.elements.length; i++) {
        const field = form.elements[i];
        // Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields
        if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue;
        // If a multi-select, get all selections
        if (field.type === 'select-multiple') {
            for (let n = 0; n < field.options.length; n++) {
                if (!field.options[n].selected) continue;
                serialized.push({"field_name":field.name,"field_value":field.options[n].value});
            }
        }
        // Convert field data to a query string
        else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
            serialized.push({"field_name":field.name,"field_value":field.value});
        }
    }
    serialized_json = JSON.stringify(serialized);
    window.localStorage.setItem(id,serialized_json);
}

// restore required fields from localstorage if available
unserialize_form('page_form');

// widget override modal

const add_widget_override_buttons = document.querySelectorAll('.add_override_widget');
add_widget_override_buttons.forEach(btn => {
    btn.addEventListener('click',(e)=> {
        // set currently working on tag wrap
        window.cur_position_tag_wrap = e.target.closest('.position_tag_wrap');
        // show modal
        const modal=document.getElementById('widget_selector_modal');
        modal.classList.add('is-active');
    });
}); 

document.querySelector('.modal .delete').addEventListener('click',(e)=> {
    e.preventDefault();
    e.stopPropagation();
    const modal=document.getElementById('widget_selector_modal');
    modal.classList.remove('is-active');
});

function update_all_position_widgets_inputs() {
    // update every array position_widgets_input value in every position
    // with id list based on tags inside
    // MUST be done at least before submitting form
    const all_position_widgets_inputs = document.querySelectorAll('.position_widgets_input');
    all_position_widgets_inputs.forEach(w_list_input => {
        const widgets_in_position = w_list_input.parentNode.querySelectorAll('.draggable_widget');
        const widget_array = [];
        widgets_in_position.forEach(w => {
            const widget_id = w.dataset.tagid;
            widget_array.push(widget_id);
        });
        const widget_array_string = widget_array.toString();
        w_list_input.value = widget_array_string;
    });
}

function add_widget_to_override_list (widget_id, widget_title) {
    // already know last known widget list 
    // via window.cur_position_tag_wrap - set on 'add widget' button press
    const markup = `
        <span data-tagid='${widget_id}' draggable='true' ondragover='dragover_tag_handler(event)' ondragend='dragend_tag_handler(event)' ondragstart='dragstart_tag_handler(event)' class='draggable_widget  is-warning tag'>${widget_title}<span class='delete is-delete'>X</span></span>	
    `;
    //console.log(window.cur_position_tag_wrap);
    window.cur_position_tag_wrap.querySelector('.tags').innerHTML+=markup;
    // update input csv position_widgets_input with new id
    update_all_position_widgets_inputs();
}

// handle widget button modal click
document.querySelector('.modal').addEventListener('click',(e)=> {
    if (e.target.classList.contains('add_widget_to_override')) {
        // get id and add to list
        const widget_id = e.target.dataset.widgetid;
        const widget_title = e.target.dataset.widgettitle;
        add_widget_to_override_list (widget_id, widget_title);
        // remove modal
        //console.log('adding widget ', widget_id);
        e.target.closest('.modal').classList.remove('is-active');
    }
});

// drag drop tags in template overrides

window.tagdrag = null;

function dragover_tag_handler(e) {
    // get nearest tag because drop target is any element inside our droppable element not just parent droppable itself
    const nearest_tag = e.target.closest('.tag');
    if ( isBefore( tagdrag, nearest_tag ) ) nearest_tag.parentNode.insertBefore( tagdrag, nearest_tag )
      else nearest_tag.parentNode.insertBefore( tagdrag, nearest_tag.nextSibling )
    update_all_position_widgets_inputs();
}

function dragstart_tag_handler(e) {
    e.dataTransfer.setData("text/plain", e.target.dataset.tagid);
    e.dataTransfer.effectAllowed = "move"
     e.dataTransfer.dropEffect = "move";
    window.tagdrag = e.target;
}

function dragend_tag_handler(e) {
    window.tagdrag = null;
}

function isBefore( el1, el2 ) {
    let cur;
    if (el1===el2) {
        //console.log('self');
        return false;
    }
    if ( el2.parentNode === el1.parentNode ) {
        for ( cur = el1.previousSibling; cur; cur = cur.previousSibling ) {
            if (cur === el2) return true
        }
    } 
    else {
        console.warn('isBefore failed - elements not siblings');
        return false;
    }
    return false;
}

// handle override / remove override clicks
function toggle_override(e) {
    const override_panels = e.target.parentNode.querySelectorAll('.position_tag_wrap');
    override_panels.forEach(panel => {
        panel.classList.toggle('active');
    });
    e.target.closest('.template_layout_widget_wrap').classList.toggle('active');
}

// handle other clicks such as tag delete, override toggle etc
document.getElementById('template_layout_container').addEventListener('click',(e)=> {
    if (e.target.classList.contains('addoverride')) {
        toggle_override(e);
    }
    if (e.target.classList.contains('removeoverride')) {
        if (confirm('Are you sure? This will clear ALL overrides for this page/position once saved.')) {
            toggle_override(e);
            const override_tags = e.target.parentNode.querySelectorAll('.override_tags_wrap .tag');
            override_tags.forEach(t => {
                t.remove();
            });
            update_all_position_widgets_inputs();
        }
    }
    if (e.target.classList.contains('delete')) {
        e.target.closest('.tag').remove();
        update_all_position_widgets_inputs();
    }
});