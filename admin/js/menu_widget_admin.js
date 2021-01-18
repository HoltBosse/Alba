let nanoid=(t=21)=>{let e="",r=crypto.getRandomValues(new Uint8Array(t));for(;t--;){let n=63&r[t];e+=n<36?n.toString(36):n<62?(n-26).toString(36).toUpperCase():n<63?"_":"-"}return e};

function find_node_in_tree(node, id) {
    let found = null;
    //console.log ('Checking for ',id,' against ',node.id);
    if (node.id==id) {
        //console.log ('FOUND!');
        return node;
    }
    else {
        for (var n=0; n<node.children.length; n++) {
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
    let newnode = {};
    newnode.title = title;
    newnode.parent = "root";
    newnode.id = nanoid();
    newnode.children = [];
    newnode.type = type;
    newnode.info = info;
    return newnode;
}

function get_index (node) {
    let parent_node = find_node_in_tree(menu_designer_config, node.parent)
    for (var n=0; n<parent_node.children.length; n++) {
        if (node.id==parent_node.children[n].id) {
            return n;
        }
    }
    return 0;
}

function insert_before (source, dest, parent) {
    // remove source from original location
    let source_parent_node = find_node_in_tree(menu_designer_config, source.parent);
    let source_index = get_index (source);
    //console.log('removing original node from index ',source_index, ' in node ', source_parent_node);
    source_parent_node.children.splice(source_index, 1);

    // dest = thing inserting before
    let dest_index = get_index (dest);
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
    let source_parent_node = find_node_in_tree(menu_designer_config, source.parent);
    let source_index = get_index (source);
    source_parent_node.children.splice(source_index, 1);

    // dest = thing inserting before
    let dest_index = get_index (dest);
    parent.children.splice(dest_index+1, 0, source);
    source.parent = parent.id;
}

function insert_inside (source, dest) {
    // remove source from original location
    let source_parent_node = find_node_in_tree(menu_designer_config, source.parent);
    let source_index = get_index (source);
    source_parent_node.children.splice(source_index, 1);

    dest.children.push(source);
    source.parent = dest.id;
}

// handle add page button click

document.getElementById('menu_desiger_add_pages').addEventListener('click',function(e){
    // clicked add pages
    // get all pages to add
    let checked = document.querySelectorAll('#menu_designer_page_listing input:checked');
    if (checked.length==0) {
        alert('Must select at least one page to add');
    }
    else {
        checked.forEach(page => {
            let name = page.closest('label').querySelector('span').innerText;
            let page_id = page.value ;
           /*  console.log('Adding page:');
            console.log(name, page_id); */
            let newnode = make_md_node (name, 'page', {"page_id":page_id});
            menu_designer_config.children.push(newnode);
            page.checked = false;
        });
        render_menu_designer();
    }
});

document.getElementById('menu_desiger_add_link').addEventListener('click',function(e){
    let link_text = document.getElementById('link_text').value;
    let link_url = document.getElementById('link_url').value;
    let newtab = document.getElementById('link_newtab').checked;
    if (!link_text || !link_url) {
        alert('Link text and link URL must contain data');
    }
    else {
        let newnode = make_md_node (link_text, 'link', {'newtab':newtab,'url':link_url});
        menu_designer_config.children.push(newnode);
        document.getElementById('heading_text').value = '';
        render_menu_designer();
    }
});

document.getElementById('menu_desiger_add_heading').addEventListener('click',function(e){
    let heading_text = document.getElementById('heading_text').value;
    if (!heading_text) {
        alert('Heading text cannot be empty');
    }
    else {
        let newnode = make_md_node (heading_text, 'heading', {});
        menu_designer_config.children.push(newnode);
        document.getElementById('heading_text').value = '';
        render_menu_designer();
    }
});

// drag drop functions

function md_dragstart_handler(ev) {
    // Add the target element's id to the data transfer object
    ev.dataTransfer.setData("application/my-app", ev.target.id);
    ev.dataTransfer.effectAllowed = "move";
    window.menu_designer_dragging_id = ev.target.id;
}
function md_dragover_handler(ev) {
    ev.preventDefault();
    let newnode = document.getElementById(window.menu_designer_dragging_id);
    let droppable = ev.target.closest('.menu_node');
    //console.log(droppable.id, newnode.id);
    // check if dragging over self
    if (newnode.id==droppable.id) {
        ev.dataTransfer.dropEffect = "none";
    }
    else {
        // dragging over droppable
        ev.dataTransfer.dropEffect = "move";
        // get position within droppable
        var rect = droppable.getBoundingClientRect();
        //var height_from_top_of_droppable = ev.screenY - rect.top;
        var height_from_top_of_droppable = ev.clientY - rect.top;
        var width_from_left_of_droppable = ev.clientX - rect.left;
        //var droppable_height = rect.height/2;
        //console.log(height_from_top_of_droppable);
        if (width_from_left_of_droppable > rect.width/1.4) {
            droppable.classList.add('insertinside');
            droppable.classList.remove('insertafter');
            droppable.classList.remove('insertbefore');
        }
        else if (height_from_top_of_droppable < rect.height/2) {
            droppable.classList.add('insertbefore');
            droppable.classList.remove('insertafter');
            droppable.classList.remove('insertinside');
        }
        else {
            droppable.classList.add('insertafter');
            droppable.classList.remove('insertbefore');
            droppable.classList.remove('insertinside');
        }
    }
}
function md_dragleave_handler(ev) {
    ev.preventDefault();
    //ev.dataTransfer.dropEffect = "move";
    let el = ev.target.closest('.menu_node');
    el.classList.remove('insertbefore');
    el.classList.remove('insertafter');
    el.classList.remove('insertinside');
}
function md_drop_handler(ev) {
    ev.preventDefault();
    ev.stopPropagation();
    // Get the id of the target and add the moved element to the target's DOM
    const node_id = ev.dataTransfer.getData("application/my-app");

    let newnode_el = document.getElementById(node_id);
    let sibling_el = ev.target.closest('.menu_node');
    let parent_el = sibling_el.parentNode.closest('.menu_node');

    let source_node = find_node_in_tree(menu_designer_config, node_id);
    let dest_node = find_node_in_tree(menu_designer_config, ev.target.closest('.menu_node').id);  
    let parent_node = find_node_in_tree(menu_designer_config, dest_node.parent); 

    var rect = sibling_el.getBoundingClientRect();
    var height_from_top_of_droppable = ev.clientY - rect.top;
    var width_from_left_of_droppable = ev.clientX - rect.left;

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
        markup=`
        <div class='menu_node depth_${level}' id='${node.id}' draggable=true ondragstart="md_dragstart_handler(event)" ondragleave="md_dragleave_handler(event)" ondragover="md_dragover_handler(event)" ondrop="md_drop_handler(event)">
            <div class='menu_node_info'>
                <h5 class='menu_designer_node_title is-5 title'>${node.title}</h5>
                <p class='unimportant'>${node.type}</p>
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
    let markup = render_node(menu_designer_config, -1);
    // get target containing element
    let el = document.getElementById('menu_designer_tree');
    // clear container
    el.innerHTML = null;
    // create dom from markup text and add to container
    el.appendChild(document.createRange().createContextualFragment(markup));
    // save to input element for submission on save
    let config_el = document.getElementById('menu_designer_config');
    config_el.value = JSON.stringify(menu_designer_config);
}

// render on pageload
render_menu_designer();