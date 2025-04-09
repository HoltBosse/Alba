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
