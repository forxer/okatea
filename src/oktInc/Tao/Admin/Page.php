<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin;

use Tao\Admin\Messages\Errors;
use Tao\Admin\Messages\Infos;
use Tao\Admin\Messages\Success;
use Tao\Admin\Messages\Warnings;
use Tao\Forms\Statics\FormElements as form;
use Tao\Html\Page as BasePage;
use Tao\Navigation\Breadcrumb;

/**
 * Construction des pages d'administration.
 *
 */
class Page extends BasePage
{
	/**
	 * _REQUEST['action']
	 * @var string
	 */
	public $action;

	/**
	 * _REQUEST['application']
	 * @var string
	 */
	public $application;

	/**
	 * _REQUEST['do']
	 * @var string
	 */
	public $do;

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

	/**
	 * Format du HTML du menu principal
	 * @var array
	 */
	public static $formatHtmlMainMenu = array(
		'block' => '<div%2$s>%1$s</div>',
		'item' => '<h2%3$s><a href="%2$s">%1$s</a></h2>%4$s',
		'active' => '<h2%3$s><a href="%2$s">%1$s</a></h2>%4$s',
		'separator' => '',
		'emptyBlock' => '<div%s>&nbsp;</div>'
	);

	/**
	 * Format du HTML des sous-menu
	 * @var array
	 */
	public static $formatHtmlSubMenu = array(
		'block' => '<div%2$s><ul class="sub-menu">%1$s</ul></div>',
		'item' => '<li%3$s class=""><span class="ui-icon ui-icon-arrow-1-e"></span><a href="%2$s">%1$s</a>%4$s</li>',
		'active' => '<li%3$s class=""><span class="ui-icon ui-icon-arrowthick-1-e"></span><a href="%2$s"><strong>%1$s</strong></a>%4$s</li>',
		'separator' => '',
		'emptyBlock' => '<div%s>&nbsp;</div>'
	);

	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($okt)
	{
		parent::__construct($okt, 'admin');

		$this->component = $okt->request->request->get('component', $okt->request->query->get('component'));
		$this->action = $okt->request->request->get('action', $okt->request->query->get('action'));
		$this->application = $okt->request->request->get('application', $okt->request->query->get('application'));
		$this->do = $okt->request->request->get('do', $okt->request->query->get('do'));

		$this->breadcrumb = new Breadcrumb();

		$this->flash = $okt->session->getFlashBag();

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
	public function addGlobalTitle($str,$url='')
	{
		# title tag
		$this->addTitleTag($str);

		# fil d'ariane
		$this->addAriane($str,$url);
	}

	/**
	 * breadcrumb::add alias
	 *
	 * @see breadcrumb::add()
	 * @param string $label
	 * @param string $url ('')
	 * @return void
	 */
	public function addAriane($label,$url='')
	{
		$this->breadcrumb->add($label,$url);
	}

	/**
	 * Retourne un champ de formulaire caché pour le CSRF token
	 */
	public static function formtoken()
	{
		return form::hidden(array('csrf_token'), $GLOBALS['okt']->user->csrf_token);
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

			if (errors_box.length)
			{
				jError(errors_box.html(),{
					autoHide: false,
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

			jQuery("input, select, textarea").oktClassFocus();

			jQuery("input:submit, .button").button();

			jQuery("table.common").styleTable();
		';
	}

	public function serve404()
	{
		$this->okt->request->attributes->set('_controller', 'Tao\Core\Controller::serve404');
	}

	public function serve503()
	{
		$this->okt->request->attributes->set('_controller', 'Tao\Core\Controller::serve503');
	}
}
