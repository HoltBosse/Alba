document.body.addEventListener('click', (e) => {
    if(e.target.classList.contains("form_row_open")) {
        e.preventDefault();

        const header = document.querySelector(".form_submissions_row.header");
        const row = e.target.closest(".form_submissions_row");

        //header contains a series of divs with text in them, transform to array
        const headers = Array.from(header.children).map(div => div.textContent.trim());
        const values = Array.from(row.children).map(div => div.textContent.trim());

        const dialog = document.createElement("dialog");
        dialog.classList.add("form_submission_dialog");
        
        dialog.addEventListener("close", () => {
            dialog.remove();
        });

        values.forEach((value, index) => {
            if(index === 0) return; // skip first column (icon)

            const field = document.createElement("div");
            field.innerHTML = `<strong>${headers[index]}:</strong><hr><p>${value}</p><br>`;
            dialog.appendChild(field);
        });

        const closeButton = document.createElement("button");
        closeButton.textContent = "x";
        closeButton.classList.add("form_submission_dialog_close");
        closeButton.addEventListener("click", () => {
            dialog.close();
        });
        dialog.appendChild(closeButton);

        document.body.appendChild(dialog);
        dialog.showModal();
    }
});