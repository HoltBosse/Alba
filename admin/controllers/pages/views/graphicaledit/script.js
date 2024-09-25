window.addEventListener("load", ()=>{
    document.querySelector("iframe").contentWindow.postMessage({
        message: "hello from root page",
        type: "log",
        source: "graphicaledit",
    });

    document.querySelector("#template").addEventListener("change", (e)=>{
        console.log(document.querySelector("#template").value);

        document.querySelector("iframe").contentWindow.postMessage({
            message: {
                action: "changeTemplate",
                params: [document.querySelector("#template").value]
            },
            type: "command",
            source: "graphicaledit",
        });
    });

    document.querySelector("#displaymode").addEventListener("change", (e)=>{
        if(document.querySelector("#displaymode").value=="mobile") {
            document.querySelector("main.container").classList.add("mobile");
        } else {
            document.querySelector("main.container").classList.remove("mobile");
        }
    });
});