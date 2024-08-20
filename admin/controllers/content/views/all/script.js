new SlimSelect({
    select:'#content_search_tags'
});

window.addEventListener("load", ()=>{
    document.addEventListener("click", (e)=>{
        if(e.target.classList.contains("orderablerow")) {
            let wrapper = e.target.closest("th");
            let selectedRadio = wrapper.querySelector("input:checked");
            //selectedRadio.checked = false;

            document.querySelectorAll('th:has(.orderablerow) input[value="regular"]').forEach(item=>{
                item.checked = true;
            });

            if(selectedRadio.nextElementSibling) {
                selectedRadio.nextElementSibling.checked = true;
            } else {
                wrapper.querySelector("input").checked = true;
            }
            
            document.getElementById(selectedRadio.getAttribute("form")).submit();
        }
    });
});

admin_rows = document.querySelectorAll('.content_admin_row');
admin_rows.forEach(row => {
    row.addEventListener('click',function(e){
        tr = e.target.closest('tr');
        tr.classList.toggle('selected');
        hidden_checkbox = tr.querySelector('.hidden_multi_edit');
        hidden_checkbox.checked = !hidden_checkbox.checked;
    });
});

// ordering js

function dragstart_handler(e) {
    //e.preventDefault();
    data = e.target.dataset.itemid;
    console.log(data);
    e.dataTransfer.dropEffect = "move";
    e.dataTransfer.setData("text/plain", data);
    e.target.closest('table').classList.add('dragging');
    e.target.closest('tr').classList.add('dragging');
    //console.log(e);
}

function dragover_handler(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = "move";
    e.target.classList.add('ready');
}

function dragleave_handler(e) {
    e.preventDefault();
    //e.dataTransfer.dropEffect = "move";
    e.target.classList.remove('ready');
}

function drop_handler(e) {
    e.preventDefault();
    //console.log(e);
    e.preventDefault();
    // get required info
    var source_id = e.dataTransfer.getData('text/plain');
    var dest_id = e.target.closest('tr').dataset.itemid;
    if (e.target.classList.contains('drop_before')) {
        var insert_position = 'before';
    }
    else {
        var insert_position = 'after';
    }
    //console.log('Insert',source_id, insert_position, dest_id);
    // perform ajax action silently
    api_data = {"action":"insert","sourceid":source_id,"destid":dest_id,"insert_position":insert_position,"content_type": window.content_type_filter};
    postAjax(`${window.uripath}/admin/content/api`, api_data, function(data){
        response = JSON.parse(data);
        if (response.success=='1') {
            // do nothing - assume it worked
        }
        else {
            console.log(response); 
            alert('Ordering failed.');
        }
    });

    // move dom rows - regardless of success of ajax - report failures
    source_row = document.getElementById('row_id_' + source_id);
    dest_row = document.getElementById('row_id_' + dest_id);
    tbody = source_row.closest('tbody');
    tbody.removeChild(source_row);
    if (insert_position=='after') {
        tbody.insertAfter(source_row, dest_row);
    }
    else {
        tbody.insertBefore(source_row, dest_row);
    }
    // clean up grips - TODO: cleaner version for single grip in drop_handler
    var grips = document.querySelectorAll('.grip');
    grips.forEach(grip => {
        grip.classList.remove('ready');
    });
}

function dragend_handler(e) {
    e.preventDefault();
    console.log(e);
    e.target.closest('table').classList.remove('dragging');
    e.target.closest('tr').classList.remove('dragging');
}