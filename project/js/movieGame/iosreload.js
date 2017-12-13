$(function () {
	var isPageHide = false;
	window.addEventListener('pageshow', function () {
		if (isPageHide) {
			window.location.reload();
		}
	});
	window.addEventListener('pagehide', function () {
		isPageHide = true;
	});
})