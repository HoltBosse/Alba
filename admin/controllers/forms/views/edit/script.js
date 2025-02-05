document.querySelector(".add_field_list.controls_button_fields_grid").addEventListener("click", (e)=>{
    if(e.target.classList.contains("button")) {
        let displayField = document.createElement("div");
        displayField.classList.add("displayfield");
        displayField.dataset.type=e.target.dataset.type;
        displayField.innerHTML = e.target.dataset.display_markup;

        document.querySelector(".fields_panel").appendChild(displayField);
    }
});

document.querySelector("[for='add_field']").addEventListener("click", ()=>{
    document.querySelector("[for='field_settings'").setAttribute("disabled", "");
});

document.querySelector(".fields_panel").addEventListener("click", (e)=>{
    if(e.target.classList.contains("displayfield")) {
        let fieldPaneButton = document.querySelector("[for='field_settings'");
        fieldPaneButton.removeAttribute("disabled");
        fieldPaneButton.click();
    }
});