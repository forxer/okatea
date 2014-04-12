<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Html;

use DirectoryIterator;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Navigation\Breadcrumb;

/**
 * Permet de gérer quelques éléments courant à une page HTML
 *
 * L'élément title, le javascript, les CSS
 * et ce qu'on veut lui ajouter...
 *
 * Fournis également tout un ensemble de méthodes pour la mise en place de
 * widgets.
 *
 */
class Page
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Les CSS de la page
	 * @var object htmlCss
	 */
	public $css;

	/**
	 * Le fil d'ariane.
	 */
	public $breadcrumb;

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
	protected $sPageId;

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
	public $module;

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
	 * La pile des Captcha disponibles
	 * @var array
	 */
	protected $captchaList = array();

	/**
	 * La partie à afficher (traditionnellement 'admin' ou 'public')
	 * @var string
	 */
	public $sPart;


	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($okt, $sPart = null)
	{
		$this->okt = $okt;

		$this->css = new Css($sPart);
		$this->js = new Js($sPart);

		$this->breadcrumb = new Breadcrumb();

		$this->sPart = $sPart;
	}

	/**
	 * Retourne un champ de formulaire caché pour le CSRF token
	 */
	public function formtoken()
	{
		return $this->okt->session->getTokenInputField();
		return '<input type="hidden" name="csrf_token" id="csrf_token" value="'.$this->okt->session->get('csrf_token').'" />';
	}

	/* Gestion de l'élément title des pages
	----------------------------------------------------------*/

	/**
	 * Get title tag
	 *
	 * @param string $sTitleTagSep
	 * @return string
	 */
	public function titleTag($sTitleTagSep = ' - ')
	{
		return implode($sTitleTagSep, array_filter($this->aTitleTagStack));
	}

	/**
	 * Add title tag
	 *
	 * @param string $sTitle
	 * @return void
	 */
	public function addTitleTag($sTitle)
	{
		array_unshift($this->aTitleTagStack, $sTitle);
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
		return Escaper::html($this->sPageId);
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


	/* Infos globales du site
	----------------------------------------------------------*/

	/**
	 * Retourne le chemon de base des URL
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getBaseUrl($sLanguage = null)
	{
		$str = $this->okt->config->app_path;

		if (!$this->okt->languages->unique) {
			$str .= ($sLanguage !== null ? $sLanguage : $this->okt->user->language).'/';
		}

		return $str;
	}

	/**
	 * Retourne le titre internationnalisé du site.
	 *
	 * @return string
	 */
	public function getSiteTitle($sLanguage = null, $sDefault = null)
	{
		if ($sLanguage !== null && !empty($this->okt->config->title[$sLanguage])) {
			return $this->okt->config->title[$sLanguage];
		}
		elseif (!empty($this->okt->config->title[$this->okt->user->language])) {
			return $this->okt->config->title[$this->okt->user->language];
		}
		elseif (!empty($this->okt->config->title[$this->okt->config->language])) {
			return $this->okt->config->title[$this->okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne la description internationnalisée du site.
	 *
	 * @return string
	 */
	public function getSiteDescription($sLanguage = null, $sDefault = null)
	{
		if ($sLanguage !== null && !empty($this->okt->config->desc[$sLanguage])) {
			return $this->okt->config->desc[$sLanguage];
		}
		elseif (!empty($this->okt->config->desc[$this->okt->user->language])) {
			return $this->okt->config->desc[$this->okt->user->language];
		}
		elseif (!empty($this->okt->config->desc[$this->okt->config->language])) {
			return $this->okt->config->desc[$this->okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne le title tag internationnalisé du site.
	 *
	 * @return string
	 */
	public function getSiteTitleTag($sLanguage = null, $sDefault = null)
	{
		if ($sLanguage !== null && !empty($this->okt->config->title_tag[$sLanguage])) {
			return $this->okt->config->title_tag[$sLanguage];
		}
		elseif (!empty($this->okt->config->title_tag[$this->okt->user->language])) {
			return $this->okt->config->title_tag[$this->okt->user->language];
		}
		elseif (!empty($this->okt->config->title_tag[$this->okt->config->language])) {
			return $this->okt->config->title_tag[$this->okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne la meta description internationnalisée du site.
	 *
	 * @return string
	 */
	public function getSiteMetaDesc($sLanguage = null, $sDefault = null)
	{
		if ($sLanguage !== null && !empty($this->okt->config->meta_description[$sLanguage])) {
			return $this->okt->config->meta_description[$sLanguage];
		}
		elseif (!empty($this->okt->config->meta_description[$this->okt->user->language])) {
			return $this->okt->config->meta_description[$this->okt->user->language];
		}
		elseif (!empty($this->okt->config->meta_description[$this->okt->config->language])) {
			return $this->okt->config->meta_description[$this->okt->config->language];
		}
		else {
			return $sDefault;
		}
	}

	/**
	 * Retourne les meta keywords internationnalisés du site.
	 *
	 * @return string
	 */
	public function getSiteMetaKeywords($sLanguage = null, $sDefault = null)
	{
		if ($sLanguage !== null && !empty($this->okt->config->meta_keywords[$sLanguage])) {
			return $this->okt->config->meta_keywords[$sLanguage];
		}
		elseif (!empty($this->okt->config->meta_keywords[$this->okt->user->language])) {
			return $this->okt->config->meta_keywords[$this->okt->user->language];
		}
		elseif (!empty($this->okt->config->meta_keywords[$this->okt->config->language])) {
			return $this->okt->config->meta_keywords[$this->okt->config->language];
		}
		else {
			return $sDefault;
		}
	}


	/* UI widgets
	----------------------------------------------------------*/

	/**
	 * Met en place l'accordion dans la page (UI accordion)
	 *
	 * Voir http://jqueryui.com/demos/accordion/#options
	 * pour la liste des options possibles
	 *
	 * @param array $aCustomOptions
	 * @param string $sElement
	 * @return void
	 */
	public function accordion(array $aCustomOptions = array(), $sElement = '#accordion')
	{
		$aOptions = array();

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery(\''.$sElement.'\').accordion('.json_encode($aOptions).');
		');
	}

	/**
	 * Met en place le datepicker dans la page (UI datepicker)
	 *
	 * Voir http://jqueryui.com/demos/datepicker/#options
	 * pour la liste des options possibles
	 *
	 * @param array $aCustomOptions
	 * @param string $sElement
	 * @return void
	 */
	public function datePicker(array $aCustomOptions = array(), $sElement = '.datepicker')
	{
		$aOptions = array(
			'dateFormat' 	=> 'dd-mm-yy',
			'changeMonth' 	=> true,
			'changeYear' 	=> true
		);

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addFile($this->okt->options->public_url.'/plugins/jquery-ui/i18n/jquery-ui-i18n.min.js');

		$this->js->addReady('
			$.datepicker.setDefaults($.datepicker.regional[\''.$this->okt->user->language.'\']); '.
			'jQuery(\''.$sElement.'\').datepicker('.json_encode($aOptions).');
		');
	}

	/**
	 * Met en place une boite de dialogue dans la page (UI dialog)
	 *
	 * Voir http://jqueryui.com/demos/dialog/#options
	 * pour la liste des options possibles
	 *
	 * @param array $aCustomOptions
	 * @param string $sElement
	 * @return void
	 */
	public function dialog(array $aCustomOptions = array(), $sElement = '.dialog')
	{
		$aOptions = array();

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery(\''.$sElement.'\').dialog('.json_encode($aOptions).');
		');
	}

	/**
	 * Met en place les onglets dans la page (UI tabs)
	 *
	 * Voir http://jqueryui.com/demos/tabs/#options
	 * pour la liste des options possibles
	 *
	 * @param array $aCustomOptions
	 * @param string $sElement
	 * @return void
	 */
	public function tabs(array $aCustomOptions = array(), $sElement = '#tabered')
	{
		$aOptions = array(
			'show' 			=> true,
			'hide' 			=> true,
			'heightStyle' 	=> 'content'
		);

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addScript('
			var tabsOptions = '.json_encode($aOptions).';

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
			jQuery(\''.$sElement.'\').tabs(tabsOptions);
		');
	}


	/* Others widgets
	----------------------------------------------------------*/

	public function langSwitcher($target, $placeholder)
	{
		$this->js->addScript('
			var target = $("'.$target.'");
			var buttons = new Array;
		');

		$i = 0;
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->js->addScript('
				buttons['.$i++.'] = $(\'<a href="#" class="lang-switcher-button" data-lang-code="'.$aLanguage['code'].'">\'
				+ \'<img src="'.$this->okt->options->public_url.'/img/flags/'.Escaper::attribute($aLanguage['img']).'" '.
						'title="'.Escaper::attribute($aLanguage['title']).' ('.Escaper::attribute($aLanguage['code']).')" '.
						'alt="'.Escaper::attribute($aLanguage['title']).'" /></a>\')
				.click(function(e) {
					switch_language(\''.$aLanguage['code'].'\');
					e.preventDefault();
				});
			');
		}

		$this->js->addScript('
			// on ajoute ces boutons là où il faut
			buttons.reverse();
			$(buttons).each(function(){
				$("'.$placeholder.'").prepend(this);
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
			switch_language("'.$this->okt->user->language.'");
		');
	}

	/**
	 * Met en place un "roundabout"
	 *
	 * Voir http://fredhq.com/projects/roundabout/#options
	 * pour la liste des options possibles
	 *
	 * @param array $aCustomOptions
	 * @param string $sElement
	 * @return void
	 */
	public function roundabout(array $aCustomOptions = array(), $sElement = '.roundabout')
	{
		$this->js->addFile($this->okt->options->public_url.'/plugins/easing/jquery.easing.min.js');
		$this->js->addFile($this->okt->options->public_url.'/components/jquery-roundabout/jquery.roundabout.min.js');

		$aOptions = array();

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery(\''.$sElement.'\').roundabout('.json_encode($aOptions).');
		');
	}

	/**
	 * Met en place un "treeview"
	 *
	 * Voir http://docs.jquery.com/Plugins/Treeview/treeview#options
	 * pour la liste des options possibles
	 *
	 * @param array $aCustomOptions
	 * @param string $sElement
	 * @return void
	 */
	public function treeview(array $aCustomOptions = array(), $sElement = '.browser')
	{
		$this->css->addFile($this->okt->options->public_url.'/plugins/treeview/jquery.treeview.css');
		$this->js->addFile($this->okt->options->public_url.'/plugins/treeview/jquery.treeview.min.js');

		$aOptions = array();

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery(\''.$sElement.'\').treeview('.json_encode($aOptions).');
		');
	}

	public function updatePermissionsCheckboxes($prefix = 'p_perm_g_')
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

	public function loader($sElement)
	{
		$this->js->addFile($this->okt->options->public_url.'/plugins/blockUI/jquery.blockUI.min.js');

		$this->js->addReady('
			jQuery(\''.$sElement.'\').click(function() {
				$.blockUI({
					theme:    true,
					title:    "'.__('c_c_Please_wait').'",
					message:  "<p><img src=\"'.$this->okt->options->public_url.'/img/ajax-loader/big-circle-ball.gif\" alt=\"\" style=\"float: left; margin: 0 1em 1em 0\" /> '.__('c_c_Please_wait_txt').'</p>"
				});
			});
		');
	}

	public function cycle($sElement = '#diaporama', array $aCustomOptions = array())
	{
		$this->js->addFile($this->okt->options->public_url.'/plugins/cycle/jquery.cycle.min.js');

		$aOptions = array();

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery(\''.$sElement.'\').cycle('.json_encode($aOptions).');
		');
	}

	public function cycleLite($sElement = '#diaporama', array $aCustomOptions = array())
	{
		$this->js->addFile($this->okt->options->public_url.'/plugins/cycle/jquery.cycle.lite.min.js');

		$aOptions = array();

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery(\''.$sElement.'\').cycle('.json_encode($aOptions).');
		');
	}

	public function filterControl($showFilter = false)
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
	}

	public function strToSlug($command, $target, array $aCustomOptions = array())
	{
		$this->js->addFile($this->okt->options->public_url.'/components/jquery-stringtoslug/jquery.stringToSlug.min.js');

		$aOptions = array(
			'setEvents' 	=> 'keyup keydown blur',
			'getPut' 		=> $target,
			'space' 		=> '-'
		);

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery("'.$command.'").stringToSlug('.json_encode($aOptions).');
		');
	}

	public function toggleWithLegend($command, $target, array $aCustomOptions = array())
	{
		$this->js->addFile($this->okt->options->public_url.'/plugins/toggleWithLegend/jquery.toggleWithLegend.min.js');

		$aOptions = array(
			'img_on_src' 		=> $this->okt->options->public_url.'/img/ico/plus.png',
			'img_on_alt' 		=> Escaper::js(__('c_c_action_show')),
			'img_off_src' 		=> $this->okt->options->public_url.'/img/ico/minus.png',
			'img_off_alt' 		=> Escaper::js(__('c_c_action_hide')),
			'hide' 				=> true,
			'speed' 			=> 0,
			'legend_click' 		=> true,
			'fn' 				=> false, // A function called on first display,
			'cookie' 			=> false,
			'reverse_cookie' 	=> false // Reverse cookie behavior
		);

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addReady('
			jQuery("#'.$command.'").toggleWithLegend(jQuery("#'.$target.'"),'.json_encode($aOptions).');
		');
	}

	public function openLinkInDialog($sElement='#kink_id', array $aCustomOptions = array(), $htmlID = null)
	{
		static $loaded = null;

		$aOptions = array(
			'width' => 700,
			'height' => 500
		);

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
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
			openInDialog("'.$sElement.'", '.json_encode($aOptions).', "'.$htmlID.'");
		');
	}

	public function lockable($sElement = '.lockable', $note = '.lockable-note')
	{
		$this->js->addReady('
			jQuery(\''.$sElement.'\').each(function() {
				var current_lockable_div = this;
				jQuery(this).find(\''.$note.'\').hide();
				jQuery(this).find("input").each(function() {
					//this.disabled = true;
					jQuery(this)
						.prop("readonly", true)
						.addClass("disabled")
						.width((jQuery(this).width()-14) + "px");

					var imgE = document.createElement("img");
					imgE.src = "'.$this->okt->options->public_url.'/img/ico/lock.png";
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

	public function checkboxHelper($sFormId, $helper_id)
	{
		$this->js->addFile($this->okt->options->public_url.'/plugins/checkboxes/jquery.checkboxes.min.js');

		$this->js->addReady('
			$(\'<a href="#" id="'.$helper_id.'-button-select-all">'.__('c_c_select_all').'</a>\')
			.click(function(event) {
				$("#'.$sFormId.'").checkCheckboxes();
				event.preventDefault();
				$(this).blur();
			}).appendTo("#'.$helper_id.'");

			$(\'<a href="#" id="'.$helper_id.'-button-select-none">'.__('c_c_select_none').'</a>\')
			.click(function(event) {
				$("#'.$sFormId.'").unCheckCheckboxes();
				event.preventDefault();
				$(this).blur();
			}).appendTo("#'.$helper_id.'");

			$(\'<a href="#" id="'.$helper_id.'-button-select-toggle">'.__('c_c_toggle_select').'</a>\')
			.click(function(event) {
				$("#'.$sFormId.'").toggleCheckboxes();
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
		$this->css->addFile($this->okt->options->public_url.'/css/autocomplete/jquery.autocomplete.css');
		$this->js->addFile($this->okt->options->public_url.'/js/autocomplete/jquery.autocomplete.min.js');
	}

	public function validateForm(array $aCustomOptions = array())
	{
		$aOptions = array(
			'selector' => 'form',
			'lang' => $this->okt->user->language,
			'fields' => array()
		);

		if (!empty($aCustomOptions)) {
			$aOptions = array_merge($aOptions, $aCustomOptions);
		}

		$this->js->addFile($this->okt->options->get('public_url').'/components/jquery-validation/dist/jquery.validate.min.js');
		$this->js->addFile($this->okt->options->get('public_url').'/components/jquery-validation/dist/additional-methods.min.js');

		if (file_exists($this->okt->options->get('public_dir').'/components/jquery-validation/src/localization/messages_'.$aOptions['lang'].'.js')) {
			$this->js->addFile($this->okt->options->get('public_url').'/components/jquery-validation/src/localization/messages_'.$aOptions['lang'].'.js');
		}

		$this->getValidateJs($aOptions['selector'], $aOptions['fields']);
	}

	public function validate($sFormId = null, array $aFields = array(), $sLanguage = null)
	{
		if (null === $sLanguage) {
			$sLanguage = $this->okt->user->language;
		}

		$this->js->addFile($this->okt->options->get('public_url').'/components/jquery-validation/dist/jquery.validate.min.js');
		$this->js->addFile($this->okt->options->get('public_url').'/components/jquery-validation/dist/additional-methods.min.js');

		if (file_exists($this->okt->options->get('public_dir').'/components/jquery-validation/src/localization/messages_'.$sLanguage.'.js')) {
			$this->js->addFile($this->okt->options->get('public_url').'/components/jquery-validation/src/localization/messages_'.$sLanguage.'.js');
		}

		$this->getValidateJs($sFormId, $aFields);
	}

	public function getValidateJs($sFormId, $aFields)
	{
		if (!empty($sFormId) && !empty($aFields))
		{
			$aRules = array();
			$aMessages = array();

			foreach ($aFields as $aField)
			{
				if (is_array($aField['rules'])) {
					$aRules[] = $aField['id'].': { '.implode(', ',$aField['rules']).' }';
				}
				else {
					$aRules[] = $aField['id'].': { '.$aField['rules'].' }';
				}

				if (!empty($aField['messages']))
				{
					if (is_array($aField['messages'])) {
						$aMessages[] = $aField['id'].': { '.implode(', ',$aField['messages']).' }';
					}
					else {
						$aMessages[] = $aField['id'].': { '.$aField['messages'].' }';
					}
				}
			}

			$this->js->addReady('
				var validator = jQuery("#'.$sFormId.'").validate({
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
	public function addRte($id, $name, $callback)
	{
		if (is_callable($callback))
		{
			$this->rteList[$id] = array(
				'id' 		=> $id,
				'name' 		=> $name,
				'callback' 	=> $callback
			);
		}
	}

	/**
	 * Application d'un RTE donné
	 *
	 * @param string $retId
	 * @param string $sElement
	 * @param array $aOptions
	 * @return void
	 */
	public function applyRte($retId, $sElement = null, array $aOptions = array())
	{
		if (isset($this->rteList[$retId])) {
			call_user_func_array($this->rteList[$retId]['callback'], array($sElement, $aOptions));
		}
	}

	/**
	 * Retourne la liste des RTE disponibles
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public function getRteList($bFlip = false)
	{
		$res = array();

		foreach ($this->rteList as $id => $rte) {
			$res[$id] = $rte['name'];
		}

		if ($bFlip) {
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
	public function addLbl($id, $name, $callback, $jsLoader = null)
	{
		if (is_callable($callback))
		{
			$this->lblList[$id] = array(
				'id' 		=> $id,
				'name' 		=> $name,
				'callback' 	=> $callback,
				'jsLoader' 	=> $jsLoader
			);
		}
	}

	/**
	 * Application d'une LBL donnée
	 *
	 * @param string $lblId
	 * @param string $sElement
	 * @param string $conteneur
	 * @param array $aOptions
	 * @return void
	 */
	public function applyLbl($lblId, $sElement = 'a.modal', $conteneur = ".modal-box", array $aOptions = array())
	{
		if (isset($this->lblList[$lblId])) {
			call_user_func_array($this->lblList[$lblId]['callback'], array($sElement, $conteneur, $aOptions));
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
	 * @param boolean $bFlip
	 * @return array
	 */
	public function getLblList($bFlip = false)
	{
		$res = array();

		foreach ($this->lblList as $id => $lbl) {
			$res[$id] = $lbl['name'];
		}

		if ($bFlip) {
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
	public function addCaptcha($sId, $sName, $aBehaviors)
	{
		if (is_array($aBehaviors) && !empty($aBehaviors))
		{
			$this->captchaList[$sId] = array(
				'id' 			=> $sId,
				'name' 			=> $sName,
				'behaviors' 	=> $aBehaviors
			);
		}
	}

	/**
	 * Retourne la liste des Captcha disponibles
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public function getCaptchaList($bFlip = false)
	{
		$res = array();

		foreach ($this->captchaList as $id => $captcha) {
			$res[$id] = $captcha['name'];
		}

		if ($bFlip) {
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

		if (isset($this->captchaList[$captchaId]) && !in_array($captchaId, $loaded))
		{
			foreach ($this->captchaList[$captchaId]['behaviors'] as $behavior=>$callback) {
				$this->okt->triggers->registerTrigger($behavior, $callback);
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
	public static function getUiThemes($bForce = false)
	{
		global $okt;

		static $aThemes = null;

		if (!is_null($aThemes) && !$bForce) {
			return $aThemes;
		}

		$aThemes = array();
		foreach (new DirectoryIterator($okt->options->public_dir.'/plugins/jquery-ui/themes') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || !$oFileInfo->isDir() ||
			!file_exists($okt->options->public_dir.'/plugins/jquery-ui/themes/'.$oFileInfo->getFilename().'/jquery-ui.css')) {
				continue;
			}

			$aThemes[] = $oFileInfo->getFilename();
		}

		return $aThemes;
	}

	public static function formatCC($str, $condition = 'IE')
	{
		return '<!--[if '.$condition.']>'."\n".$str.'<![endif]-->'."\n";
	}
}
