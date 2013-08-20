// Blend 2.4 for jQuery 1.3+
// Copyright (c) 2013 Jack Moore - jack@colorpowered.com
// License: http://www.opensource.org/licenses/mit-license.php

// Blend creates a 2nd layer on top of the selected element.
// This layer is faded in and out to create the effect.  The original, bottom layer
// has it's class set to 'hover' and remains that way for the duration to
// keep the CSS :hover state from being apparent when the object is moused-over.
(function ($, window) {

	var blend = $.fn.blend = function (speed, callback) {
		var background = 'background',
		properties = [
			background + 'Color',
			background + 'Image',
			background + 'Repeat',
			background + 'Attachment',
			background + 'Position', // Standards browsers
			background + 'PositionX', // IE only
			background + 'PositionY' // IE only
		];

		speed = speed || $.fn.blend.speed;
		callback = callback || $.fn.blend.callback;

		this.not('.jQblend').each(function () {
			var
			hover = $('<span style="position:absolute;top:0;bottom:0;left:0;right:0;z-index:-1;"/>')[0],
			base = this,
			style = base.currentStyle || window.getComputedStyle(base, null);

			if (style.position !== 'absolute') {
				base.style.position = 'relative';
			}
			if (style.zIndex === 'auto') {
				base.style.zIndex = 1;
			}

			$.each(properties, function(){
				hover.style[this] = style[this];
			});

			$(base)
			.addClass('hover jQblend')
			.prepend(hover)
			.hover(function () {
				$(hover).stop().fadeTo(speed, 0, function () {
					if ($.isFunction(callback)) {
						callback();
					}
				});
			}, function () {
				$(hover).stop().fadeTo(speed, 1);
			});
		});

		return this;
	};

	blend.speed = 350;
	blend.callback = false;

}(jQuery, this));