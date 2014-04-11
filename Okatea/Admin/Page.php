<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin;

use ArrayObject;
use Okatea\Admin\Messages\Errors;
use Okatea\Admin\Messages\Infos;
use Okatea\Admin\Messages\Success;
use Okatea\Admin\Messages\Warnings;
use Okatea\Tao\L10n\DateTime;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Html\Page as BasePage;

/**
 * Construction des pages d'administration.
 *
 */
class Page extends BasePage
{
	/**
	 * Le fil d'ariane
	 * @var object breadcrumb
	 */
	public $breadcrumb;

	/**
	 * Les messages flash.
	 * @var object
	 */
	public $flash;

	/**
	 * La pile de messages d'information.
	 * @var object
	 */
	public $infos;

	/**
	 * La pile de messages de confirmation.
	 * @var object
	 */
	public $success;

	/**
	 * La pile de messages d'avertissements
	 * @var object
	 */
	public $warnings;

	/**
	 * La pile de messages d'erreurs.
	 * @var object
	 */
	public $errors;

	/**
	 * La pile des jeux de boutons
	 * @var array
	 */
	public $buttonset = array();

	public $display_menu = true;

	/**
	 * Format du HTML du menu principal
	 * @var array
	 */
	public static $formatHtmlMainMenu = array();

	/**
	 * Format du HTML des sous-menu
	 * @var array
	 */
	public static $formatHtmlSubMenu = array();

	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($okt)
	{
		parent::__construct($okt, 'admin');

		$this->flash = $this->okt->session->getFlashBag();

		$this->infos = new Infos();
		$this->success = new Success();
		$this->warnings = new Warnings();
		$this->errors = new Errors();

		$this->getCommonReady();
	}

	/**
	 * Définit d'un coup le title tag et le fil d'ariane
	 *
	 * @param string $str
	 * @param string $url ('')
	 * @return void
	 */
	public function addGlobalTitle($str, $url='')
	{
		# title tag
		$this->addTitleTag($str);

		# fil d'ariane
		$this->addAriane($str, $url);
	}

	/**
	 * breadcrumb::add alias
	 *
	 * @see breadcrumb::add()
	 * @param string $label
	 * @param string $url ('')
	 * @return void
	 */
	public function addAriane($label, $url='')
	{
		$this->breadcrumb->add($label, $url);
	}

