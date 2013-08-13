/*!
 * jQuery scrollToTopOfPage Plugin v1.0
 *
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */

(function($) {
	$.fn.scrollToTopOfPage = function( options ) {

		var settings = $.extend({
			'top': 100,
			'duration': 600,
			'easing': 'swing'
		}, options);

		return this.each(function() {

			var $this = $(this);

			$this.hide();

			if (settings.top > 0)
			{
				$(window).scroll(function(){
					if ($(this).scrollTop() > settings.top) {
						$this.fadeIn();
					} else {
						$this.fadeOut();
					}
				});
			}
			else {
				$this.show();
			}

			$this.click(function( event ){
				$('html:not(:animated), body:not(:animated)').animate({ scrollTop: 0 }, settings.duration, settings.easing);
				event.preventDefault();
			});
		});
	};
})( jQuery );