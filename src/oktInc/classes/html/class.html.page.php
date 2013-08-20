<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class htmlPage
 * @ingroup okt_classes_html
 * @brief Permet de gérer quelques éléments courant à une page HTML
 *
 * L'élément title, le javascript, les CSS
 * et ce qu'on veut lui ajouter...
 *
 * Fournis également tout un ensemble de méthodes pour la mise en place de
 * widgets.
 *
 */
class htmlPage
{
	/**
	 * L'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	/**
	 * Les CSS de la page
	 * @var object htmlCss
	 */
	public $css;

	/**
	 * Le JS de la page
	 * @var object htmlJs
	 */
	public $js;

	/**
	 * Le contenu des meta keywords de la page
	 * @var string
	 */
	public $meta_keywords = null;

	/**
	 * Le contenu de la meta description de la page
	 * @var string
	 */
	public $meta_description = null;

	/**
	 * L'élément title de la page
	 * @var string
	 */
	protected $sTitleTag = '';

	/**
	 * La pile pour la construction de l'élément title de la page
	 * @var array
	 */
	protected $aTitleTagStack = array();

	/**
	 * L'ID de la page
	 * @var string
	 */
	protected $sPageId = null;

	/**
	 * Le titre de la page
	 * @var string
	 */
	protected $sTitle = '';

	/**
	 * Le titre SEO de la page (typiquement le h1)
	 * @var string
	 */
	protected $sTitleSeo = '';

	/**
	 * L'ID du module en cours
	 * @var string
	 */
	public $module = null;

	/**
	 * La pile des RTE disponibles
	 * @var array
	 */
	protected $rteList = array();

	/**
	 * La pile des LBL disponibles
	 * @var array
	 */
	protected $lblList = array();

	/**
	 * La pile des paiements CB disponibles
	 * @var array
	 */
	protected $cbpList = array();

	/**
	 * La pile des Captcha disponibles
	 * @var array
	 */
	protected $captchaList = array();

	/**
	 * La partie à afficher (traditionnellement 'admin' ou 'public')
	 * @var string
	 */
	public $sPart = null;


	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($okt,$sPart=null)
	{
		$this->okt = $okt;

		$this->css = new htmlCss($sPart);
		$this->js = new htmlJs($sPart);

		$this->sPart = $sPart;
	}

	/* Gestion de l'élément title des pages
	----------------------------------------------------------*/

	/**
	 * Get title tag
	 *
	 * @param string $sTitleTagSep
	 * @return string
	 */
	public function titleTag($sTitleTagSep=' - ')
	{
		return implode($sTitleTagSep,array_filter($this->aTitleTagStack));
	}

	/**
	 * Add title tag
	 *
	 * @param string $sTitle
	 * @return void
	 */
	public function addTitleTag($sTitle)
	{
		array_unshift($this->aTitleTagStack,$sTitle);
	}

	/**
	 * Reset title tag
	 *
	 * @return void
	 */
	public function resetTitleTag()
	{
		$this->aTitleTagStack = array();
	}


	/* Gestion de l'id de la page
	----------------------------------------------------------*/

	public function pageId($str)
	{
		$this->sPageId = $str;
	}

	public function getPageId()
	{
		return html::escapeHTML($this->sPageId);
	}

	public function hasPageId()
	{
		return !empty($this->sPageId);
	}


	/* Gestion du titre de la page
	----------------------------------------------------------*/

	public function setTitle($str)
	{
		$this->sTitle = trim($str);
	}

	public function unsetTitle()
	{
		$this->sTitle = '';
	}

	public function getTitle()
	{
		return $this->sTitle;
	}

	public function hasTitle()
	{
		return !empty($this->sTitle);
	}


	/* Gestion du titre SEO de la page
	----------------------------------------------------------*/

	public function setTitleSeo($str)
	{
		$this->sTitleSeo = trim($str);
	}

	public function unsetTitleSeo()
	{
		$this->sTitleSeo = '';
	}

	public function getTitleSeo()
	{
		return $this->sTitleSeo;
	}

