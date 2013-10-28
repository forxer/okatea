
(function($){
	$.fn.oktEstimateForm = function(options) {

		var opts = $.extend({}, $.fn.oktEstimateForm.defaults, options);

		return this.each(function(){

			$(this).find('.' + opts.html.productWrapper).each(function(){

				var product_counter = parseInt($(this).attr('id').match(/[\d]+$/));

				if (product_counter != undefined && product_counter > 0) {
					handleProductWrapper(product_counter);
				}
			});
		});

		function handleProductWrapper(product_counter)
		{
			$('#' + opts.html.productQuantityField + '_' + product_counter).spinner(opts.spinner);

			$('#' + opts.html.productField + '_' + product_counter).change(function(){
				handleProductChange($(this).val(), product_counter);
			});
		};

		function handleProductChange(product_id, product_counter)
		{
			$('#' + opts.html.productQuantityField + '_' + product_counter).val(null);

			if (product_id > 0 && opts.accessories[product_id] != undefined)
			{
				var accessories_selects = $('.' + opts.html.accessoryField + '_' + product_counter);

				if ($(accessories_selects).length > 0)
				{
					$(accessories_selects).empty();

					$('.' + opts.html.accessoryQuantityField + '_' + product_counter).val(null);

					$.each(opts.accessories[product_id], function(value, key) {
						$(accessories_selects).append($('<option></option>')
							.attr('value', key)
							.text(value));
					});
				}
				else
				{
					var accessories_wrapper = $('#' + opts.html.accessoriesWrapper + '_' + product_counter);

					for (var i = 1; i <= opts.default_accessories_number; i++)
					{
						$(accessories_wrapper).append(getAccessory(product_counter, i, (i == opts.default_accessories_number)));

						$('#' + opts.html.accessoryQuantityField + '_' + product_counter + '_' + i).spinner(opts.spinner);
					}

					$(accessories_wrapper).append(getAddAccessoryLink(product_counter));

					$(accessories_wrapper).slideDown();
				}
			}
			else
			{
				$('#' + opts.html.accessoriesWrapper + '_' + product_counter).slideUp().empty();
			}
		};

		function getAccessory(product_counter, accessory_counter, last_line)
		{
			var product_id = parseInt($('#' + opts.html.productField + '_' + product_counter).val());

			if (product_id <= 0 && opts.accessories[product_id] == undefined) {
				return null;
			}

			var wrapper = $('<div></div>')
				.attr('id', opts.html.accessoryWrapper + '_' + product_counter + '_' + accessory_counter)
				.addClass(opts.html.accessoryWrapper);

			var select_id = opts.html.accessoryField + '_' + product_counter + '_' + accessory_counter;

			var select_wrapper = $('<p></p>')
				.addClass('field accessory');

			var select_label = $('<label></label>')
				.attr('for', select_id)
				.text(opts.text.accessoryLabel.replace('%s', accessory_counter))
				.appendTo(select_wrapper);

			var select = $('<select></select>')
				.addClass('select ' + opts.html.accessoryField + '_' + product_counter)
				.attr('id', select_id)
				.attr('name', opts.html.accessoryField + '[' + product_counter + '][' + accessory_counter + ']')
				.appendTo(select_wrapper);

			$.each(opts.accessories[product_id], function(value, key) {
				$(select).append($('<option></option>')
					.attr('value', key)
					.text(value));
			});


			var quantity_id = opts.html.accessoryQuantityField + '_' + product_counter + '_' + accessory_counter;

			var quantity_wrapper = $('<p></p>')
				.addClass('field quantity');

			var quantity_label = $('<label></label>')
				.attr('for', quantity_id)
				.text(opts.text.quantityLabel)
				.appendTo(quantity_wrapper);

			var quantity = $('<input></input>')
				.addClass('text spinner ' + opts.html.accessoryQuantityField + '_' + product_counter)
				.attr('id', quantity_id)
				.attr('name', opts.html.accessoryQuantityField + '[' + product_counter + '][' + accessory_counter + ']')
				.attr('type', 'text')
				.attr('size', 10)
				.appendTo(quantity_wrapper);


			var remove_wrapper = $('<p></p>')
				.attr('id', opts.html.removeAccessoryWrapper + '_' + product_counter + '_' + accessory_counter)
				.addClass(opts.html.removeAccessoryWrapper);

			if (last_line)
			{
				var prev_accessory_counter = accessory_counter-1;

				if (prev_accessory_counter > 0) {
					$('#' + opts.html.removeAccessoryWrapper + '_' + product_counter + '_' + prev_accessory_counter).empty();
				}

				$(remove_wrapper).append(getRemoveAccessoryLink(product_id, product_counter, accessory_counter, wrapper));
			}

			$(wrapper)
				.append(select_wrapper)
				.append(quantity_wrapper)
				.append(remove_wrapper);

			return wrapper;
		};

		function getRemoveAccessoryLink(product_id, product_counter, accessory_counter, wrapper)
		{
			return $('<a />')
				.attr('href', '')
				.addClass(opts.html.removeAccessoryLink)
				.attr('id', opts.html.removeAccessoryLink + '_' + product_counter + '_' + accessory_counter)
				.text(opts.text.removeAccessory)
				.click(function(event){
					event.preventDefault();
					$(wrapper).remove();

					if (accessory_counter > 1)
					{
						var prev_accessory_counter = accessory_counter-1;

						var prev_wrapper = $('#' + opts.html.accessoryWrapper + '_' + product_counter + '_' + prev_accessory_counter);

						$('#' + opts.html.removeAccessoryWrapper + '_' + product_counter + '_' + prev_accessory_counter)
							.append(getRemoveAccessoryLink(product_id, product_counter, prev_accessory_counter, prev_wrapper));
					}

					$('#' + opts.html.accessoriesWrapper + '_' + product_counter)
						.append(getAddAccessoryLink(product_counter));
				});
		};

		function getAddAccessoryLink(product_counter)
		{
			var accessory_counter = $('.' + opts.html.accessoryField + '_' + product_counter).length;

			$('#' + opts.html.addAccessoryWrapper + '_' + product_counter).remove();

			var add_wrapper = $('<p></p>')
				.attr('id', opts.html.addAccessoryWrapper + '_' + product_counter)
				.addClass(opts.html.addAccessoryWrapper);

			var link = $('<a />')
				.attr('href', '')
				.attr('id', opts.html.addAccessoryLink + '_' + product_counter)
				.addClass(opts.html.addAccessoryLink)
				.text(opts.text.addAccessory)
				.click(function(event){
					event.preventDefault();

					$(getAccessory(product_counter, accessory_counter+1, true))
						.insertBefore($(add_wrapper));

					$('#' + opts.html.accessoriesWrapper + '_' + product_counter)
						.append(getAddAccessoryLink(product_counter));

					$('#' + opts.html.accessoryQuantityField + '_' + product_counter + '_' + (accessory_counter+1)).spinner(opts.spinner);
				})
				.appendTo(add_wrapper);

			return add_wrapper;
		};
	};

	$.fn.oktEstimateForm.defaults = {
		text: {
			quantityLabel: 'Quantity',
			accessoryLabel: 'Accessory %s',
			addAccessory: 'Add an accessory',
			removeAccessory: 'Remove this accessory'
		},
		html: {
			productWrapper: 'product_wrapper',
			productField: 'p_product',
			productQuantityField: 'p_product_quantity',
			accessoriesWrapper: 'accessories_wrapper',
			accessoryWrapper: 'accessory_wrapper',
			accessoryField: 'p_accessory',
			accessoryQuantityField: 'p_accessory_quantity',
			addAccessoryWrapper: 'add_accessory_wrapper',
			addAccessoryLink: 'add_accessory_link',
			removeAccessoryWrapper: 'remove_accessory_wrapper',
			removeAccessoryLink: 'remove_accessory_link'
		},
		default_accessories_number: 2,
		accessories: {},
		spinner: { min: 0 }
	};


})(jQuery);

