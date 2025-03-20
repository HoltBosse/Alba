let currentlySelectedField = null;

function fieldOptionsToMarkup(input){
    const config = JSON.parse(input);
    let markup = "";

    config.forEach(field=>{
        if(field.type=="input") {
            markup += `<div>
                <label>${field.label}:</label>
                <input data-fieldid="${field.id}" class="input" type='${field.input_type}' value='${field.default ?? ""}'>
            </div>`;
        } else if (field.type=="select") {
            let options = "";
            for (const [key, value] of Object.entries(field.options)) {
                options += `<option ${(field.default ?? "")==key ? "selected" : ""} value="${key}">${value}</option>`;
            }

            markup += `<div>
                <label>${field.label}:</label>
                <div class="select">
                    <select data-fieldid="${field.id}" style="width: 100%;">
                        ${options}
                    </select>
                </div>
            </div>`;
        } else {
            markup += "<p>invalid field</p>";
        }
    });

    markup += "<button class='button is-link form_field_options_submit'>Update</button>";

    return markup;
}

function updateElementConfig(input) {
    const config = JSON.parse(input);
    const pannel = document.querySelector(".field_configuration_list");

    config.forEach(field=>{
        field.default = pannel.querySelector(`[data-fieldid='${field.id}']`).value;
    });

    return JSON.stringify(config);
}

function fieldConfigToClassConfig(field) {
    const config = JSON.parse(field.dataset.config);
    const classConfig = {};
    classConfig["type"] = field.dataset.type;

    config.forEach((field)=>{
        if(field.type=="input" || field.type=="select") {
            if(field.default && field.default!="") {
                classConfig[field.name] = field.default;
            }
        }
    });

    return classConfig;
}

function generateForm(formId) {
    const fields = [];
    document.querySelector(".fields_panel").querySelectorAll(".displayfield").forEach((field)=>{
        fields.push(fieldConfigToClassConfig(field));
    });

    const form = {
        "id": formId,
        "fields": fields,
    };

    return form;
}

function getFormId() {
    return "replaceWithFormAliasHere";
}

document.querySelector(".add_field_list.controls_button_fields_grid").addEventListener("click", (e)=>{
    if(e.target.classList.contains("button")) {
        const displayField = document.createElement("div");
        displayField.dataset.config = e.target.dataset.config;
        displayField.classList.add("displayfield");
        displayField.dataset.type=e.target.dataset.type;
        displayField.innerHTML = e.target.dataset.display_markup;

        document.querySelector(".fields_panel").appendChild(displayField);
        document.querySelector("[name='form_json']").value = JSON.stringify(generateForm(getFormId()));
    }
});

document.querySelector(".field_configuration_list").addEventListener("click", (e)=>{
    if(e.target.classList.contains("form_field_options_submit")) {
        currentlySelectedField.dataset.config = updateElementConfig(currentlySelectedField.dataset.config);
        document.querySelector("[name='form_json']").value = JSON.stringify(generateForm(getFormId()));

        currentlySelectedField = null;
        document.querySelector("[for='add_field']").click();
    }
});

document.querySelector("[for='add_field']").addEventListener("click", ()=>{
    document.querySelector("[for='field_settings'").setAttribute("disabled", "");
});

document.querySelector(".fields_panel").addEventListener("click", (e)=>{
    if(e.target.classList.contains("displayfield")) {
        const fieldPaneButton = document.querySelector("[for='field_settings'");
        fieldPaneButton.removeAttribute("disabled");

        document.querySelector(".field_configuration_list").innerHTML = fieldOptionsToMarkup(e.target.dataset.config);

        currentlySelectedField = e.target;

        fieldPaneButton.click();
    }
});