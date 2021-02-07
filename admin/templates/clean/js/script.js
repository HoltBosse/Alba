/* FORM AUTO FILTER */

auto_filters = document.querySelectorAll('select.auto_filter');
auto_filters.forEach(auto_filter => {
	// get index of th then build lexicon of unique items at index for each td of index in each row
	var options = ['All'];
	var table = auto_filter.closest('table');
	var th = auto_filter.closest('th');
	var all_ths = table.querySelectorAll('th');
	var index=null;
	for (var n=0; n < all_ths.length; n++) {
		if (all_ths[n]===th) {
			index=n;
		}
	}
	//console.log ('Auto filter found for column ', index);
	var col = table.querySelectorAll('tr td:nth-child(' + (index+1).toString() + ')');
	col.forEach(cell => {
		if (!options.includes(cell.innerText)) {
			options.push(cell.innerText);
		}
	});
	//console.log(options);
	var options_markup='';
	options.forEach(option => {
		options_markup += '<option value="' + option + '">' + option + '</option>';
	});
	auto_filter.innerHTML = options_markup;

	auto_filter.addEventListener('change',function(e){
		e.preventDefault();
		var filter = e.target.value;
		var table = auto_filter.closest('table');
		var th = auto_filter.closest('th');
		var all_ths = table.querySelectorAll('th');
		var index=null;
		for (var n=0; n < all_ths.length; n++) {
			if (all_ths[n]===th) {
				index=n;
			}
		}
		var col = table.querySelectorAll('tr td:nth-child(' + (index+1).toString() + ')');
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

/* TOGGLE ADMIN PANELS */

function hide(el) {
	el.style.display = "none";
}

function show(el, value) {
	el.style.display = value;
}

function toggle(el, value) {
	var display = (window.getComputedStyle
		? getComputedStyle(el, null)
		: el.currentStyle
	).display;
	if (display == "none") el.style.display = value;
	else el.style.display = "none";
}

var showhide_anchors = document.querySelectorAll(".toggle_siblings");

showhide_anchors.forEach(showhide_anchor => {
	showhide_anchor.addEventListener("click", function(e) {
		console.log("clicked toggle");
		e.preventDefault();
		var next = e.target.nextElementSibling;
		console.log(next);
		if (next) {
			toggle(next, "block");
		}
	});
});

// ALERT CODE

document.addEventListener('DOMContentLoaded', () => {
  (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
    $notification = $delete.parentNode;

    $delete.addEventListener('click', () => {
      $notification.parentNode.removeChild($notification);
    });
  });
});

// TABS


function getTabIndex(el) {
	return [...el.parentElement.children].indexOf(el);	
}

function deactivateAllTabs() {
	alltabs = document.querySelectorAll('.tabs li, .tab-content')
	alltabs.forEach(function (tab) {
		tab.classList.remove('is-active');
	});
}

let alltabs = document.querySelectorAll('.tabs');
alltabs.forEach(tabs => {
	let closest_wrap = tabs.closest('.tabs-wrap');
	let content_wrap = closest_wrap.querySelector('.tab-content-start');
	// set first tab active
	tabs.querySelector('li').classList.add('is-active');
	// set first content active
	content_wrap.querySelector('.tab-content').classList.add('is-active');
	
	// click event handler for tab headings
	tabs.querySelectorAll('li').forEach(tab => {
		tab.addEventListener('click',function(e){
			e.preventDefault();
			// remove active class from all current tabset active elements
			let all_active = closest_wrap.querySelectorAll('.is-active');
			all_active.forEach(active => {
				active.classList.remove('is-active');
			});
			let index = getTabIndex(e.target.closest('li'));
			tabs.querySelectorAll('li')[index].classList.add('is-active');
			let all_tab_contents = content_wrap.querySelectorAll('.tab-content');
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
	var image_selector_container = target.closest(".image_selector");
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

var lazyTargets = document.querySelectorAll(".lazy");
lazyTargets.forEach(lazyload);





