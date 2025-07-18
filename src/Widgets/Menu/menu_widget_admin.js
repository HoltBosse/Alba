const nanoid=(t=21)=>{let e="",r=crypto.getRandomValues(new Uint8Array(t));while(t--){const n=63&r[t];e+=n<36?n.toString(36):n<62?(n-26).toString(36).toUpperCase():n<63?"_":"-"}return e};

function find_node_in_tree(node, id) {
    // in:  node (js object with id, title, children[array], parent[id ref], info[object with misc props])
    //      id - id generated for node by nanoid function
    let found = null;
    //console.log ('Checking for ',id,' against ',node.id);
    if (node.id==id) {
        //console.log ('FOUND!');
        return node;
    } else {
        for (let n=0; n<node.children.length; n++) {
            found = find_node_in_tree(node.children[n], id);
            if (found) {
                //console.log('FOUND IN CHILD!');
                return found;
            }
        }
    }
    return found;
}


function make_md_node (title, type, info) {
    const newnode = {};
    newnode.title = title;
    newnode.parent = "root";
    newnode.id = nanoid();
    newnode.children = [];
    newnode.type = type;
    newnode.info = info;
    return newnode;
}

function get_index (node) {
    const parent_node = find_node_in_tree(menu_designer_config, node.parent);
    if (!parent_node) {
        throw 'Index cannot be determined: Parent node not found';
    }
    for (let n=0; n<parent_node.children.length; n++) {
        if (node.id==parent_node.children[n].id) {
            return n;
        }
    }
    return 0;
}

function is_descendant(node_a, node_b) {
    // check if node_a is descendant of node_b
    // loop through all parents of node_a
    let descendant = false;
    let parent = find_node_in_tree(menu_designer_config, node_a.parent);
    while (parent.type!=='root') {
        if (parent.id==node_b.id) {
            descendant = true;
            break;
        }
        parent = find_node_in_tree(menu_designer_config, parent.parent);
    }
    return descendant;
}

function insert_before (source, dest, parent) {
    // remove source from original location
    const source_parent_node = find_node_in_tree(menu_designer_config, source.parent);
    const source_index = get_index (source);
    //console.log('removing original node from index ',source_index, ' in node ', source_parent_node);
    source_parent_node.children.splice(source_index, 1);

    // dest = thing inserting before
    const dest_index = get_index (dest);
    if (dest_index==0) {
        // push to start
        parent.children.unshift(source);
    }
    else {
        // push to index
        parent.children.splice(dest_index, 0, source);
    }
    source.parent = parent.id;
}

function insert_after (source, dest, parent) {
    // remove source from original location
    const source_parent_node = find_node_in_tree(menu_designer_config, source.parent);
    const source_index = get_index (source);
    source_parent_node.children.splice(source_index, 1);

    // dest = thing inserting before
    const dest_index = get_index (dest);
    parent.children.splice(dest_index+1, 0, source);
    source.parent = parent.id;
}

function insert_inside (source, dest) {
    // remove source from original location
    const source_parent_node = find_node_in_tree(menu_designer_config, source.parent);
    const source_index = get_index (source);
    
    source_parent_node.children.splice(source_index, 1);

    dest.children.push(source);
    source.parent = dest.id;
}

function delete_node (node) {
    const index = get_index(node);
    const parent = find_node_in_tree(menu_designer_config, node.parent);
    parent.children.splice(index, 1);
    render_menu_designer();
}

// handle add page button click

document.getElementById('menu_desiger_add_pages').addEventListener('click',()=> {
    // clicked add pages
    // get all pages to add
    const checked = document.querySelectorAll('#menu_designer_page_listing input:checked');
    if (checked.length==0) {
        alert('Must select at least one page to add');
    }
    else {
        checked.forEach(page => {
            const name = page.closest('label').querySelector('span').innerText;
            const page_id = page.value ;
           /*  console.log('Adding page:');
            console.log(name, page_id); */
            const newnode = make_md_node (name, 'page', {"page_id":page_id});
            menu_designer_config.children.push(newnode);
            page.checked = false;
        });
        render_menu_designer();
    }
});

document.getElementById('menu_desiger_add_link').addEventListener('click',()=> {
    const link_text = document.getElementById('link_text').value;
    const link_url = document.getElementById('link_url').value;
    const newtab = document.getElementById('link_newtab').checked;
    if (!link_text || !link_url) {
        alert('Link text and link URL must contain data');
    }
    else {
        const newnode = make_md_node (link_text, 'link', {'newtab':newtab,'url':link_url});
        if (document.querySelector('.menu_node.selected')) {
            // push into selected node, not root
            const parent = find_node_in_tree(menu_designer_config, document.querySelector('.menu_node.selected').id );
            parent.children.push(newnode);
        }
        else {
            // push into root
            menu_designer_config.children.push(newnode);
        }
        document.getElementById('link_text').value = '';
        document.getElementById('link_url').value = '';
        document.getElementById('link_newtab').checked = false;
        render_menu_designer();
    }
});

