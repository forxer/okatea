<?php
/**
 * @ingroup okt_module_lbl_colorbox
 * @brief La classe principale du module.
 *
 */
use Okatea\Tao\Modules\Module;

class module_lbl_colorbox extends Module
{

	public $config = null;

	protected static $jsLoader = 'loadColorbox();';

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('colorbox_config', __('m_lbl_colorbox_perm_config'), 'configuration');
		
		# configuration
		$this->config = $this->okt->newConfig('conf_lbl_colorbox');
		
		$this->okt->page->addLbl('colorbox', 'ColorBox', array(
			'module_lbl_colorbox',
			'colorbox'
		), self::$jsLoader);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(__('m_lbl_colorbox_menu_config'), 'module.php?m=lbl_colorbox&amp;action=config', $this->bCurrentlyInUse && ($this->okt->page->action === 'config'), 25, $this->okt->checkPerm('colorbox_config'), null);
		}
	}

	public static function addFiles()
	{
		global $okt;
		
		$okt->page->css->addFile($okt->theme->url . '/modules/lbl_colorbox/' . $okt->lbl_colorbox->config->theme . '/colorbox.css');
		$okt->page->js->addFile($okt->theme->url . '/modules/lbl_colorbox/jquery.colorbox-min.js');
		
		if (file_exists($okt->theme->path . '/modules/lbl_colorbox/i18n/jquery.colorbox-' . $okt->user->language . '.js'))
		{
			$okt->page->js->addFile($okt->theme->url . '/modules/lbl_colorbox/i18n/jquery.colorbox-' . $okt->user->language . '.js');
		}
	}

	/**
	 * Met en place colorbox dans la page.
	 *
	 * Voir http://www.jacklmoore.com/colorbox/
	 * pour la liste des options possibles.
	 *
	 * @param string $sElement        	
	 * @param string $sConteneur        	
	 * @param array $aUserOptions        	
	 * @return void
	 */
	public static function colorbox($sElement = 'a.modal', $sConteneur = '.modal-box', array $aUserOptions = array())
	{
		global $okt;
		
		self::addFiles();
		
		$okt->page->js->addScript('
			function loadColorbox() {
				jQuery("' . $sElement . '").colorbox(' . json_encode(self::getOptions($aUserOptions)) . ');
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
	public static function getOptions($aUserOptions = array())
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
			
			'scalePhotos' => true,
			'maxWidth' => '80%',
			'maxHeight' => '80%',
			
			'scrolling' => true
		);
		
		if (! empty($aUserOptions))
		{
			$aOptions = array_merge($aOptions, $aUserOptions);
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
