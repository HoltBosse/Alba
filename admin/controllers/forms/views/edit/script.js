function field_options_to_markup(input){
    const config = JSON.parse(input);
    let markup = "";

    config.forEach(field=>{
        if(field.type=="input") {
            markup += `<div>
                <label>${field.label}</label>
                <input class="input" type='${field.input_type}' >
            </div>`;
        } else if (field.type=="select") {
            let options = "";
            for (const [key, value] of Object.entries(field.options)) {
                options += `<option value="${key}">${value}</option>`;
            }

            markup += `<div>
                <label>${field.label}</label>
                <div class="select">
                    <select style="width: 100%;">
                        ${options}
                    </select>
                </div>
            </div>`;
        } else {
            markup += "<p>invalid field</p>";
        }
    });

    return markup;
}

document.querySelector(".add_field_list.controls_button_fields_grid").addEventListener("click", (e)=>{
    if(e.target.classList.contains("button")) {
        const displayField = document.createElement("div");
        displayField.dataset.config = e.target.dataset.config;
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
        const fieldPaneButton = document.querySelector("[for='field_settings'");
        fieldPaneButton.removeAttribute("disabled");

        document.querySelector(".field_configuration_list").innerHTML = field_options_to_markup(e.target.dataset.config);

        fieldPaneButton.click();
    }
});