document.getElementById('menu_desiger_add_heading').addEventListener('click',()=> {
    const heading_text = document.getElementById('heading_text').value;
    if (!heading_text) {
        alert('Heading text cannot be empty');
    }
    else {
        const newnode = make_md_node (heading_text, 'heading', {});
        if (document.querySelector('.menu_node.selected')) {
            // push into selected node, not root
            const parent = find_node_in_tree(menu_designer_config, document.querySelector('.menu_node.selected').id );
            parent.children.push(newnode);
        }
        else {
            // push into root
            menu_designer_config.children.push(newnode);
        }
        document.getElementById('heading_text').value = '';
        render_menu_designer();
    }
});
document.getElementById('menu_desiger_edit_heading').addEventListener('click',()=> {
    const heading_text = document.getElementById('heading_text').value;
    if (!heading_text) {
        alert('Heading text cannot be empty');
    }
    else {
        // update node with details from edit field
        const edited_node_el = document.querySelector('.menu_node.selected');
        const edited_node = find_node_in_tree(menu_designer_config, edited_node_el.id);
        const edited_field = document.getElementById('heading_text');
        edited_node.title = edited_field.value; // update node value with field value
        document.querySelector('fieldset.edit').classList.remove('edit'); // remove edit mode from fieldset
        edited_field.value=''; // reset field value
        render_menu_designer();
    }
});

document.getElementById('menu_desiger_edit_link').addEventListener('click',()=> {
    const link_text = document.getElementById('link_text').value;
    const link_url = document.getElementById('link_url').value;
    const link_newtab = document.getElementById('link_newtab').checked;
    if (!link_text || !link_url) {
        alert('Link text or URL cannot be empty');
    }
    else {
        // update node with details from edit field
        const edited_node_el = document.querySelector('.menu_node.selected');
        const edited_node = find_node_in_tree(menu_designer_config, edited_node_el.id);
        edited_node.title = link_text; // update node value with field values
        edited_node.info.url = link_url;
        edited_node.info.newtab = link_newtab;
        document.querySelector('fieldset.edit').classList.remove('edit'); // remove edit mode from fieldset
        document.getElementById('link_text').value=''; // reset field values
        document.getElementById('link_url').value='';
        document.getElementById('link_newtab').checked=false;
        render_menu_designer();
    }
});

document.getElementById('menu_designer_tree').addEventListener('click',(e)=> {
    if (e.target.classList.contains('delete')) {
        e.stopPropagation();
        // clicked delete on tree node
        if (confirm('Are you sure? Changes, once saved, cannot be undone!')) {
            const node = find_node_in_tree(menu_designer_config, e.target.closest('.menu_node').id);
            delete_node (node);
        }
    }
    if (e.target.classList.contains('menu_node')) {
        e.stopPropagation();
        heading_input_el = document.getElementById('heading_text');
        link_text_el = document.getElementById('link_text');
        link_url_el = document.getElementById('link_url');
        link_newtab_el = document.getElementById('link_newtab');
        // clicked on tree node
        // unselect previously selected and reset forms
        document.querySelectorAll('.menu_node.selected').forEach(e=>{
            e.classList.remove('selected');
            heading_input_el.value='';
            document.getElementById('link_text').value=''; // reset field values
            document.getElementById('link_url').value='';
            document.getElementById('link_newtab').checked=false;   
        });
        // clear edit on fieldsets so only correct one is selected later
        document.querySelectorAll('fieldset.edit').forEach(e=>{
            e.classList.remove('edit');
        });
        // select clicked menu node
        e.target.classList.add('selected');
        // populate setup field for changes
        const node = find_node_in_tree(menu_designer_config, e.target.id);
        if (node.type=='heading') {
            // populate heading field
            heading_input_el.closest('fieldset').classList.add('edit'); // set edit mode of container
            heading_input_el.value = node.title;
        }
        if (node.type=='link') {
            link_text.closest('fieldset').classList.add('edit'); // set edit mode of container
            link_text.value = node.title;
            link_url.value = node.info.url;
            link_newtab.checked = node.info.newtab;
        }
    }
});

// drag drop functions

