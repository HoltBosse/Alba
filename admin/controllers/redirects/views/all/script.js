admin_rows = document.querySelectorAll('.content_admin_row');
admin_rows.forEach(row => {
    row.addEventListener('click',(e)=> {
        tr = e.target.closest('tr');
        tr.classList.toggle('selected');
        hidden_checkbox = tr.querySelector('.hidden_multi_edit');
        hidden_checkbox.checked = !hidden_checkbox.checked;
    });
});