	public function getMainMenuHtml()
	{
		$mainMenuHtml = array('html' => null, 'active' => null);

		# construction du menu principal
		if ($this->display_menu)
		{
			$mainMenuHtml = $this->mainMenu->build();

			if ($this->okt->config->admin_menu_position != 'top')
			{
				$this->accordion(array(
					'heightStyle' => 'auto',
					'active' => ($mainMenuHtml['active'] === null ? 0 : $mainMenuHtml['active'])
				), '#mainMenu-'.$this->okt->config->admin_menu_position);
			}
			else
			{
				$this->js->addReady('
					$("#mainMenu-top").menu({
						position: { using: positionnerSousMenu }
					});

					function positionnerSousMenu(position, elements) {
						var options = {
							of: elements.target.element
						};

						if (elements.element.element.parent().parent().attr("id") === "mainMenu-top") {
							options.my = "left top";
							options.at = "left bottom";
						}
						else {
							options.my = "left top";
							options.at = "right top";
						}

						elements.element.element.position(options);
					}

					$("#mainMenu-top > li > a > span.ui-icon-carat-1-e").removeClass("ui-icon-carat-1-e").addClass("ui-icon-carat-1-s");

					$(window).scroll(function(e){
						var top = $(window).scrollTop();

						if (top > 180) {
							$("#mainMenu-top").addClass("fixed");
						}
						else {
							$("#mainMenu-top").removeClass("fixed");
						}
					});
				');
			}
		}

		return $mainMenuHtml['html'];
	}

	public function getUserBars()
	{
		$aUserBars = new ArrayObject(array(
			'first' => array(),
			'second' => array()
		));

		# logged in user
		if (!$this->okt->user->is_guest)
		{
			# profil link
			$aUserBars['first'][10] = sprintf(__('c_c_user_hello_%s'),
				'<a href="'.$this->okt->adminRouter->generate('User_profile').'">'.
				Escaper::html($this->okt->user->usedname).'</a>');

			# log off link
			$aUserBars['first'][90] = '<a href="'.$this->okt->adminRouter->generate('logout').'">'.__('c_c_user_log_off_action').'</a>';

			# last visit info
			$aUserBars['second'][10] = sprintf(__('c_c_user_last_visit_on_%s'), DateTime::full($this->okt->user->last_visit));
		}
		# guest user
		else {
			$aUserBars['first'][10] = __('c_c_user_hello_you_are_not_logged');
		}

		# languages switcher
		if ($this->okt->config->admin_lang_switcher && !$this->okt->languages->unique)
		{
			$sBaseUri = $this->okt->request->getUri();
			$sBaseUri .= strpos($sBaseUri,'?') ? '&' : '?';

			$iCount = 50;

			foreach ($this->okt->languages->list as $aLanguage)
			{
				if ($aLanguage['code'] === $this->okt->user->language) {
					continue;
				}

				$aUserBars['second'][$iCount++] = '<a href="'.$sBaseUri.'lang='.Escaper::html($aLanguage['code']).'" title="'.Escaper::html($aLanguage['title']).'">'.
						'<img src="'.$this->okt->options->public_url.'/img/flags/'.$aLanguage['img'].'" alt="'.Escaper::html($aLanguage['title']).'" /></a>';
			}

			unset($sBaseUri,$aLanguage);
		}

		$aUserBars['second'][100] = '<a href="'.$this->okt->config->app_path.'">'.__('c_c_go_to_website').'</a>';


		# -- CORE TRIGGER : adminHeaderUserBars
		$this->okt->triggers->callTrigger('adminHeaderUserBars', $aUserBars);


		# sort items of user bars by keys
		ksort($aUserBars['first']);
		ksort($aUserBars['second']);

		# remove empty values of user bars
		$aUserBars['first'] = array_filter($aUserBars['first']);
		$aUserBars['second'] = array_filter($aUserBars['second']);

		return $aUserBars;
	}

	/* Gestion des jeux de boutons
	 * @TODO mettre ça dans une classe indépendante uiButonSet
	 * @TODO à déplacer dans un dossier classes, rendre générique et surcharger
	----------------------------------------------------------*/

	/**
	 * Get current button set
	 *
	 * @return string
	 */
	public function getButtonSet($sButtonSetId)
	{
		return $this->buttonset($sButtonSetId, $this->buttonset[$sButtonSetId]);
	}

	/**
	 * Add button to current button set
	 *
	 * @param $aButton
	 * @param $direction
	 * @return void
	 */
	public function addButton($sButtonSetId, $aButton=array(), $direction='next')
	{
		if ($direction == 'next') {
			array_push($this->buttonset[$sButtonSetId]['buttons'],$aButton);
		}
		else {
			array_unshift($this->buttonset[$sButtonSetId]['buttons'],$aButton);
		}
	}

	/**
	 * Set current button set
	 *
	 * @param $aButtonsSet
	 * @return void
	 */
	public function setButtonset($sButtonSetId, $aButtonsSet=array())
	{
		$this->buttonset[$sButtonSetId] = $aButtonsSet;
	}

	/**
	 * Construit un jeu de bouton à partir d'un tableau associatif
	 *
	 * @param string $sButtonSetId
	 * @param array $aParams
	 * @return string
	 */
	public function buttonset($sButtonSetId, $aParams=array())
	{
		if (empty($aParams['buttons'])) {
			return null;
		}

		# check perms
		$aButons = array();
		foreach ($aParams['buttons'] as $aButon)
		{
			if (!empty($aButon) && isset($aButon['permission']) && $aButon['permission'] !== false) {
				$aButons[] = $aButon;
			}
		}

		if (empty($aButons)) {
			return null;
		}

		# construction
		$res = '<p class="buttonset';


		if (!empty($aParams['class']))
		{
			if (is_array($aParams['class'])) {
				$res .= ' '.implod(' ',$aParams['class']);
			}
			else {
				$res .= ' '.$aParams['class'];
			}
		}
		$res .= '"';

		if (empty($aParams['id'])) {
			$aParams['id'] = $sButtonSetId;
		}

		$res .= ' id="'.$aParams['id'].'"';

		$res .= '>';

		$iNumButons = count($aButons);

		$i = 0;


		$jsButtons = array();

		foreach ($aButons as $aButon)
		{
			$bIsFirst = (boolean)($i == 0);
			$bIsLast = (boolean)(($i+1) == $iNumButons);
			$bIsUnique = (boolean)($bIsFirst && $bIsLast);

			$res .= '<a href="'.$aButon['url'].'"';

			if (empty($aButon['id'])) {
				$aButon['id'] = $sButtonSetId.'-'.$i;
			}

			$res .= ' id="'.$aButon['id'].'"';

			$jsButtons[$i] = '$("#'.$aButon['id'].'").button({';

			if (!empty($aButon['onclick'])) {
				$res .= ' onclick="'.$aButon['onclick'].'"';
			}

			$res .= ' class="button';

			if (!empty($aButon['class']))
			{
				if (is_array($aButon['class'])) {
					$res .= ' '.implod(' ',$aButon['class']);
				}
				else {
					$res .= ' '.$aButon['class'];
				}
			}

			if (!empty($aButon['active'])) {
				$res .= ' ui-state-active';
			}

			$res .= '">';

			if (!empty($aButon['ui-icon'])) {
				$jsButtons[$i] .= 'icons: { primary: "ui-icon-'.$aButon['ui-icon'].'" }';
			}

			if (!empty($aButon['sprite-icon'])) {
				$res .= '<span class="icon '.$aButon['sprite-icon'].'"></span>';
			}

			if (!empty($aButon['title'])) {
				$res .= $aButon['title'];
			}

			$res .= '</a>';

			$jsButtons[$i] .= '})';
			++$i;
		}

		$res .= '</p>'.PHP_EOL.'<div class="clearer"></div>'.PHP_EOL;

		$this->js->addReady(implode(";\n",$jsButtons).'.parent().buttonset();');

		return $res;
	}

	public function getCommonReady()
	{
		$this->js->addReady(self::getCommonJs());
	}

	public static function getCommonJs()
	{
		return '
			var infos_box = jQuery("#messages div.infos_box");
			var success_box = jQuery("#messages div.success_box");
			var warnings_box = jQuery("#messages div.warnings_box");
			var errors_box = jQuery("#messages div.errors_box");

			infos_box.css("display","none");
			success_box.css("display","none");
			warnings_box.css("display","none");
			errors_box.css("display","none");

			if (infos_box.length)
			{
				jInfos(infos_box.html(),{
					autoHide: true,
					clickOverlay: true,
					MinWidth: 300,
					TimeShown: 3000,
					ShowTimeEffect: 200,
					HideTimeEffect: 200,
					LongTrip: 70,
					HorizontalPosition: "center",
					VerticalPosition: "top",
					ShowOverlay: true,
					ColorOverlay: "#000",
					OpacityOverlay: 0.3
				});
			}

			if (success_box.length)
			{
				jSuccess(success_box.html(),{
					autoHide: true,
					clickOverlay: true,
					MinWidth: 300,
					TimeShown: 3000,
					ShowTimeEffect: 200,
					HideTimeEffect: 200,
					LongTrip: 70,
					HorizontalPosition: "center",
					VerticalPosition: "top",
					ShowOverlay: true,
					ColorOverlay: "#000",
					OpacityOverlay: 0.3
				});
			}

			if (warnings_box.length)
			{
				jWarning(warnings_box.html(),{
					autoHide: true,
					clickOverlay: true,
					MinWidth: 300,
					TimeShown: 5000,
					ShowTimeEffect: 200,
					HideTimeEffect: 200,
					LongTrip: 70,
					HorizontalPosition: "center",
					VerticalPosition: "top",
					ShowOverlay: true,
					ColorOverlay: "#000",
					OpacityOverlay: 0.3
				});
			}

			if (errors_box.length)
			{
				jError(errors_box.html(),{
					autoHide: false,
					clickOverlay: true,
					MinWidth: 300,
					TimeShown: 5000,
					ShowTimeEffect: 200,
					HideTimeEffect: 200,
					LongTrip: 70,
					HorizontalPosition: "center",
					VerticalPosition: "top",
					ShowOverlay: true,
					ColorOverlay: "#000",
					OpacityOverlay: 0.3
				});
			}

			jQuery("input, select, textarea").oktClassFocus();

			jQuery("input:submit, .button").button();

			jQuery("table.common").styleTable();
		';
	}

	public function serve404()
	{
		$this->okt->request->attributes->set('controller', 'Okatea\Admin\Controller::serve404');
	}

	public function serve503()
	{
		$this->okt->request->attributes->set('controller', 'Okatea\Admin\Controller::serve503');
	}
}
