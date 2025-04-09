export function handleAdminRows(classname) {
    document.querySelectorAll(`.${classname}`).forEach(row => {
        row.addEventListener('click',(e)=> {
            const tr = e.target.closest('tr');
            const hidden_checkbox = tr.querySelector('.hidden_multi_edit');

            tr.classList.toggle('selected');
            hidden_checkbox.checked = !hidden_checkbox.checked;
        });
    });
}