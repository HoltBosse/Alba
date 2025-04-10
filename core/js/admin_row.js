export function handleAdminRows(elementSelector) {
    let counter = 0;

    document.querySelectorAll(elementSelector).forEach(row => {
        row.addEventListener('click',(e)=> {
            const tr = e.target.closest('tr');
            const hidden_checkbox = tr.querySelector('.hidden_multi_edit');

            tr.classList.toggle('selected');
            hidden_checkbox.checked = !hidden_checkbox.checked;

            if(tr.classList.contains("selected")) {
                counter++;
            } else {
                counter--;
            }

            const rowSelectedEvent = new CustomEvent("adminRowSelected", {
                bubbles: true,
                target: tr,
                detail: {
                    counter: counter,
                },
            });

            tr.dispatchEvent(rowSelectedEvent);
        });
    });
}