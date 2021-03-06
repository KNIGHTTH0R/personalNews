// is the DOM ready for manipulation?
document.addEventListener('DOMContentLoaded', function() {

		// ---- variables ----
		var themeLight = 'light';
		var themeDark = 'dark';
		var elementToToggleOnLoad = 'application-loading';

		// ---- global functions ----
		// toggle Element
		function toggleElement(elementId,targetElementId) {
			toggleElement = document.getElementById(elementId);
			toggleElement.onclick = function() {
				targetElement = document.getElementById(targetElementId);
				if(targetElement.classList.contains('js-hidden')) {
					targetElement.classList.remove('js-hidden');
				} else {
					targetElement.classList.add('js-hidden');
				}
				event.preventDefault();
				fixTimeline("content");
			}
		}

		// make element sticky (via position in css)
		function stickyElement(stickyId,compensateId,compensateProperty) {
			stickyElement = document.getElementById(stickyId);
			stickyElement.classList.add('sticky');
			stickyHeight = stickyElement.clientHeight + 'px';

			//add Element-Height as defined property to desired element
			document.getElementById(compensateId).style.setProperty(compensateProperty,stickyHeight);
		}

		// place Element in relation to sticky element
		function placeOverlay(elementId) {
			overlayElement = document.getElementById(elementId);
			overlayElement.style.top = stickyHeight;
		}

		// ---- helper functions ----
		// add JS to body-tag to allow CSS-Manipulation if JS is available
		function setJs() {
			document.getElementsByTagName('body')[0].classList.add('js');
		}

		// scroll to desired position
		function scrollToTarget(x,y) {
			window.scrollTo(x,y);
		}

		// ---- theme-switching ----
		// check if localStorage is filled and set body.class with it. This is useful, if the site runs as app
		var savedLocalStorageTheme = localStorage.getItem('theme');
		console.log(savedLocalStorageTheme);
		if(savedLocalStorageTheme !== null) {
			document.getElementsByTagName('html')[0].classList.remove(themeLight, themeDark);
			document.getElementsByTagName('html')[0].classList.add(savedLocalStorageTheme);
			if(savedLocalStorageTheme === themeLight) {
				document.getElementById('theme-switcher').checked = false;
			}
			if(savedLocalStorageTheme === themeDark) {
				document.getElementById('theme-switcher').checked = true;
			}
		}

		// switch theme by adding and removing classes to body
		function themeSwitch(elementId) {
			var renderFile = 'themeswitch.php?theme=';

			switchingElement = document.getElementById(elementId);
			switchingElement.onclick = function() {
				xmlhttp = new XMLHttpRequest();
				if (this.checked) {
					document.getElementsByTagName('html')[0].classList.add(themeDark);
					document.getElementsByTagName('html')[0].classList.remove(themeLight);
					xmlhttp.open('GET',renderFile+themeDark,true);
					xmlhttp.send();
					localStorage.setItem('theme', themeDark);
					console.log('localStorage Theme is: ' + themeDark);
				} else {
					document.getElementsByTagName('html')[0].classList.add(themeLight);
					document.getElementsByTagName('html')[0].classList.remove(themeDark);
					xmlhttp.open('GET','themeswitch.php?theme='+themeLight,true);
					xmlhttp.send();
					localStorage.setItem('theme', themeLight);
					console.log('localStorage Theme is: ' + themeLight);
				}
			}
		}

		// ---- Loading Feeds (via Ajax) ----
		// add listener
		function channelSwitch(elementId) {
			elementContainer = document.getElementById(elementId);
			elementContainer.addEventListener('click', switchChannel, false);
		}

		// prepare loading after click
		function switchChannel(e) {
			if (e.target !== e.currentTarget) {
				channelLink = e.target.getAttribute('href');
				ajaxRequest(channelLink);
				e.preventDefault();
			}
			e.stopPropagation();
		}

		// loading the content
		function ajaxRequest(channelLink) {
			// if this function is called with no parameter, we're chencking the localStorag, if one is present and use this (useful for initial load)
			if(channelLink === '') {
				var savedLocalStorageChannel = localStorage.getItem('channel');
				if(savedLocalStorageChannel !== null) {
					var channelLink = savedLocalStorageChannel;
				}
			}

			// requesting the content
			document.getElementById(elementToToggleOnLoad).classList.remove('js-hidden');
			renderFile = 'render-feeds.php';
			xmlhttp = new XMLHttpRequest();
			xmlhttp.open('GET',renderFile+channelLink,true);
			xmlhttp.send();

			overlayContainer = document.getElementById('application-overlay');
			overlayContainer.classList.add('js-hidden');


			// output if call is succesful
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState === 4 && xmlhttp.readyState) {
					outputContainer = document.getElementById('content');
					outputContainer.innerHTML = xmlhttp.response;
					document.getElementById(elementToToggleOnLoad).classList.add('js-hidden');
					document.getElementById('content').classList.remove('fixed');
					scrollToTarget(0,0);
					localStorage.setItem('channel', channelLink);
				}
			}
		}

		// ---- fix element to current position
		function fixTimeline(elementId) {
			elementToFix = document.getElementById(elementId);
			scrollY = window.pageYOffset;

			if(elementToFix.classList.contains('fixed')) {
				elementToFix.classList.remove('fixed');
				elementToFix.style.top = 0;
				scrollToTarget(0,scrollYMem);
			} else {
				elementToFix.classList.add('fixed');
				elementToFix.style.top = '-' + scrollY + 'px';
				scrollYMem = scrollY;
			}

			console.log(scrollYMem);
		}

		// ---- initialize ----
		// set Js on body if JS is available
		setJs();

		// initial load of content
		ajaxRequest('');

		//sticky header (item(id) to fix, item(id) with property to compensate fix)
		stickyElement('application-header','content','margin-top');

		// theme switcher
		themeSwitch('theme-switcher');

		// toggle Element (trigger, target)
		toggleElement('toggle-overlay', 'application-overlay');

		// place overlay
		placeOverlay('application-overlay');

		// switch channels
		channelSwitch('channels');
	});
