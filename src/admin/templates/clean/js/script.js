const hamburger = document.querySelector("a.navbar-burger.burger");
if(hamburger) {
	hamburger.addEventListener("click", ()=>{
		document.getElementById("navbarBasicExample").classList.toggle("active");
	});
}
const nav_menu = document.getElementById("navbarBasicExample");
if(nav_menu) {
	nav_menu.addEventListener("click", (e)=>{
		if(e.target.classList.contains("navbar-link")) {
			e.target.closest(".navbar-item.has-dropdown").querySelector("div.navbar-dropdown").classList.toggle("active");
		}
	});
}