const _log = console.log;
console.log = function (...rest) {
    // window.parent is the parent frame that made this window
    window.parent.postMessage(
        {
            source: "graphicaleditrender",
            type: "log",
            message: rest,
        },
        "*",
    );
    _log.apply(console, arguments);
};
window.addEventListener("error", (e)=>{
    console.log("ERROR!!! - BAD |", e.error);
});