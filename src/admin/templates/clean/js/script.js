/* FORM AUTO FILTER */

auto_filters = document.querySelectorAll('select.auto_filter');
auto_filters.forEach(auto_filter => {
	// get index of th then build lexicon of unique items at index for each td of index in each row
	const options = ['All'];
	const table = auto_filter.closest('table');
	const th = auto_filter.closest('th');
	const all_ths = table.querySelectorAll('th');
	let index=null;
	for (let n=0; n < all_ths.length; n++) {
		if (all_ths[n]===th) {
			index=n;
		}
	}
	//console.log ('Auto filter found for column ', index);
	const col = table.querySelectorAll(`tr td:nth-child(${(index+1).toString()})`);
	col.forEach(cell => {
		if (!options.includes(cell.innerText)) {
			options.push(cell.innerText);
		}
	});
	//console.log(options);
	let options_markup='';
	options.forEach(option => {
		options_markup += `<option value="${option}">${option}</option>`;
	});
	auto_filter.innerHTML = options_markup;

	auto_filter.addEventListener('change',(e)=> {
		e.preventDefault();
		const filter = e.target.value;
		const table = auto_filter.closest('table');
		const th = auto_filter.closest('th');
		const all_ths = table.querySelectorAll('th');
		let index=null;
		for (let n=0; n < all_ths.length; n++) {
			if (all_ths[n]===th) {
				index=n;
			}
		}
		const col = table.querySelectorAll(`tr td:nth-child(${(index+1).toString()})`);
		col.forEach(cell => {
			if (filter==='All' || filter===cell.innerText) {
				cell.closest('tr').style.display = "table-row";
			}
			else {
				cell.closest('tr').style.display = "none";
			}
		});
	})
});

// ALERT CODE

document.addEventListener('DOMContentLoaded', () => {
  (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
    const $notification = $delete.parentNode;

    $delete.addEventListener('click', () => {
      $notification.parentNode.removeChild($notification);
    });
  });
});

// TABS


function getTabIndex(el) {
	return [...el.parentElement.children].indexOf(el);	
}

// biome-ignore lint: not solving now
function deactivateAllTabs() {
	alltabs = document.querySelectorAll('.tabs li, .tab-content')
	alltabs.forEach((tab) => {
		tab.classList.remove('is-active');
	});
}

let alltabs = document.querySelectorAll('.tabs');
alltabs.forEach(tabs => {
	const closest_wrap = tabs.closest('.tabs-wrap');
	const content_wrap = closest_wrap.querySelector('.tab-content-start');
	// set first tab active - check to make sure no existing active items from invalid checks
	if(!tabs.querySelector(".is-active")) {
		tabs.querySelector('li').classList.add('is-active');
	}
	// set first content active - check to make sure no existing active items from invalid checks
	if(!content_wrap.querySelector('.is-active')) {
		content_wrap.querySelector('.tab-content').classList.add('is-active');
	}
	
	// click event handler for tab headings
	tabs.querySelectorAll('li').forEach(tab => {
		tab.addEventListener('click',(e)=> {
			e.preventDefault();
			// remove active class from all current tabset active elements
			const all_active = closest_wrap.querySelectorAll('.is-active');
			all_active.forEach(active => {
				active.classList.remove('is-active');
			});
			const index = getTabIndex(e.target.closest('li'));
			tabs.querySelectorAll('li')[index].classList.add('is-active');
			const all_tab_contents = content_wrap.querySelectorAll('.tab-content');
			if (all_tab_contents.length<=index) {
				// form might be incomplete with more tab headings than content areas
				// fail silently
				console.log('missing tab content area - check form json');
			}
			else {
				// activate content
				content_wrap.querySelectorAll('.tab-content')[index].classList.add('is-active');
			}
		});
	});
});

// END TABS

/* LAZYLOAD IMAGES */

function lazyload(target) {
	const image_selector_container = target.closest(".image_selector");
	console.log(image_selector_container);
	const obs = new IntersectionObserver((entries, observer) => {
		entries.forEach(entry => {
			if (entry.isIntersecting) {
				const img = entry.target;
				const src = img.dataset.src;
				img.setAttribute("src", src);
				img.classList.add("loaded");
				observer.disconnect();
			}
		});
	}, image_selector_container); // use closest image_selector or null = document
	obs.observe(target);
}

const lazyTargets = document.querySelectorAll(".lazy");
lazyTargets.forEach(lazyload);

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