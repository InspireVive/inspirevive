// setup console logging
var debugging = true;
if (typeof console == "undefined") var console = { log: function() {} };
else if (!debugging || typeof console.log == "undefined") console.log = function() {};

(function($) {
	$(function() {
		$("[data-toggle=popover]").popover();
		$('.popover-toggle').click(function(e) { return false; });
	});
})(jQuery);

$(function() {
	if ($('#page').length == 0)
		return;

	snapper = new Snap({
		element: document.getElementById('page'),
		disable: 'right',
		maxPosition: 230,
		minPosition: 230,
		hyperextensible: false,
		touchToDrag: false
	});

	var resize = function() {
		if ($(window).width() <= 767)
			snapper.enable();
		else
			snapper.disable();
	};

	$(window).resize(resize);
	resize();

	$('.navbar-toggle').click(function() {
		if (snapper.state().state=="left") {
			snapper.close();
		} else {
			snapper.open('left');
		}
	});

});

// props to http://my.opera.com/GreyWyvern/blog/show.dml/1725165

function clone(obj) {
	// Handle the 3 simple types, and null or undefined
	if (null == obj || "object" != typeof obj) return obj;

	// Handle Date
	if (obj instanceof Date) {
		var copy = new Date();
		copy.setTime(obj.getTime());
		return copy;
	}

	// Handle Array
	if (obj instanceof Array) {
		var copy = [];
		for (var i = 0, len = obj.length; i < len; i++) {
			copy[i] = clone(obj[i]);
		}
		return copy;
	}

	// Handle Object
	if (obj instanceof Object) {
		var copy = {};
		for (var attr in obj) {
			if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
		}
		return copy;
	}

	throw new Error("Unable to copy obj! Its type isn't supported.");
}

// placeholders
$(function() {
	if (Modernizr.input.placeholder)
		$('html').addClass('placeholder');
});

function nl2br(input) {
	if (typeof input != 'undefined')
		return (input + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
	else
		return '';
}