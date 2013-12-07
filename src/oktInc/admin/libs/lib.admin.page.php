<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Html\Page;
use Tao\Navigation\Breadcrumb;

/**
 * Construction des pages d'administration.
 *
 * @addtogroup Okatea
 *
 */

class adminPage extends Page
{
	/**
	 * $_REQUEST['action']
	 * @var string
	 */
	public $action;

	/**
	 * $_REQUEST['application']
	 * @var string
	 */
	public $application;

	/**
	 * $_REQUEST['do']
	 * @var string
	 */
	public $do;

	/**
	 * Le fil d'ariane
	 * @var object breadcrumb
	 */
	public $breadcrumb;

	/**
	 * La pile de messages
	 * @var object
	 */
	public $messages;

	/**
	 * La pile d'avertissements
	 * @var object
	 */
	public $warnings;

	/**
	 * La pile d'erreurs
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

		$this->action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : null;
		$this->application = !empty($_REQUEST['application']) ? $_REQUEST['application'] : null;
		$this->do = !empty($_REQUEST['do']) ? $_REQUEST['do'] : null;

		$this->breadcrumb = new Breadcrumb();

		$this->flashMessages = new oktFlashMessages();

		$this->messages = new adminMessagesSuccess();
		$this->warnings = new adminMessagesWarnings();
		$this->errors = new adminMessagesErrors();

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
			var msg_box = jQuery("#messages div.msg_box");
			var wrn_box = jQuery("#messages div.wrn_box");
			var error_box = jQuery("#messages div.error_box");

			msg_box.css("display","none");
			wrn_box.css("display","none");
			error_box.css("display","none");

			if (msg_box.length)
			{
				jSuccess(msg_box.html(),{
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

			if (wrn_box.length)
			{
				jNotify(wrn_box.html(),{
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

			if (error_box.length)
			{
				jError(error_box.html(),{
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

} # class
