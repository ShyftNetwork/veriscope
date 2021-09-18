
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Require the main contracts Vue application logic
 */
require('./kycflow');

/**
 * Require the main attestations Vue application logic
 */
require('./attestationsflow');

/**
 * Require the chart Vue component logic
 */
require('./chart');

/**
 * Require the pie Vue component logic
 */
require('./pie');

/**
 * Require the authenticated Vue component logic
 */
require('./backoffice');

/**
 * For form inputs, control their classes when
 * each contains a value
 */

const toggleValueClass = el =>
	el.classList.toggle('has-value', el.value !== '');

const toggleHasValueHandler = ({ target }) =>
	toggleValueClass(target);

/**
 * Helper to listen for a CSS hook for autofill fields.
 * Inspired by https://stackoverflow.com/questions/11708092/detecting-browser-autofill
 * Essentially creating an event input via CSS on autofill for webkit
 * @param {Object} e - Event from input
 */
const animationStartHandler = e => {
	if(e.animationName === 'onAutoFillStart') {
		e.target.classList.add('has-value');
	}
};

[].forEach.call(document.querySelectorAll('.form-control--simple input'), function(input,i) {
	input.addEventListener('keyup', toggleHasValueHandler);
	input.addEventListener('change', toggleHasValueHandler);
	input.addEventListener('input', toggleHasValueHandler);
	input.addEventListener('blur', toggleHasValueHandler);
	input.addEventListener('focus', toggleHasValueHandler);
	input.addEventListener('animationstart', animationStartHandler);
	toggleValueClass(input);
});

/**
 * For controlling the navigation via the hamburger
 */
if(document.getElementById('main-navigation__hamburger')) {
	const hamburger = document.getElementById('main-navigation__hamburger');
	const mainNavigation = document.getElementById('main-navigation');
	hamburger.addEventListener('mousedown', function(e) {
		mainNavigation.classList.toggle('main-navigation--open');
	});
}

/**
 * Trigger the notification to open. Then enable its
 * closing via a click handler.
 */
if(document.getElementById('notification')) {
	const notification = document.getElementById('notification');
	const toggleNotification = function toggleNotification() {
		notification.classList.toggle('notification--open');
	}
	toggleNotification();
	notification.addEventListener('click', toggleNotification);
}

/**
 * Purchase Agreement form for
 * agreeing to terms & lockup
 */
if(document.getElementById('purchase-agreement-form')) {
    const legal_agree = document.getElementById('legal_agree_1');
    const lockup_agree = document.getElementById('lockup_agree_1');
    const checkPurchaseAgreementForm = () => {
        const submit = document.getElementById('purchase-agreement-submit');
        if(legal_agree.checked && lockup_agree.checked) {
            submit.disabled = false;
        } else {
            submit.disabled = true;
        }
    }
    legal_agree.addEventListener('change', checkPurchaseAgreementForm);
    lockup_agree.addEventListener('change', checkPurchaseAgreementForm);
}

/**
 * When the window scroll event is fired,
 * add a modifier class to the main navigation.
 */
 const doc = document.documentElement;
 const nav = document.getElementById('main-navigation');
 const scrollClass = 'main-navigation--scrolled';
 const globe = document.getElementById('globe');
 window.addEventListener('scroll', function(e) {
	var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
	if(top > 10) {
		nav.classList.add(scrollClass);
		if (globe) {
			globe.style.display = "none";
		}
		
	} else {
		nav.classList.remove(scrollClass);
		if (globe) {
			globe.style.display = "block";
		}
		

	}
});

/**
 * When the window load event is fired,
 * remove the preload class. Useful for page
 * loading transitions.
 */
window.addEventListener('load', function() {
	document.body.classList.remove('preload');
});
