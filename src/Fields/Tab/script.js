function getTabIndex(el) {
	return [...el.parentElement.children].indexOf(el);	
}

document.querySelectorAll('.tabs').forEach(tabs => {
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