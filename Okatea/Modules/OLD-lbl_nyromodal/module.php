<?php
/**
 * @ingroup okt_module_lbl_nyromodal
 * @brief La classe principale du module.
 *
 */
use Okatea\Tao\Modules\Module;

class module_lbl_nyromodal extends Module
{

	public $config = null;

	protected static $jsLoader = 'loadNyromodal();';

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('nyromodal_config', __('m_lbl_nyromodal_perm_config'), 'configuration');
		
		# configuration
		$this->config = $this->okt->newConfig('conf_lbl_nyromodal');
		
		$this->okt->page->addLbl('nyromodal', 'nyroModal', array(
			'module_lbl_nyromodal',
			'nyromodal'
		), self::$jsLoader);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(__('m_lbl_nyromodal_menu_config'), 'module.php?m=lbl_nyromodal&amp;action=config', $this->bCurrentlyInUse && ($this->okt->page->action === 'config'), 25, $this->okt->checkPerm('nyromodal_config'), null);
		}
	}

	/**
	 * Met en place nyroModal dans la page
	 *
	 * Voir http://nyromodal.nyrodev.com/indexFr.php#settings
	 * pour la liste des options possibles
	 *
	 * @param string $element        	
	 * @param string $conteneur        	
	 * @param array $user_options        	
	 * @return void
	 */
	public static function nyromodal($element = 'a.modal', $conteneur = '.modal-box', $user_options = array())
	{
		global $okt;
		
		$okt->page->css->addFile($okt->theme->url . '/modules/lbl_nyromodal/nyroModal.css');
		$okt->page->js->addFile($okt->theme->url . '/modules/lbl_nyromodal/jquery.nyroModal.min.js');
		
		$config = $okt->lbl_nyromodal->config;
		
		$options = array(
			# indicates if the modal should resize when the window is resized
			'windowResize' => true,
			
			# Background color
			'bgColor' => '#' . $config->bgColor,
			
			# default Width If null, will be calculate automatically
			'width' => null,
			
			# default Height If null, will be calculate automatically
			'height' => null,
			
			# Minimum width
			'minWidth' => 400,
			
			# Minimum height
			'minHeight' => 300,
			
			# Indicate if the content is resizable. Will be set to false for swf
			'resizable' => true,
			
			# Indicate if the content is auto sizable. If not, the min size will be used
			'autoSizable' => true,
			
			# Use .nyroModalPrev and .nyroModalNext to set the navigation link
			'galleryLinks' => '<a href=\"#\" class=\"nyroModalPrev\">' . __('c_c_previous_f') . '</a><a href=\"#\" class=\"nyroModalNext\">' . __('c_c_next_f') . '</a>',
			
			# Indicate if the gallery should loop
			'galleryLoop' => false,
			
			# Adding automaticly as the first child of #nyroModalWrapper
			'closeButton' => '<a href=\"#\" class=\"nyroModalClose\" id=\"closeBut\" title=\"' . __('c_c_action_close') . '\">' . __('c_c_action_Close') . '</a>'
		)
		;
		
		if (! empty($user_options))
		{
			$options = array_merge($options, (array) $user_options);
		}
		
		$okt->page->js->addScript('
			function loadNyromodal() {
				jQuery("' . $element . '").nyroModal(' . json_encode($options) . ');
			}
		');
		
		$okt->page->js->addReady(self::$jsLoader);
	}
}
