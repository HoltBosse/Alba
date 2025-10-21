const hidden_input = document.querySelector(`.picker_data[{{replace_with_rendered_name}}][data-repeatableindex="{{replace_with_index}}"]`);
const picker = hidden_input.parentElement.querySelector('.twocol_picker');
// handle search
picker.querySelector('.pickersearch')?.addEventListener('input', (e) => {
    // loop over values that partially match search stringsssss
    const searchstring = e.target.value;
    if (searchstring) {
        // filter
        picker.querySelectorAll('.twocol_picker_source_item').forEach(el => {
            if (el.innerText.toLowerCase().includes(searchstring.toLowerCase()) && !el.classList.contains('picked')) {
                el.style.display = 'block';
            }
            else {
                el.style.display = 'none';
            }
        });
    } else {
        // show all
        picker.querySelectorAll('.twocol_picker_source_item').forEach(el => {
            if (el.classList.contains('picked')) {
                el.style.display = 'none';
            }
            else {
                el.style.display = 'block';
            }
        });
    }
});
// handle clear search
picker.querySelector('.pickersearch_clear')?.addEventListener('click', (e) => {
    e.target.closest('.contentpicker_search_wrap').querySelector('.pickersearch').value = '';
    // show all
    picker.querySelectorAll('.twocol_picker_source_item').forEach(el => {
        if (el.classList.contains('picked')) {
            el.style.display = 'none';
        } else {
            el.style.display = 'block';
        }
    });
});
// apply drag drop to server rendered lis
const rendered_lis = picker.querySelectorAll('.twocol_picker_right ul li');
rendered_lis.forEach(li => {
    li.setAttribute('draggable', true);
    li.ondragend = (item) => {
        item.target.classList.remove('drag-sort-active');
        // update field
        
        const csv_arr = [];
        const all_li = li.closest('ul').querySelectorAll('li');
        all_li.forEach(an_li => {
            csv_arr.push(an_li.dataset.content_id);
        });
        hidden_input.value = csv_arr.join(",");
    }
    li.ondrag = (item) => {
        const selectedItem = item.target,
        list = selectedItem.parentNode,
        x = event.clientX,
        y = event.clientY;
        selectedItem.classList.add('drag-sort-active');
        let swapItem = document.elementFromPoint(x, y) === null ? selectedItem : document.elementFromPoint(x, y);
        if (list === swapItem.parentNode) {
            swapItem = swapItem !== selectedItem.nextSibling ? swapItem : swapItem.nextSibling;
            list.insertBefore(selectedItem, swapItem);
        }
    }
});
// handle clicks etc
picker.addEventListener('click', (e) => {
    if (e.target.classList.contains('twocol_picker_source_item')) {
        const title = e.target.dataset.content_title;
        const id = e.target.dataset.content_id;
        const li = document.createElement('LI');
        li.dataset.content_id = id;
        li.dataset.content_title = title;
        li.innerText = title;
        // handle drag drop
        li.setAttribute('draggable', true);
        li.ondragend = (item) => {
            item.target.classList.remove('drag-sort-active');
            // update field
            
            const csv_arr = [];
            const all_li = ul.querySelectorAll('li');
            all_li.forEach(an_li => {
                csv_arr.push(an_li.dataset.content_id);
            });
            hidden_input.value = csv_arr.join(",");
        }
        li.ondrag = (item) => {
            const selectedItem = item.target,
            list = selectedItem.parentNode,
            x = event.clientX,
            y = event.clientY;
            selectedItem.classList.add('drag-sort-active');
            let swapItem = document.elementFromPoint(x, y) === null ? selectedItem : document.elementFromPoint(x, y);
            if (list === swapItem.parentNode) {
                swapItem = swapItem !== selectedItem.nextSibling ? swapItem : swapItem.nextSibling;
                list.insertBefore(selectedItem, swapItem);
            }
        }
        // add to ul
        const ul = picker.querySelector('.twocol_picker_right ul');
        ul.appendChild(li);
        // update field
        
        const csv_arr = [];
        const all_li = ul.querySelectorAll('li');
        all_li.forEach(an_li => {
            csv_arr.push(an_li.dataset.content_id);
        });
        hidden_input.value = csv_arr.join(",");
        // hide original clicked element - ready to restore if clicked in right column
        e.target.style.display='none';
        e.target.classList.add('picked');
    }
    else {
        if (e.target.nodeName=="LI") {
            // update field
            const ul = e.target.closest('ul');
            
            const csv_arr = [];
            const all_li = ul.querySelectorAll('li');
            all_li.forEach(an_li => {
                if(an_li.dataset.content_id!=e.target.dataset.content_id) {
                    csv_arr.push(an_li.dataset.content_id);
                }
            });
            hidden_input.value = csv_arr.join(",");
            // restore left column item
            const id = e.target.dataset.content_id;
            const picker = e.target.closest('.twocol_picker');
            const left_col_el = picker.querySelector(`.twocol_picker_left li[data-content_id="${id}"]`);
            left_col_el.style.display = 'block';
            left_col_el.classList.remove('picked');
            // remove right hand element, no longer needed
            e.target.remove();
            // check if matches filter
            const searchstring = picker.querySelector('.pickersearch')?.value;
            if (searchstring) {
                if (!left_col_el.innerText.toLowerCase().includes(searchstring.toLowerCase()) ) {
                    // no match - hide
                    left_col_el.style.display = 'none';
                }
            }
        }
    }
});