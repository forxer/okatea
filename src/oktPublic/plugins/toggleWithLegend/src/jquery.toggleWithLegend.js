
(function($){
	jQuery.fn.toggleWithLegend = function(target,s) {
		var defaults = {
			img_on_src: 'img/plus/url',
			img_on_alt: 'show',
			img_off_src: 'img/minus/url',
			img_off_alt: 'hide',
			hide: true,
			speed: 0,
			legend_click: false,
			fn: false, // A function called on first display,
			cookie: false,
			reverse_cookie: false // Reverse cookie behavior
		};
		var o = jQuery.extend(defaults,s);

		if (!target) {
			return this;
		}

		var set_cookie = o.hide ^ o.reverse_cookie;

		if (o.cookie && jQuery.cookie(o.cookie)) {
			o.hide = o.reverse_cookie;
		}

		var toggle = function(img,speed) {
			speed = speed || 0;
			if (o.hide) {
				img.src = o.img_on_src;
				img.alt = o.img_on_alt;
				target.hide(speed);
			}
			else {
				img.src = o.img_off_src;
				img.alt = o.img_off_alt;
				target.show(speed);
				if (o.fn) {
					o.fn.apply(target);
					o.fn = false;
				}
			}

			if (o.cookie && set_cookie) {
				if (o.hide ^ o.reverse_cookie) {
					jQuery.cookie(o.cookie,'',{expires: -1});
				} else {
					jQuery.cookie(o.cookie,1,{expires: 30});
				}
			}

			o.hide = !o.hide;
		};

		return this.each(function() {

			var img = document.createElement('img');
			img.src = o.img_off_src;
			img.alt = o.img_off_alt;
			$(img).css('vertical-align','baseline');

			var a = document.createElement('a');
			a.href= '#';
			$(a).append(img);
			$(a).css({
				border: 'none',
				outline: 'none'
			});

			var ctarget = o.legend_click ? this : a;

			$(ctarget).css('cursor','pointer');
			$(ctarget).click(function(event) {
				event.preventDefault();
				toggle(img,o.speed);
			});

			toggle(img);
			$(this).prepend(' ').prepend(a);
		});
	};

})(jQuery);
