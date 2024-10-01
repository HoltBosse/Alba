window.addEventListener("load", ()=>{
    window.top.postMessage(
        {
            message: 'hello from iframe',
            type: "command",
            source: "graphicaleditrender"
        },
        '*'
    );
});

document.addEventListener("click", (e)=>{
    console.log("ele clicked");
    console.log(e.target.classList.contains("test"));
});