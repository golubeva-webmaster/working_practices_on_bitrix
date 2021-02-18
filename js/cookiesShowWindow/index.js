	function cookiesPopup(){
	var elem = {
		CONTAINER: '.b-cookies',
		CLOSE: '.b-cookies__close'
	},
	state = {COOKIES_INVIS: 'b-cookies--invisible'};

	// при первом посещении показать
	if (document.cookie.indexOf('loveCookies') < 0) {
		setTimeout(function () {
			$(elem.CONTAINER).removeClass(state.COOKIES_INVIS);
		}, 3000);
	}

	$(document).on('click', elem.CLOSE, function () {
		$(this).closest(elem.CONTAINER).addClass(state.COOKIES_INVIS);
		document.cookie = "loveCookies=loveCookies; expires= Tue, 19 Jan 2038 03:15:07 GMT; Path=/";
	});
