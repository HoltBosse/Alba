class Commands {
    static changeTemplate(template) {
        //console.log(template);
        fetch(`/admin/pages/graphicaleditrequest/gettemplate/${template}`).then(response=>response.text()).then(templateContent=>{
            //console.log(templateContent);
            templateContent = templateContent.replace("<!--PAGECONTENT-->", "<div id='graphicaleditrendersection'></div>");
            let details = document.querySelector("#graphicaleditrendersection");

            let html = document.createElement("html");
            html.innerHTML = templateContent;
            document.querySelector("html").replaceWith(html);
            document.querySelector("#graphicaleditrendersection").replaceWith(details);
            console.log(`template switched to ${template}`);
        }).catch((e)=>{
            console.log("error switching template");
            console.log(e.message);
        });
    }
}