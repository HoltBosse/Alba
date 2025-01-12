function unloadCheckerFunction(e) {
    let blockStatus = false;
    document.querySelectorAll("input, textarea").forEach((i)=>{
        if(i.value != (i.getAttribute("value") ?? "")) {
            blockStatus = true;
        }
    });

    if(blockStatus) {
        e.preventDefault();
        e.returnValue = true;
    }
}
window.addEventListener("beforeunload", unloadCheckerFunction);
window.addEventListener("load", ()=>{
    document.querySelector("form").addEventListener("submit", ()=>{
        window.removeEventListener("beforeunload", unloadCheckerFunction);
    });
});