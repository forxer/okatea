<?php
/**
 * @ingroup okt_module_lbl_nyromodal_2
 * @brief La classe principale du module.
 *
 */
use Okatea\Tao\Modules\Module;

class module_lbl_nyromodal_2 extends Module
{

	public $config = null;

	protected static $jsLoader = 'loadNyromodal2();';

	protected function prepend()
	{
		# permissions
		$this->okt['permissions']->addPerm('nyromodal_2_config', __('m_lbl_nyromodal_2_perm_config'), 'configuration');
		
		# configuration
		$this->config = $this->okt->newConfig('conf_lbl_nyromodal_2');
		
		$this->okt->page->addLbl('nyromodal2', 'nyroModal2', array(
			'module_lbl_nyromodal_2',
			'nyromodal2'
		), self::$jsLoader);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(__('m_lbl_nyromodal_2_menu_config'), 'module.php?m=lbl_nyromodal_2&amp;action=config', $this->bCurrentlyInUse && ($this->okt->page->action === 'config'), 25, $this->okt['visitor']->checkPerm('nyromodal_2_config'), null);
		}
	}

	/**
	 * Met en place nyroModal 2 dans la page
	 *
	 * Voir http://nyromodal.nyrodev.com/indexFr.php#settings
	 * pour la liste des options possibles
	 *
	 * @param string $element        	
	 * @param string $conteneur        	
	 * @param array $user_options        	
	 * @return void
	 */
	public static function nyromodal2($element = 'a.modal', $conteneur = '.modal-box', $user_options = [])
	{
		global $okt;
		
		$okt->page->css->addFile($okt->theme->url . '/modules/lbl_nyromodal_2/styles/nyroModal.css');
		$okt->page->js->addFile($okt->theme->url . '/modules/lbl_nyromodal_2/js/jquery.nyroModal.min.js');
		$okt->page->js->addCCFile($okt->theme->url . '/modules/lbl_nyromodal_2/js/jquery.nyroModal-ie6.min.js', 'IE 6');
		
		$config = $okt->lbl_nyromodal_2->config;
		
		$options = array(
			# Indicates if it's a modal window or not
			'modal' => $config->modal,
			
			# Indicates if the modal should close on Escape key
			'closeOnEscape' => $config->closeOnEscape,
			
			# Indicates if a click on the background should close the modal
			'closeOnClick' => $config->closeOnClick,
			
			# Indicates if the closeButonn should be added
			'showCloseButton' => $config->showCloseButton,
			
			# Close button HTML
			'closeButton' => '<a href="#" class="nyroModalClose nyroModalCloseButton nmReposition" title="' . __('c_c_action_close') . '">' . __('c_c_action_Close') . '</a>',
			
			# Indicates if the gallery should loop
			'galleryLoop' => $config->galleryLoop,
			
			# Indicates if the gallery counts should be shown
			'galleryCounts' => $config->galleryCounts,
			
			# Error message
			'errorMsg' => __('An error occured')
		);
		
		if (!empty($user_options))
		{
			$options = array_merge($options, (array) $user_options);
		}
		
		$okt->page->js->addScript('
			function loadNyromodal2() {
				jQuery("' . $element . '").nyroModal(' . json_encode($options) . ');
			}
		');
		
		$okt->page->js->addReady(self::$jsLoader);
	}
}
