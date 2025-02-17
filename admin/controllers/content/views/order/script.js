new Sortable(document.getElementById('ordertablebody'), {
    animation: 150,
    onUpdate: (e)=>{
        /* console.log("From ", e.oldIndex, " to ", e.newIndex);
        console.log(e.item.dataset); */
        async function update_ordering() {
            const formData = new FormData();
            // changeorder
            // id, new_order, prev_order, content_type
            formData.append("action", "changeorder");
            formData.append("id", e.item.dataset.content_id);
            formData.append("content_type", content_type);
            formData.append("new_order", e.newIndex+1); // client-size index 0 based, ordering 1
            formData.append("prev_order", e.oldIndex+1);
            fetch(`${window.uripath}/admin/content/api`, {
                    method: "POST",
                    body: formData,
            }).then((response) => response.json()).then((data) => {
                console.log(data);
            });
        }

        update_ordering();
        
    },
});