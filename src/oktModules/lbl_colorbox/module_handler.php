<?php
/**
 * @ingroup okt_module_lbl_colorbox
 * @brief La classe principale du module.
 *
 */

use Tao\Modules\Module;

class module_lbl_colorbox extends Module
{
	public $config = null;

	protected static $jsLoader = 'loadColorbox();';

	protected function prepend()
	{
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# permissions
		$this->okt->addPerm('colorbox_config', __('m_lbl_colorbox_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_lbl_colorbox');

		$this->okt->page->addLbl('colorbox','ColorBox',array('module_lbl_colorbox','colorbox'),self::$jsLoader);
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('m_lbl_colorbox_menu_config'),
				'module.php?m=lbl_colorbox&amp;action=config',
				ON_LBL_COLORBOX_MODULE && ($this->okt->page->action === 'config'),
				25,
				$this->okt->checkPerm('colorbox_config'),
				null
			);
		}
	}

	public static function getScripts()
	{
		global $okt;

		$okt->page->css->addFile(OKT_THEME.'/modules/lbl_colorbox/'.$okt->lbl_colorbox->config->theme.'/colorbox.css');
		$okt->page->js->addFile(OKT_THEME.'/modules/lbl_colorbox/jquery.colorbox-min.js');
	}

	/**
	 * Met en place colorbox dans la page
	 *
	 * Voir http://colorpowered.com/colorbox/
	 * pour la liste des options possibles
	 *
	 * @param string $element
	 * @param string $conteneur
	 * @param array $aUserOptions
	 * @return void
	 */
	public static function colorbox($element='a.modal', $conteneur='.modal-box', $aUserOptions=array())
	{
		global $okt;

		self::getScripts();

		$okt->page->js->addScript('
			function loadColorbox() {
				jQuery("'.$element.'").colorbox('.json_encode(self::getOptions($aUserOptions)).');
			}
		');

		$okt->page->js->addReady(self::getJsLoader());
	}

	/**
	 * Retourne sous forme de tableau les options de la ColorBox.
	 *
	 * @param array $aUserOptions
	 * @return array
	 */
	public static function getOptions($aUserOptions=array())
	{
		global $okt;

		$oConfig = $okt->lbl_colorbox->config;

		$aOptions = array(
			'transition' => $oConfig->transition,
			'speed' => $oConfig->speed,
			'loop' => $oConfig->loop,

			'slideshow' => $oConfig->slideshow,
			'slideshowSpeed' => $oConfig->slideshowspeed,
			'slideshowAuto' => $oConfig->slideshowauto,

			'maxWidth' => '75%',
			'maxHeight' => '75%',

			'close' => __('c_c_action_close'),
			'slideshowStart' => __('start slideshow'),
			'slideshowStop' => __('stop slideshow'),
			'current' => __('{current} of {total}'),
			'previous' => __('c_c_previous_f'),
			'next' => __('c_c_next_f'),
			'close' => __('c_c_action_close')
		);

		if (!empty($aUserOptions)) {
			$aOptions = array_merge($aOptions,(array)$aUserOptions);
		}

		return $aOptions;
	}

	/**
	 * Retourne la chaine de caractère du javascript pour charger la FancyBox.
	 *
	 * @return string
	 */
	public static function getJsLoader()
	{
		return self::$jsLoader;
	}

}
