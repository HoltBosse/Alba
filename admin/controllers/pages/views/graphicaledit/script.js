window.addEventListener("load", ()=>{
    document.querySelector("iframe").contentWindow.postMessage({
        message: "hello from root page",
        type: "log",
        source: "graphicaledit",
    });

    document.querySelector("iframe").contentWindow.postMessage({
        message: {
            action: "changeTemplate",
            params: ["bulma_graphicaledit"]
        },
        type: "command",
        source: "graphicaledit",
    });
});