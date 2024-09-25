window.addEventListener("message", (e)=>{
    if(typeof(e.data)!="object" || e.data.source!="graphicaledit") {
        //console.log("rejecting: ", e.data);
        return;
    }
    if(e.data.type=="log") {
        console.log("message from parent:", e.data.message);
    }else if(e.data.type=="command") {
        Commands[e.data.message.action](...e.data.message.params);
    }
});
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