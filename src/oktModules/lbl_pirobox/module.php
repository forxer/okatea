<?php
/**
 * @ingroup module_lbl_pirobox
 * @brief La classe principale du module.
 *
 */

use Tao\Modules\Module;

class module_lbl_pirobox extends Module
{
	public $config = null;

	protected static $jsLoader = 'loadPiroBox();';

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('pirobox_config', __('m_lbl_pirobox_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_lbl_pirobox');

		$this->okt->page->addLbl('pirobox','piroBox',array('module_lbl_pirobox','pirobox'),self::$jsLoader);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('m_lbl_pirobox_menu_config'),
				'module.php?m=lbl_pirobox&amp;action=config',
				$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
				25,
				$this->okt->checkPerm('pirobox_config'),
				null
			);
		}
	}


	/**
	 * Met en place piroBox dans la page
	 *
	 * Voir http://pirobox.nyrodev.com/indexFr.php#settings
	 * pour la liste des options possibles
	 *
	 * @param string $element
	 * @param string $conteneur
	 * @param array $user_options
	 * @return void
	 */
	public static function pirobox($element='a.modal', $conteneur='.modal-box', $user_options=array())
	{
		global $okt;

		$config = $okt->lbl_pirobox->config;

		$okt->page->css->addFile(OKT_THEME.'/modules/lbl_pirobox/'.$config->theme.'/style.css');
		$okt->page->js->addFile(OKT_THEME.'/modules/lbl_pirobox/jquery.piroBox.js');

		$options = array(
	//		'selector' => "a[class^='".$classe."']",
			'selector' => $element,
			'my_speed' => $config->my_speed, //animation speed
			'close_speed' => $config->close_speed,
			'bg_alpha' => 0.5, //background opacity
			'slideShow' => $config->slideShow, // true == slideshow on, false == slideshow off
			'slideSpeed' => $config->slideSpeed, //slideshow duration in seconds
			'close_all' => '.piro_close,.piro_overlay',
			't_close' => __('c_c_action_close'),
			't_play_slideshow' => __('start slideshow'),
			't_stop_slideshow' => __('stop slideshow'),
			't_previous' => __('c_c_previous_f'),
			't_next' => __('c_c_next_f'),
			't_new_window' => __('Open image in a new window')
		);

		if (!empty($user_options)) {
			$options = array_merge($options,(array)$user_options);
		}

		$okt->page->js->addScript('
			function loadPiroBox() {
				jQuery().piroBox('.json_encode($options).');
			}
		');

		$okt->page->js->addReady(self::$jsLoader);
	}

}
