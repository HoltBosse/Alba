function createFieldDiv(fieldType) {
    const id = `id_${crypto.randomUUID()}`;
    const numFieldTypes = document.querySelectorAll("input.fieldtype").length;

    const div = document.createElement("div");
    div.classList.add("field-item");
    div.id = id;
    div.dataset.fieldType = fieldType;
    div.innerHTML = `
        <h5 class="title is-5">Unnamed Field</h5>
        <p class='unimportant'>Type: ${fieldType}</p>
        <span class="close-me">&times;</span>
        <input type='hidden' class="fieldtype" name='fieldtypes[]'>
        <input type='hidden' class="fields" name='fields[]' value='${fieldType}'>
    `;
    div.setAttribute("hx-trigger", "click");
    div.setAttribute("hx-post", `/admin/forms/api/fieldoptions/${fieldType}?index=${numFieldTypes}`);
    div.setAttribute("hx-target", ".control-panel");
    div.setAttribute("hx-swap", "innerHTML");
    div.setAttribute("hx-include", `input.fieldtype`);

    htmx.process(div);

    return div;
}

document.querySelector(".new-field-select").addEventListener("change", (e) => {
    const value = e.target.value;
    
    e.preventDefault();
    event.target.value = "";

    console.log(value);

    const div = createFieldDiv(value);

    htmx.process(div);

    document.querySelector(".fields-panel").appendChild(div);
});

document.querySelector(".fields-panel").addEventListener("click", (e) => {
    const fieldItem = e.target.closest(".field-item");
    if (!fieldItem) return;

    document.querySelector(".field-item.selected")?.classList.remove("selected");

    fieldItem.classList.toggle("selected");
});

document.querySelector(".fields-panel").addEventListener("click", (e) => {
    if(e.target.classList.contains("close-me")) {
        const fieldItem = e.target.closest(".field-item");
        if (!fieldItem) return;

        if(fieldItem.classList.contains("selected")) {
            clear_selection();
        }

        fieldItem.remove();
    }
});

function clear_selection() {
    document.querySelector(".field-item.selected")?.classList.remove("selected");

    htmx.ajax("POST", `/admin/forms/api/fieldoptions/none`, ".control-panel");
}

document.addEventListener('keydown', (e)=>{
    if (e.key === "Escape" || e.key === "Esc") {
        clear_selection();
    }
});

document.addEventListener('click', (e)=>{
    if (!e.target.closest('.field-item') && !e.target.closest('.control-panel')) {
        clear_selection();
    }
});

function updateFieldDiv(field, label, fieldData) {
    field.querySelector("h5").innerText = label || "Unnamed Field";
    field.querySelector("input.fieldtype").value = fieldData;

    return field
}

document.querySelector(".control-panel").addEventListener("click", (e) => {
    if(e.target.classList.contains("button") && e.target.classList.contains("field-update")) {
        const form = e.target.closest("form");

        const formStatus = form.reportValidity();
        if (!formStatus) return;

        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            body: formData
        }).then(response => response.json()).then(res => {
            if(res.status==0) {
                htmx.swap(".control-panel", res.html, {swap:"innerHTML"});
            } else if(res.status==1) {
                const selectedField = document.querySelector(".field-item.selected");
                selectedField.classList.remove("selected");

                updateFieldDiv(selectedField, formData.get("label"), res.data);
                
                //console.log("yay");
                //console.log(res);

                htmx.ajax("POST", `/admin/forms/api/fieldoptions/none`, ".control-panel");
            } else {
                console.log(res);
                alert("unable to update form!");
            }
        });
    }
});

window.addEventListener("load", ()=>{
    if(window.existingFormData) {
        const formData = window.existingFormData;
        const formFields = formData.fields || [];

        formFields.forEach((fieldData)=>{
            const fieldDiv = createFieldDiv(fieldData.type);

            document.querySelector(".fields-panel").appendChild(fieldDiv);

            //remove type from fieldData
            delete fieldData.type;

            const convertedFieldData = [];

            for (const [key, value] of Object.entries(fieldData)) {
                convertedFieldData.push({
                    name: key,
                    value: value
                });
            }

            //update field div with data
            updateFieldDiv(fieldDiv, fieldData.label, JSON.stringify(convertedFieldData));
        });

    }
});