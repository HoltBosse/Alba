window.addEventListener("message", (e)=>{
    if(typeof(e.data)!="object" || e.data.source!="graphicaleditrender") {
        //console.log("rejecting: ", e.data);
        return;
    }
    if(e.data.type=="log") {
        if(typeof(e.data.message)=="object") {
            console.log("%cmessage from iframe log bridge:", "color: yellow;", ...e.data.message);
        } else {
            console.log("%cmessage from iframe log bridge:", "color: yellow;", e.data.message);
        }
    } else if(e.data.type=="command") {
        if(typeof(e.data.message)=="object") {
            console.log("%ccommand from iframe log bridge:", "color: yellow;", ...e.data.message);
        } else {
            console.log("%ccommand from iframe log bridge:", "color: yellow;", e.data.message);
        }
    } else {
        console.log("unknown operation from iframe", e.data);
    }
});