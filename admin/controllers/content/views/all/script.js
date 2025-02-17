new SlimSelect({
    select:'#content_search_tags'
});

window.addEventListener("load", ()=>{
    document.addEventListener("click", (e)=>{
        if(e.target.classList.contains("orderablerow")) {
            const wrapper = e.target.closest("th");
            const selectedRadio = wrapper.querySelector("input:checked");
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
    row.addEventListener('click',(e)=>{
        tr = e.target.closest('tr');
        tr.classList.toggle('selected');
        hidden_checkbox = tr.querySelector('.hidden_multi_edit');
        hidden_checkbox.checked = !hidden_checkbox.checked;
    });
});