	public function hasTitleSeo()
	{
		return !empty($this->sTitleSeo);
	}


	/**
	 * Retourne le chemon de base des URL
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getBaseUrl($sLanguage=null)
	{
		$str = $this->okt->config->app_path;

		if (!$this->okt->languages->unique) {
			$str .= ($sLanguage !== null ? $sLanguage : $this->okt->user->language).'/';
		}

		return $str;
	}

	/* UI widgets
	----------------------------------------------------------*/

	/**
	 * Met en place l'accordion dans la page (UI accordion)
	 *
	 * Voir http://jqueryui.com/demos/accordion/#options
	 * pour la liste des options possibles
	 *
	 * @param array $user_options
	 * @param string $element
	 * @return void
	 */
	public function accordion($user_options=array(), $element='#accordion')
	{
		$options = array(
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').accordion('.json_encode($options).');
		');
	}

	/**
	 * Met en place le datepicker dans la page (UI datepicker)
	 *
	 * Voir http://jqueryui.com/demos/datepicker/#options
	 * pour la liste des options possibles
	 *
	 * @param array $user_options
	 * @param string $element
	 * @return void
	 */
	public function datePicker($user_options=array(), $element='.datepicker')
	{
		$options = array(
			'dateFormat' => 'dd-mm-yy',
			'changeMonth' => true,
			'changeYear' => true
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/ui/i18n/jquery-ui-i18n.min.js');

		$this->js->addReady('
			$.datepicker.setDefaults($.datepicker.regional[\''.$GLOBALS['okt']->user->language.'\']); '.
			'jQuery(\''.$element.'\').datepicker('.json_encode($options).');
		');
	}

	/**
	 * Met en place une boite de dialogue dans la page (UI dialog)
	 *
	 * Voir http://jqueryui.com/demos/dialog/#options
	 * pour la liste des options possibles
	 *
	 * @param array $user_options
	 * @param string $element
	 * @return void
	 */
	public function dialog($user_options=array(), $element='.dialog')
	{
		$options = array(
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').dialog('.json_encode($options).');
		');
	}

	/**
	 * Met en place les onglets dans la page (UI tabs)
	 *
	 * Voir http://jqueryui.com/demos/tabs/#options
	 * pour la liste des options possibles
	 *
	 * @param array $user_options
	 * @param string $element
	 * @return void
	 */
	public function tabs($user_options=array(), $element='#tabered')
	{
		$options = array(
			'show' => true,
			'hide' => true,
			'heightStyle' => 'content'
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addScript('
			var tabsOptions = '.json_encode($options).';

			var hasHash = location.href.indexOf("#");

			if (hasHash == -1)
			{

				var cookie = $.cookie("oktCurrentTab");
				if (cookie) {
					tabsOptions.active = parseInt(cookie) ;
				}

				tabsOptions.activate = function (event, ui) {
					$.cookie("oktCurrentTab", ui.newTab.index());
				};
			}
		');

		$this->js->addReady('
			jQuery(\''.$element.'\').tabs(tabsOptions);
		');
	}


	/* Others widgets
	----------------------------------------------------------*/

	public function langSwitcher($target,$placeholder)
	{
		global $okt;

		# récupération de la liste des langues et encodage
		$jsonlanguages = json_encode(array_values($okt->languages->list));

		$this->js->addScript('

			// zone a traiter
			var target = $("'.$target.'");

			// object of language list
			var oktLanguages = jQuery.parseJSON(\''.$jsonlanguages.'\');

			// construction des boutons sur la liste des langues
			var buttons = new Array;

			$(oktLanguages).each(function(){
				var oktLanguage = this;

				var button = $(\'<a href="#" class="lang-switcher-button" data-lang-code="\' + oktLanguage.code + \'">\'
				+ \'<img src="'.OKT_PUBLIC_URL.'/img/flags/\' + oktLanguage.img + \'" alt="\' + oktLanguage.title + \'" /></a>\')
				.click(function(e) {
					switch_language(oktLanguage.code);
					e.preventDefault();
				});

				buttons.push(button);
			});

			// on ajoute ces boutons là où il faut
			buttons.reverse();
			$(buttons).each(function(){
				$("'.$placeholder.'").prepend(this)
			});

			// fonction pour activer une langue
			function switch_language(lang)
			{
				// affichage/masquage des parties
				$("[lang]", target).each(function() {
					if (lang == $(this).attr("lang")) {
							$(this).show();
					} else {
							$(this).hide();
					}
				});

				// boucle sur les boutons
				$(".lang-switcher-button").each(function() {

					// Switch class "on"/"off"
					if (lang == $(this).attr("data-lang-code")) {
							$(this).
							addClass("button_on").
							removeClass("button_off").
							addClass("ui-state-active").
							removeClass("ui-state-default");
					} else {
							$(this).
							removeClass("button_on").
							addClass("button_off").
							removeClass("ui-state-active").
							addClass("ui-state-default");
					}
				});
			}
		');

		$this->js->addReady('
			switch_language("'.$okt->user->language.'");
		');
	}

	/**
	 * Met en place les "smart colonnes"
	 *
	 * Voir http://www.sohtanaka.com/web-design/smart-columns-w-css-jquery/
	 *
	 * légèrement "forké" pour okatea
	 *
	 * <ul class="smartColumns">
	 *   <li class="column"></li>
	 *   <li class="column"></li>
	 *   <li class="column"></li>
	 * </ul>
	 *
	 * @return void
	 */
	public function smartColumns($width=220)
	{
		# Create a function that calculates the smart columns
		$this->js->addScript('
			function smartColumns() {

				var jElement = jQuery("ul.smartColumns");

				// Reset column size to a 100% once view port has been adjusted
				jElement.css({"width" : "100%"});

				// Get the width of row
				var colWrap = jElement.width();

				// Find how many columns of $width px can fit per row / then round it down to a whole number
				var colNum = Math.floor(colWrap / '.$width.');

				// Get the width of the row and divide it by the number of columns it can fit / then round it down to a whole number. This value will be the exact width of the re-adjusted column
				var colFixed = Math.floor(colWrap / colNum);

				// Set exact width of row in pixels instead of using % - Prevents cross-browser bugs that appear in certain view port resolutions.
				jElement.css({"width" : colWrap});

				// Set exact width of the re-adjusted column
				jQuery("ul.smartColumns li.column").css({"width" : colFixed});
			}
		');

		$this->js->addReady('
			// Execute the smartColumns function when page loads
			smartColumns();

			// Each time the viewport is adjusted/resized, execute the smartColumns function
			jQuery(window).resize(function () {
				smartColumns();
			});
		');
	}

	/**
	 * Met en place un "roundabout"
	 *
	 * Voir http://fredhq.com/projects/roundabout/#options
	 * pour la liste des options possibles
	 *
	 * @param array $user_options
	 * @param string $element
	 * @return void
	 */
	public function roundabout($user_options=array(), $element='.roundabout')
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/easing/jquery.easing.min.js');
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/roundabout/jquery.roundabout.min.js');

		$options = array(
		);

		if (!empty($user_options)) {
			$options = array_merge($options,(array)$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').roundabout('.json_encode($options).');
		');
	}

	/**
	 * Met en place "jPicker"
	 *
	 * Voir http://www.digitalmagicpro.com/jPicker/
	 *
	 * @param array $element
	 * @param array $user_options
	 * @return void
	 */
	public function colorpicker($element='#colorpicker',$user_options=array())
	{
		$this->css->addFile(OKT_PUBLIC_URL.'/plugins/jpicker/css/jPicker.min.css');
		$this->js->addFile(OKT_PUBLIC_URL.'/plugins/jpicker/jpicker.min.js');

		# patch for 1.1.5 missing this property
		# TODO : remove when it will be corrected
		$this->css->addCss('.jPicker tr, .jPicker td { vertical-align: middle; } ');

		l10n::set(OKT_LOCALES_PATH.'/'.$GLOBALS['okt']->user->language.'/jPicker');

		$options = array(
			'images' => array(
				'clientPath' => OKT_PUBLIC_URL.'/plugins/jpicker/images/'
			),
			'localization' => array(
				'text' => array(
					'title' => __('jpicker_text_title'),
					'newColor' => __('jpicker_text_newColor'),
					'currentColor' => __('jpicker_text_currentColor'),
					'ok' => __('jpicker_text_ok'),
					'cancel' => __('jpicker_text_cancel')
				),
				'tooltips' => array(
					'colors' => array(
						'newColor' => __('jpicker_tooltips_colors_newColor'),
						'currentColor' => __('jpicker_tooltips_colors_currentColor')
					),
					'buttons' => array(
						'ok' => __('jpicker_tooltips_buttons_ok'),
						'cancel' => __('jpicker_tooltips_buttons_cancel')
					),
					'hue' => array(
						'radio' => __('jpicker_tooltips_hue_radio'),
						'textbox' => __('jpicker_tooltips_hue_textbox')
					),
					'saturation' => array(
						'radio' => __('jpicker_tooltips_saturation_radio'),
						'textbox' => __('jpicker_tooltips_saturation_textbox')
					),
					'value' => array(
						'radio' => __('jpicker_tooltips_value_radio'),
						'textbox' => __('jpicker_tooltips_value_textbox')
					),
					'red' => array(
						'radio' => __('jpicker_tooltips_red_radio'),
						'textbox' => __('jpicker_tooltips_red_textbox')
					),
					'green' => array(
						'radio' => __('jpicker_tooltips_green_radio'),
						'textbox' => __('jpicker_tooltips_green_textbox')
					),
					'blue' => array(
						'radio' => __('jpicker_tooltips_blue_radio'),
						'textbox' => __('jpicker_tooltips_blue_textbox')
					),
					'alpha' => array(
						'radio' => __('jpicker_tooltips_alpha_radio'),
						'textbox' => __('jpicker_tooltips_alpha_textbox')
					),
					'hex' => array(
						'textbox' => __('jpicker_tooltips_hex_textbox'),
						'alpha' => __('jpicker_tooltips_hex_alpha')
					)
				)
			)
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').jPicker('.json_encode($options).');
		');
	}

	/**
	 * Met en place un "treeview"
	 *
	 * Voir http://docs.jquery.com/Plugins/Treeview/treeview#options
	 * pour la liste des options possibles
	 *
	 * @param array $user_options
	 * @param string $element
	 * @return void
	 */
	public function treeview($user_options=array(), $element='.browser')
	{
		$this->css->addFile(OKT_PUBLIC_URL.'/plugins/treeview/jquery.treeview.css');
		$this->js->addFile(OKT_PUBLIC_URL.'/plugins/treeview/jquery.treeview.min.js');

		$options = array(
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').treeview('.json_encode($options).');
		');
	}

	public function updatePermissionsCheckboxes($prefix='p_perm_g_')
	{
		$this->js->addScript('

			function updatePermissionsCheckboxes()
			{
				if ($("#'.$prefix.'0").is(":checked")) {
					$("input[id^=\''.$prefix.'\']").not("#'.$prefix.'0").prop("disabled", true);
				}
				else {
					$("input[id^=\''.$prefix.'\']").not("#'.$prefix.'0").prop("disabled", false);
				}
			}

			$("#'.$prefix.'0").click(function(){
				updatePermissionsCheckboxes();
			});

		');

		$this->js->addReady('
			updatePermissionsCheckboxes();
		');
	}

	public function loader($element)
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/blockUI/jquery.blockUI.min.js');

		$this->js->addReady('
			jQuery(\''.$element.'\').click(function() {
				$.blockUI({
					theme:    true,
					title:    "'.__('c_c_Please_wait').'",
					message:  "<p><img src=\"'.OKT_PUBLIC_URL.'/img/ajax-loader/big-circle-ball.gif\" alt=\"\" style=\"float: left; margin: 0 1em 1em 0\" /> '.__('c_c_Please_wait_txt').'</p>"
				});
			});
		');
	}

	public function cycle($element='#diaporama', $user_options=array())
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/cycle/jquery.cycle.min.js');

		$options = array(
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').cycle('.json_encode($options).');
		');
	}

	public function cycleLite($element='#diaporama', $user_options=array())
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/cycle/jquery.cycle.lite.min.js');

		$options = array(
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery(\''.$element.'\').cycle('.json_encode($options).');
		');
	}

	public function filterControl($showFilter=false)
	{
		$this->js->addReady('
			if (!'.(integer)$showFilter.') {
				jQuery("#filters-form").hide();
			}
			jQuery("#filter-control").click(function() {
				jQuery("#filters-form").slideToggle();
				return false;
			});
		');
/*
		$this->js->addReady('
			var c = $("#filter-control");
			c.css("display","inline");
			$("#filters-form").hide();
			c.click(function() {
				$(this).hide();
				$("#filters-form").slideToggle();
				return false;
			});
		');
*/
	}

	public function strToSlug($command,$target,$user_options=array())
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/stringToSlug/jquery.stringToSlug.min.js');

		$options = array(
			'setEvents' => 'keyup keydown blur',
			'getPut' => $target,
			'space' => '-'
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery("'.$command.'").stringToSlug('.json_encode($options).');
		');
	}

	public function toggleWithLegend($command,$target,$user_options=array())
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/toggleWithLegend/jquery.toggleWithLegend.min.js');

		$options = array(
			'img_on_src' => OKT_PUBLIC_URL.'/img/ico/plus.png',
			'img_on_alt' => html::escapeJS(__('c_c_action_show')),
			'img_off_src' => OKT_PUBLIC_URL.'/img/ico/minus.png',
			'img_off_alt' => html::escapeJS(__('c_c_action_hide')),
			'hide' => true,
			'speed' => 0,
			'legend_click' => true,
			'fn' => false, // A function called on first display,
			'cookie' => false,
			'reverse_cookie' => false // Reverse cookie behavior
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addReady('
			jQuery("#'.$command.'").toggleWithLegend(jQuery("#'.$target.'"),'.json_encode($options).');
		');
	}

	public function openLinkInDialog($element='#kink_id',$user_options=array(), $htmlID = null)
	{
		static $loaded = null;

		$options = array(
			'width' => 700,
			'height' => 500
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		if(is_null($loaded))
		{
			$this->js->addScript('
				function openInDialog(element, options, htmlID)
				{
					jQuery(element).click(function(event){

						var dialog = jQuery("<div style=\"display:hidden\"></div>").appendTo("body");
						dialog.load(((htmlID == "") ? this.href : this.href + " "+htmlID), {},
							function (responseText, textStatus, XMLHttpRequest) {
								dialog.dialog(options);
							}
						);

						event.preventDefault();
					});
				}
			');
			$loaded = true;
		}
		$this->js->addReady('
			openInDialog("'.$element.'", '.json_encode($options).', "'.$htmlID.'");
		');
	}

	public function lockable($element='.lockable',$note='.lockable-note')
	{
		$this->js->addReady('
			jQuery(\''.$element.'\').each(function() {
				var current_lockable_div = this;
				jQuery(this).find(\''.$note.'\').hide();
				jQuery(this).find("input").each(function() {
					//this.disabled = true;
					jQuery(this)
						.prop("readonly", true)
						.addClass("disabled")
						.width((jQuery(this).width()-14) + "px");

					var imgE = document.createElement("img");
					imgE.src = "'.OKT_PUBLIC_URL.'/img/ico/lock.png";
					imgE.style.position = "absolute";
					imgE.style.top = "1.7em";
					imgE.style.left = ($(this).width()+4) + "px";
					jQuery(imgE).css("cursor","pointer");

					jQuery(imgE).click(function() {
						jQuery(this).hide();
						jQuery(this).prev("input").each(function() {
							//this.disabled = false;
							jQuery(this)
								.prop("readonly", false)
								.removeClass("disabled")
								.width(($(this).width()+14) + "px")
								.focus();
						});
						jQuery(this).next(\''.$note.'\').show();
					});

					jQuery(this).parent().css("position","relative");
					jQuery(this).after(imgE);
				});
			});
		');
	}

	public function checkboxHelper($form_id,$helper_id)
	{
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/checkboxes/jquery.checkboxes.min.js');

		$this->js->addReady('
			$(\'<a href="#" id="'.$helper_id.'-button-select-all">'.__('c_c_select_all').'</a>\')
			.click(function(event) {
				$("#'.$form_id.'").checkCheckboxes();
				event.preventDefault();
				$(this).blur();
			}).appendTo("#'.$helper_id.'");

			$(\'<a href="#" id="'.$helper_id.'-button-select-none">'.__('c_c_select_none').'</a>\')
			.click(function(event) {
				$("#'.$form_id.'").unCheckCheckboxes();
				event.preventDefault();
				$(this).blur();
			}).appendTo("#'.$helper_id.'");

			$(\'<a href="#" id="'.$helper_id.'-button-select-toggle">'.__('c_c_toggle_select').'</a>\')
			.click(function(event) {
				$("#'.$form_id.'").toggleCheckboxes();
				event.preventDefault();
				$(this).blur();
			}).appendTo("#'.$helper_id.'");

			$("#'.$helper_id.'").buttonset();
			$("#'.$helper_id.'-button-select-all").button("option", "icons", {
				primary: "ui-icon-check"
			});
			$("#'.$helper_id.'-button-select-none").button("option", "icons", {
				primary: "ui-icon-closethick"
			});
			$("#'.$helper_id.'-button-select-toggle").button("option", "icons", {
				primary: "ui-icon-transferthick-e-w"
			});
		');
	}

	public function autocomplete()
	{
		$this->css->addFile(OKT_PUBLIC_URL.'/css/autocomplete/jquery.autocomplete.css');
		$this->js->addFile(OKT_PUBLIC_URL.'/js/autocomplete/jquery.autocomplete.min.js');
	}

	public function validateForm($user_options=array())
	{
		$options = array(
			'selector' => 'form',
			'lang' => $GLOBALS['okt']->user->language,
			'fields' => array()
		);

		if (!empty($user_options)) {
			$options = array_merge($options,$user_options);
		}

		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/validate/jquery.validate.min.js');
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/validate/additional-methods.min.js');
		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/validate/l10n/messages_'.$options['lang'].'.js');

		$this->getValidateJs($options['selector'],$options['fields']);
	}

	public function validate($form_id=null, $fields=array(), $lang=null)
	{
		if (empty($lang)) {
			$lang = $GLOBALS['okt']->user->language;
		}

		$this->js->addFile(OKT_PUBLIC_URL.'/js/jquery/validate/l10n/messages_'.$lang.'.js');

		$this->getValidateJs($form_id,$fields);
	}

	public function getValidateJs($form_id,$fields)
	{
		if (!empty($form_id) && !empty($fields))
		{
			$aRules = array();
			$aMessages = array();

			foreach ($fields as $field)
			{
				if (is_array($field['rules'])) {
					$aRules[] = $field['id'].': { '.implode(', ',$field['rules']).' }';
				}
				else {
					$aRules[] = $field['id'].': { '.$field['rules'].' }';
				}

				if (!empty($field['messages']))
				{
					if (is_array($field['messages'])) {
						$aMessages[] = $field['id'].': { '.implode(', ',$field['messages']).' }';
					}
					else {
						$aMessages[] = $field['id'].': { '.$field['messages'].' }';
					}
				}
			}

			$this->js->addReady('
				var validator = jQuery("#'.$form_id.'").validate({
					rules: {
						'.implode(",\n\t\t\t",$aRules).
					'
					},
					messages: {
						'.implode(",\n\t\t\t",$aMessages).
					'
					},
					invalidHandler: function(form, validator) {
						var errors = validator.numberOfInvalids();
						if (errors) {
							var invalidPanels = $(validator.invalidElements()).closest(".ui-tabs-panel", form);
							if (invalidPanels.size() > 0) {
								$.each($.unique(invalidPanels.get()), function(){
									$(this).siblings(".ui-tabs-nav")
										.find("a[href=\'#" + this.id + "\']")
										.parent()
										.addClass("ui-state-error")
										.show("pulsate",{times: 3});
								});
							}
						}
					}
				});
			');
		}
	}


	/* Gestion des RTE (Rich Text Editor) switchables
	----------------------------------------------------------*/

	/**
	 * Ajout d'un RTE
	 *
	 * @param string $id
	 * @param string $name
	 * @param callback $callback
	 * @return void
	 */
	public function addRte($id,$name,$callback)
	{
		if (is_callable($callback))
		{
			$this->rteList[$id] = array(
				'id' => $id,
				'name' => $name,
				'callback' => $callback
			);
		}
	}

	/**
	 * Application d'un RTE donné
	 *
	 * @param string $retId
	 * @param string $element
	 * @param array $options
	 * @return void
	 */
	public function applyRte($retId,$element=null,$options=array())
	{
		if (isset($this->rteList[$retId])) {
			call_user_func_array($this->rteList[$retId]['callback'],array($element,$options));
		}
	}

	/**
	 * Retourne la liste des RTE disponibles
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public function getRteList($flip=false)
	{
		$res = array();

		foreach ($this->rteList as $id=>$rte) {
			$res[$id] = $rte['name'];
		}

		if ($flip) {
			$res = array_flip($res);
			ksort($res);
		}

		return $res;
	}

	/**
	 * Indique si il y a des RTE disponibles
	 *
	 * @return boolean
	 */
	public function hasRte()
	{
		return !empty($this->rteList);
	}


	/* Gestion des LBL (LightBox Like) switchables
	----------------------------------------------------------*/

	/**
	 * Ajout d'une LBL
	 *
	 * @param string $id
	 * @param string $name
	 * @param callback $callback
	 * @param string $jsLoader
	 * @return void
	 */
	public function addLbl($id,$name,$callback,$jsLoader=null)
	{
		if (is_callable($callback))
		{
			$this->lblList[$id] = array(
				'id' => $id,
				'name' => $name,
				'callback' => $callback,
				'jsLoader' => $jsLoader
			);
		}
	}

	/**
	 * Application d'une LBL donnée
	 *
	 * @param string $lblId
	 * @param string $element
	 * @param string $conteneur
	 * @param array $options
	 * @return void
	 */
	public function applyLbl($lblId, $element='a.modal', $conteneur=".modal-box", $options=array())
	{
		if (isset($this->lblList[$lblId])) {
			call_user_func_array($this->lblList[$lblId]['callback'],array($element,$conteneur,$options));
		}
	}

	/**
	 * Retourne le javascript pour charger (ou recharger) la LBL
	 *
	 * @param string $lblId
	 * @return void
	 */
	public function getLblJsLoader($lblId)
	{
		if (isset($this->lblList[$lblId])) {
			return $this->lblList[$lblId]['jsLoader'];
		}
	}

	/**
	 * Retourne la liste des LBL disponibles
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public function getLblList($flip=false)
	{
		$res = array();

		foreach ($this->lblList as $id=>$lbl) {
			$res[$id] = $lbl['name'];
		}

		if ($flip) {
			$res = array_flip($res);
			ksort($res);
		}

		return $res;
	}

	/**
	 * Indique si il y a des LBL disponibles
	 *
	 * @return boolean
	 */
	public function hasLbl()
	{
		return !empty($this->lblList);
	}

	/**
	 * Indique si une LBL donnée existe
	 * @return boolean
	 */
	public function lblExists($lblId)
	{
		return isset($this->lblList[$lblId]);
	}


	/* Gestion des paiements CB switchables
	----------------------------------------------------------*/

	/**
	 * Ajout d'un paiement CB
	 *
	 * @param string $id
	 * @param string $name
	 * @param callback $callback
	 * @return void
	 */
	public function addCbp($id,$name,$callback)
	{
		if (is_callable($callback))
		{
			$this->cbpList[$id] = array(
				'id' => $id,
				'name' => $name,
				'callback' => $callback
			);
		}
	}

	/**
	 * Application d'un paiement CB donné
	 *
	 * @param string $cbpId
	 * @param string $element
	 * @param array $options
	 * @return void
	 */
	public function applyCbp($cbpId, $moduleId=null, $id_item=0, $numItem=null, $price=0, $customer_email=null)
	{
		if (isset($this->cbpList[$cbpId])) {
			call_user_func_array($this->cbpList[$cbpId]['callback'],array($moduleId,$id_item,$numItem,$price,$customer_email));
		}
	}

	/**
	 * Retourne la liste des paiements CB disponibles
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public function getCbpList($flip=false)
	{
		$res = array();

		foreach ($this->cbpList as $id=>$cbp){
			$res[$id] = $cbp['name'];
		}

		if ($flip) {
			$res = array_flip($res);
			ksort($res);
		}

		return $res;
	}

	/**
	 * Indique si il y a des paiements CB disponibles
	 *
	 * @return boolean
	 */
	public function hasCbp()
	{
		return !empty($this->cbpList);
	}


	/* Gestion des Captcha switchables
	----------------------------------------------------------*/

	/**
	 * Ajout d'un Captcha
	 *
	 * @param string $sId
	 * @param string $sName
	 * @param array $aBehaviors
	 * @return void
	 */
	public function addCaptcha($sId,$sName,$aBehaviors)
	{
		if (is_array($aBehaviors) && !empty($aBehaviors))
		{
			$this->captchaList[$sId] = array(
				'id' => $sId,
				'name' => $sName,
				'behaviors' => $aBehaviors
			);
		}
	}

	/**
	 * Retourne la liste des Captcha disponibles
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public function getCaptchaList($flip=false)
	{
		$res = array();

		foreach ($this->captchaList as $id=>$captcha) {
			$res[$id] = $captcha['name'];
		}

		if ($flip) {
			$res = array_flip($res);
			ksort($res);
		}

		return $res;
	}

	/**
	 * Chargement d'un captcha donné
	 *
	 * @param string $captchaId
	 * @return void
	 */
	public function loadCaptcha($captchaId)
	{
		static $loaded = null;

		if (is_null($loaded)) {
			$loaded = array();
		}

		if (isset($this->captchaList[$captchaId]) && !in_array($captchaId,$loaded))
		{
			global $okt;

			foreach ($this->captchaList[$captchaId]['behaviors'] as $behavior=>$callback) {
				$okt->triggers->registerTrigger($behavior,$callback);
			}

			$loaded[] = $captchaId;
		}
	}

	/**
	 * Indique si il y a des Captcha disponibles
	 *
	 * @return boolean
	 */
	public function hasCaptcha()
	{
		return !empty($this->captchaList);
	}


	/* Others things...
	----------------------------------------------------------*/

	/**
	 * Retourne la liste des thèmes UI présents.
	 *
	 * @param boolean $bForce 	Force le scan des fichiers/n'utilise pas le pseudo cache
	 * @return array
	 */
	public static function getUiThemes($bForce=false)
	{
		static $aThemes = null;

		if (!is_null($aThemes) && !$bForce) {
			return $aThemes;
		}

		$aThemes = array();
		foreach (new DirectoryIterator(OKT_PUBLIC_PATH.'/ui-themes') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || !$oFileInfo->isDir() ||
			!file_exists(OKT_PUBLIC_PATH.'/ui-themes/'.$oFileInfo->getFilename().'/jquery-ui.css')) {
				continue;
			}

			$aThemes[] = $oFileInfo->getFilename();
		}

		return $aThemes;
	}

	public static function formatCC($str,$condition='IE')
	{
		return '<!--[if '.$condition.']>'."\n".$str.'<![endif]-->'."\n";
	}

} # class htmlPage