// biome-ignore lint: not solving now
function md_dragstart_handler(ev) {
    // Add the target element's id to the data transfer object
    ev.dataTransfer.setData("application/my-app", ev.target.id);
    ev.dataTransfer.effectAllowed = "move";
    window.menu_designer_dragging_id = ev.target.id;
}
// biome-ignore lint: not solving now
function md_dragover_handler(ev) {
    ev.preventDefault();
    const newnode_el = document.getElementById(window.menu_designer_dragging_id);
    const droppable_el = ev.target.closest('.menu_node');
    //console.log(droppable.id, newnode.id);
    // check if dragging over self
    const source_node = find_node_in_tree (menu_designer_config, newnode_el.id);
    const dest_node = find_node_in_tree (menu_designer_config, droppable_el.id);
    if (newnode_el.id==droppable_el.id) {
        ev.dataTransfer.dropEffect = "none";
    }
    else if (is_descendant(dest_node, source_node)) {
        ev.dataTransfer.dropEffect = "none";
    }
    else {
        // dragging over droppable
        ev.dataTransfer.dropEffect = "move";
        // get position within droppable
        const rect = droppable_el.getBoundingClientRect();
        //var height_from_top_of_droppable = ev.screenY - rect.top;
        const height_from_top_of_droppable = ev.clientY - rect.top;
        const width_from_left_of_droppable = ev.clientX - rect.left;
        //var droppable_height = rect.height/2;
        //console.log(height_from_top_of_droppable);
        if (width_from_left_of_droppable > rect.width/1.4) {
            droppable_el.classList.add('insertinside');
            droppable_el.classList.remove('insertafter');
            droppable_el.classList.remove('insertbefore');
        }
        else if (height_from_top_of_droppable < rect.height/2) {
            droppable_el.classList.add('insertbefore');
            droppable_el.classList.remove('insertafter');
            droppable_el.classList.remove('insertinside');
        }
        else {
            droppable_el.classList.add('insertafter');
            droppable_el.classList.remove('insertbefore');
            droppable_el.classList.remove('insertinside');
        }
    }
}
// biome-ignore lint: not solving now
function md_dragleave_handler(ev) {
    ev.preventDefault();
    //ev.dataTransfer.dropEffect = "move";
    const el = ev.target.closest('.menu_node');
    el.classList.remove('insertbefore');
    el.classList.remove('insertafter');
    el.classList.remove('insertinside');
}
// biome-ignore lint: not solving now
function md_drop_handler(ev) { 
    ev.preventDefault();
    ev.stopPropagation();
    // Get the id of the target and add the moved element to the target's DOM
    const node_id = ev.dataTransfer.getData("application/my-app");

    //const newnode_el = document.getElementById(node_id);
    const sibling_el = ev.target.closest('.menu_node');
    //const parent_el = sibling_el.parentNode.closest('.menu_node');

    const source_node = find_node_in_tree(menu_designer_config, node_id);
    const dest_node = find_node_in_tree(menu_designer_config, ev.target.closest('.menu_node').id);  
    const parent_node = find_node_in_tree(menu_designer_config, dest_node.parent); 

    const rect = sibling_el.getBoundingClientRect();
    const height_from_top_of_droppable = ev.clientY - rect.top;
    const width_from_left_of_droppable = ev.clientX - rect.left;

    if (width_from_left_of_droppable > rect.width/1.4) { 
        insert_inside (source_node, dest_node);
    }
    else if (height_from_top_of_droppable < rect.height/2) {
        // insert before
        // parent_el.insertBefore(newnode_el, sibling_el);
        insert_before (source_node, dest_node, parent_node);
    }
    else { 
        // insert after
        //sibling.parentNode.insertBefore(newnode_el, sibling_el.nextSibling);
        insert_after (source_node, dest_node, parent_node);
    }

    render_menu_designer();

    //console.log ('Inserting ',newnode, ' next to ', sibling, ' inside ', parent);
    /* sibling_el.classList.remove('insertbefore');
    sibling_el.classList.remove('insertafter'); */
}

// render tree onload

function render_node(node, level) {
    // recursive function to get menu designer node markup
    let markup="";
    if (level>=0) {
        // don't render root, only it's children (has level of -1)
        let menu_type_icon = '<i class="far fa-newspaper"></i>';
        if (node.type=='link') {
            menu_type_icon = '<i class="fas fa-external-link-alt"></i>';
        }
        if (node.type=='heading') {
            menu_type_icon = '<i class="fas fa-heading"></i>';
        }
        markup=`
        <div class='menu_node depth_${level}' id='${node.id}' draggable=true ondragstart="md_dragstart_handler(event)" ondragleave="md_dragleave_handler(event)" ondragover="md_dragover_handler(event)" ondrop="md_drop_handler(event)">
            <div class='menu_node_info'>
                <h5 class='menu_designer_node_title is-5 title'>${node.title}<button type='button' class="pull-right delete is-small"></button></h5>
                <p class='unimportant'>${menu_type_icon} ${node.type}</p>
            </div>`;
    }
    if (node.children.length>0) {
        node.children.forEach(child => {
            markup += render_node(child, level+1);
        });
    }
    markup +=`
    </div>
    `;
    return markup;
}

function render_menu_designer() {
    // get markup
    const markup = render_node(menu_designer_config, -1);
    // get target containing element
    const el = document.getElementById('menu_designer_tree');
    // clear container
    el.innerHTML = null;
    // create dom from markup text and add to container
    el.appendChild(document.createRange().createContextualFragment(markup));
    // save to input element for submission on save
    const config_el = document.getElementById('menu_designer_config');
    config_el.value = JSON.stringify(menu_designer_config);
}

// render on pageload
render_menu_designer();