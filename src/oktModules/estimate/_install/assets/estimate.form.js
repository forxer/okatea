
(function($){
	$.fn.oktEstimateForm = function(options) {

		var opts = $.extend({}, $.fn.oktEstimateForm.defaults, options);

		return this.each(function(){

			var num_product = 0;

			$(this).find('.' + opts.html.productWrapper).each(function(){

				var product_counter = parseInt($(this).attr('id').match(/[\d]+$/));

				if (product_counter != undefined && product_counter > 0) {
					handleProduct(product_counter);

					num_product++;
				}
			});

			if (num_product > 0)
			{
				$('#' + opts.html.removeProductWrapper + '_' + num_product)
					.append(getRemoveProductLink(num_product, $('#' + opts.html.productWrapper + '_' + num_product)));
			}

			$('#' + opts.html.productsWrapper).append(getAddProductLink());
		});

		function handleProduct(product_counter)
		{
			var productField = $('#' + opts.html.productField + '_' + product_counter);
			var product_id = $(productField).val();

			if (product_id <= 0 || opts.accessories[product_id] == undefined) {
				$('#' + opts.html.accessoriesWrapper + '_' + product_counter).hide();
			}

			$('#' + opts.html.productQuantityField + '_' + product_counter).spinner(opts.spinner);

			$(productField).change(function(){
				handleProductChange($(this).val(), product_counter);
			});
		}

		function handleProductChange(product_id, product_counter)
		{
			if (product_id > 0 && opts.accessories[product_id] != undefined)
			{
				var accessories_selects = $('.' + opts.html.accessoryField + '_' + product_counter);

				if ($(accessories_selects).length > 0)
				{
					$(accessories_selects).empty();

					$('.' + opts.html.accessoryQuantityField + '_' + product_counter).val(null);

					$.each(opts.accessories[product_id], function(value, key) {
						$(accessories_selects).append($('<option/>')
							.attr('value', value)
							.text(key));
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

					$(accessories_wrapper)
						.delay(200)
						.slideDown();
				}
			}
			else {
				$('#' + opts.html.accessoriesWrapper + '_' + product_counter).slideUp(function() {
					$(this).empty();
				});
			}

			if (product_id > 0) {
				$('#' + opts.html.productQuantityField + '_' + product_counter).val(1);
			}
			else {
				$('#' + opts.html.productQuantityField + '_' + product_counter).val(null);
			}
		};

		function getProduct(product_counter)
		{
			var wrapper = $('<fieldset/>')
				.attr('id', opts.html.productWrapper + '_' + product_counter)
				.addClass(opts.html.productWrapper);

			var legend = $('<legend/>')
				.text(opts.text.productTitle.replace('%s', product_counter))
				.appendTo(wrapper);


			var product_line = $('<div/>')
				.attr('id', opts.html.productLine + '_' + product_counter)
				.addClass(opts.html.productLine);

			var select_id = opts.html.productField + '_' + product_counter;

			var select_wrapper = $('<p/>')
				.addClass('field product');

			var select_label = $('<label/>')
				.attr('for', select_id)
				.text(opts.text.productLabel)
				.appendTo(select_wrapper);

			var select = $('<select/>')
				.addClass('select ' + opts.html.productField)
				.attr('id', select_id)
				.attr('name', opts.html.productField + '[' + product_counter + ']')
				.appendTo(select_wrapper);

			$.each(opts.products, function(value, key) {
				$(select).append($('<option/>')
					.attr('value', key)
					.text(value));
			});


			var quantity_id = opts.html.productQuantityField + '_' + product_counter;

			var quantity_wrapper = $('<p/>')
				.addClass('field quantity');

			var quantity_label = $('<label/>')
				.attr('for', quantity_id)
				.text(opts.text.quantityLabel)
				.appendTo(quantity_wrapper);

			var quantity = $('<input/>')
				.addClass('text spinner ' + opts.html.productQuantityField)
				.attr('id', quantity_id)
				.attr('name', opts.html.accessoryQuantityField + '[' + product_counter + ']')
				.attr('type', 'text')
				.attr('size', 10)
				.appendTo(quantity_wrapper);


			var remove_wrapper = $('<p/>')
				.attr('id', opts.html.removeProductWrapper + '_' + product_counter)
				.addClass(opts.html.removeProductWrapper);

			var prev_product_counter = product_counter-1;

			if (prev_product_counter > 0) {
				$('#' + opts.html.removeProductWrapper + '_' + prev_product_counter).empty();
			}

			$(remove_wrapper).append(getRemoveProductLink(product_counter, wrapper));

			$(product_line)
				.append(select_wrapper)
				.append(quantity_wrapper)
				.append(remove_wrapper);

			var accessories_wrapper = $('<div/>')
				.attr('id', opts.html.accessoriesWrapper + '_' + product_counter)
				.addClass(opts.html.accessoriesWrapper);

			$(wrapper)
				.append(product_line)
				.append(accessories_wrapper);

			return wrapper;
		};

		function getAddProductLink()
		{
			var wrapper = $('<p/>')
				.attr('id', opts.html.addProductWrapper)
				.addClass(opts.html.addProductWrapper);

			var link = $('<a/>')
				.attr('href', '')
				.attr('id', opts.html.addProductLink)
				.addClass(opts.html.addProductLink)
				.text(opts.text.addProduct)
				.button({
					icons: {
						primary: "ui-icon-plusthick"
					}
				})
				.click(function(event){
					event.preventDefault();

					var product_counter = $('.' + opts.html.productField).length + 1;

					$(getProduct(product_counter))
						.hide()
						.insertBefore($(wrapper))
						.delay(200)
						.slideDown();

					handleProduct(product_counter);
				})
				.appendTo(wrapper);

			return wrapper;
		};

		function getRemoveProductLink(product_counter, wrapper)
		{
			return $('<a/>')
				.attr('href', '')
				.addClass(opts.html.removeProductLink)
				.attr('id', opts.html.removeProductLink + '_' + product_counter)
				.text(opts.text.removeProduct)
				.button({
					icons: {
						primary: "ui-icon-minusthick"
					}
				})
				.hover(
					function() {
						$(wrapper).fadeTo('normal', 0.4);
					},
					function() {
						$(wrapper).fadeTo('fast', 1);
					})
				.click(function(event){
					event.preventDefault();

					$(wrapper).slideUp(function(){

						$(this).remove();

						if (product_counter > 1)
						{
							var prev_product_counter = product_counter-1;

							var prev_wrapper = $('#' + opts.html.productWrapper + '_' + prev_product_counter);

							$('#' + opts.html.removeProductWrapper + '_' + prev_product_counter)
								.append(getRemoveProductLink(prev_product_counter, prev_wrapper));
						}
					});
				});
		};

		function getAccessory(product_counter, accessory_counter, last_line)
		{
			var product_id = parseInt($('#' + opts.html.productField + '_' + product_counter).val());

			if (product_id <= 0 && opts.accessories[product_id] == undefined) {
				return null;
			}

			var wrapper = $('<div/>')
				.attr('id', opts.html.accessoryWrapper + '_' + product_counter + '_' + accessory_counter)
				.addClass(opts.html.accessoryWrapper);

			var select_id = opts.html.accessoryField + '_' + product_counter + '_' + accessory_counter;

			var select_wrapper = $('<p/>')
				.addClass('field accessory');

			var select_label = $('<label/>')
				.attr('for', select_id)
				.text(opts.text.accessoryLabel.replace('%s', accessory_counter))
				.appendTo(select_wrapper);

			var select = $('<select/>')
				.addClass('select ' + opts.html.accessoryField + '_' + product_counter)
				.attr('id', select_id)
				.attr('name', opts.html.accessoryField + '[' + product_counter + '][' + accessory_counter + ']')
				.change(function(){
					if ($(this).val() > 0) {
						$('#' + opts.html.accessoryQuantityField + '_' + product_counter + '_' + accessory_counter).val(1);
					}
					else {
						$('#' + opts.html.accessoryQuantityField + '_' + product_counter + '_' + accessory_counter).val(null);
					}
				})
				.appendTo(select_wrapper);

			$.each(opts.accessories[product_id], function(value, key) {
				$(select).append($('<option/>')
					.attr('value', value)
					.text(key));
			});


			var quantity_id = opts.html.accessoryQuantityField + '_' + product_counter + '_' + accessory_counter;

			var quantity_wrapper = $('<p/>')
				.addClass('field quantity');

			var quantity_label = $('<label/>')
				.attr('for', quantity_id)
				.text(opts.text.quantityLabel)
				.appendTo(quantity_wrapper);

			var quantity = $('<input/>')
				.addClass('text spinner ' + opts.html.accessoryQuantityField + '_' + product_counter)
				.attr('id', quantity_id)
				.attr('name', opts.html.accessoryQuantityField + '[' + product_counter + '][' + accessory_counter + ']')
				.attr('type', 'text')
				.attr('size', 10)
				.appendTo(quantity_wrapper);


			var remove_wrapper = $('<p/>')
				.attr('id', opts.html.removeAccessoryWrapper + '_' + product_counter + '_' + accessory_counter)
				.addClass(opts.html.removeAccessoryWrapper);

			if (last_line)
			{
				var prev_accessory_counter = accessory_counter-1;

				if (prev_accessory_counter > 0)
				{
					$('#' + opts.html.removeAccessoryWrapper + '_' + product_counter + '_' + prev_accessory_counter).fadeOut(400, function() {
						$(this).empty();
					});
				}

				$(remove_wrapper).append(getRemoveAccessoryLink(product_id, product_counter, accessory_counter, wrapper));
			}

			$(wrapper)
				.append(select_wrapper)
				.append(quantity_wrapper)
				.append(remove_wrapper);

			return wrapper;
		};

		function getAddAccessoryLink(product_counter)
		{
			var accessory_counter = $('.' + opts.html.accessoryField + '_' + product_counter).length;

			$('#' + opts.html.addAccessoryWrapper + '_' + product_counter).remove();

			var wrapper = $('<p/>')
				.attr('id', opts.html.addAccessoryWrapper + '_' + product_counter)
				.addClass(opts.html.addAccessoryWrapper);

			var link = $('<a/>')
				.attr('href', '')
				.attr('id', opts.html.addAccessoryLink + '_' + product_counter)
				.addClass(opts.html.addAccessoryLink)
				.text(opts.text.addAccessory)
				.button({
					icons: {
						primary: "ui-icon-plus"
					}
				})
				.click(function(event){
					event.preventDefault();

					$(getAccessory(product_counter, accessory_counter+1, true))
						.hide()
						.insertBefore($(wrapper))
						.delay(200)
						.slideDown();

					$('#' + opts.html.accessoriesWrapper + '_' + product_counter)
						.append(getAddAccessoryLink(product_counter));

					$('#' + opts.html.accessoryQuantityField + '_' + product_counter + '_' + (accessory_counter+1)).spinner(opts.spinner);
				})
				.appendTo(wrapper);

			return wrapper;
		};

		function getRemoveAccessoryLink(product_id, product_counter, accessory_counter, wrapper)
		{
			return $('<a/>')
				.attr('href', '')
				.addClass(opts.html.removeAccessoryLink)
				.attr('id', opts.html.removeAccessoryLink + '_' + product_counter + '_' + accessory_counter)
				.text(opts.text.removeAccessory)
				.button({
					icons: {
						primary: "ui-icon-minus"
					}
				})
				.hover(
					function() {
						$(wrapper).fadeTo('normal', 0.4);
					},
					function() {
						$(wrapper).fadeTo('fast', 1);
					})
				.click(function(event){
					event.preventDefault();

					$(wrapper).slideUp(function(){

						$(this).remove();

						if (accessory_counter > 1)
						{
							var prev_accessory_counter = accessory_counter-1;

							var prev_wrapper = $('#' + opts.html.accessoryWrapper + '_' + product_counter + '_' + prev_accessory_counter);

							$('#' + opts.html.removeAccessoryWrapper + '_' + product_counter + '_' + prev_accessory_counter)
								.append(getRemoveAccessoryLink(product_id, product_counter, prev_accessory_counter, prev_wrapper))
								.fadeIn('fast');
						}

						$('#' + opts.html.accessoriesWrapper + '_' + product_counter)
							.append(getAddAccessoryLink(product_counter));
					});
				});
		};

	}; // $.fn.oktEstimateForm

	$.fn.oktEstimateForm.defaults = {
		text: {
			productTitle: 'Product %s',
			addProduct: 'Add a product',
			removeProduct: 'Remove this product',
			productLabel: 'Choose a product',
			quantityLabel: 'Quantity',
			accessoryLabel: 'Accessory %s',
			addAccessory: 'Add an accessory',
			removeAccessory: 'Remove this accessory'
		},
		html: {
			productsWrapper: 'products_wrapper',
			productWrapper: 'product_wrapper',
			productLine: 'product_line',
			productField: 'p_product',
			productQuantityField: 'p_product_quantity',
			addProductWrapper: 'add_product_wrapper',
			addProductLink: 'add_product_link',
			removeProductWrapper: 'remove_product_wrapper',
			removeProductLink: 'remove_product_link',
			removeProductWrapper: 'remove_accessory_wrapper',
			removeProductLink: 'remove_accessory_link',
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
		products: {},
		accessories: {},
		spinner: { min: 0 }
	};

})(jQuery);

