/**
 * maxlength <a title="jQuery" href="http://www.devzone.fr/tag/jquery">jQuery</a> plugin
 *
 * @author Fabrice Planchette <http://www.fabriceplanchette.fr>
 * @version 1.0
 * @requires <a title="jQuery" href="http://www.devzone.fr/tag/jquery">jQuery</a>
 * @see http://www.devzone.fr/plugin-jquery-maxlength
 * @param int maxlength Nombre de caractères max
 * @param boolean autoload Calcul au chargement ?
 * @param string mess_eq Message si equivalent
 * @param string mess_inf Message si inférieur
 * @param string mess_sup Message si supérieur
 * @description Calcul et affiche le restant/surplus de la saisie dans un input/textarea
 */

(function( $ ){

	$.fn.maxlength = function(options) {
		// Les variables par défaut
		var defaults = {
			maxlength: 25,
			autoload: true,
			mess_eq: "Nombre de caractères maximum atteint.",
			mess_inf: "Caractères restants : ",
			mess_sup: "Caractères en trop : "
		}
		var options = $.extend(defaults, options);

		return this.each(function()
		{
			var $$ = $(this);

			if( options.autoload==true ) {
				info( parseInt( $$.val().length ) );
			}

			$$.keyup(function() {
				info( parseInt( $$.val().length ) );
			});

			/**
			 * Quelle info va-t-on afficher ?
			 *
			 * @name info
			 * @param int length
			 */
			function info(length)
			{
				// on retire un éventuel précédent message
				$("#maxlength-" + $$.attr("id")).remove();

				// Equivalent, inférieur ou supérieur
				if( length == options.maxlength ) {
					msg = options.mess_eq;
				}
				else if( length < parseInt(options.maxlength) ) {
					msg = options.mess_inf + ( parseInt( options.maxlength ) - length );
				}
				else {
					msg = options.mess_sup + ( length - parseInt(options.maxlength) );
				}

				// Affichage
				$$.after('<span class="maxlength" id="maxlength-' + $$.attr('id') + '">' + msg + '</span>');
			}
		});
	};

})( jQuery );

