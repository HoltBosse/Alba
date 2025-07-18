/* 
    position_control is a cursed field
    it doesnt have a target to adjust required on in it easily
    so we cant use form logic to turn it on and off
    so this does the same thing
*/

function updatePositionPages(el) {
    const position_pages = document.querySelector(`[data-field_id="position_pages"]`);
    if(el.value==2) {
        position_pages.classList.add("logic_hide");
    } else {
        position_pages.classList.remove("logic_hide");
    }
}

const position_control = document.querySelector("#position_control");
position_control.addEventListener("change", (e)=>{
    updatePositionPages(e.target);
});

window.addEventListener("load", ()=>{
    updatePositionPages(position_control);
})