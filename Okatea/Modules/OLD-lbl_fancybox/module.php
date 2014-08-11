<?php
/**
 * @ingroup okt_module_lbl_fancybox
 * @brief La classe principale du module.
 *
 */
use Okatea\Tao\Modules\Module;

class module_lbl_fancybox extends Module
{

	public $config = null;

	public static $jsLoader = 'loadFancybox();';

	protected function prepend()
	{
		# permissions
		$this->okt['permissions']->addPerm('fancybox_config', __('m_lbl_fancybox_perm_config'), 'configuration');
		
		# configuration
		$this->config = $this->okt->newConfig('conf_lbl_fancybox');
		
		$this->okt->page->addLbl('fancybox', 'Fancybox', array(
			'module_lbl_fancybox',
			'fancybox'
		), self::$jsLoader);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(__('m_lbl_fancybox_menu_config'), 'module.php?m=lbl_fancybox&amp;action=config', $this->bCurrentlyInUse && ($this->okt->page->action === 'config'), 25, $this->okt['visitor']->checkPerm('fancybox_config'), null);
		}
	}

	/**
	 * Met en place fancybox dans la page
	 *
	 * Voir http://fancybox.net/howto
	 * pour la liste des options possibles
	 *
	 * @param string $element        	
	 * @param string $conteneur        	
	 * @param array $aUserOptions        	
	 * @return void
	 */
	public static function getScripts()
	{
		global $okt;
		
		$okt->page->css->addFile($okt->theme->url . '/modules/lbl_fancybox/jquery.fancybox.css');
		$okt->page->js->addFile($okt->theme->url . '/modules/lbl_fancybox/jquery.fancybox.min.js');
		$okt->page->js->addFile($this->okt['public_url'] . '/components/jquery-mousewheel/jquery.mousewheel.js');
		
		$okt->page->css->addCss("
		/* IE6 */

		.fancybox-ie6 #fancybox-close { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_close.png', sizingMethod='scale'); }

		.fancybox-ie6 #fancybox-left-ico { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_nav_left.png', sizingMethod='scale'); }
		.fancybox-ie6 #fancybox-right-ico { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_nav_right.png', sizingMethod='scale'); }

		.fancybox-ie6 #fancybox-title-over { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_title_over.png', sizingMethod='scale'); zoom: 1; }
		.fancybox-ie6 #fancybox-title-float-left { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_title_left.png', sizingMethod='scale'); }
		.fancybox-ie6 #fancybox-title-float-main { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_title_main.png', sizingMethod='scale'); }
		.fancybox-ie6 #fancybox-title-float-right { background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_title_right.png', sizingMethod='scale'); }

		.fancybox-ie6 #fancybox-bg-w, .fancybox-ie6 #fancybox-bg-e, .fancybox-ie6 #fancybox-left, .fancybox-ie6 #fancybox-right, #fancybox-hide-sel-frame {
			height: expression(this.parentNode.clientHeight + \"px\");
		}

		#fancybox-loading.fancybox-ie6 {
			position: absolute; margin-top: 0;
			top: expression( (-20 + (document.documentElement.clientHeight ? document.documentElement.clientHeight/2 : document.body.clientHeight/2 ) + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop )) + 'px');
		}

		#fancybox-loading.fancybox-ie6 div	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_loading.png', sizingMethod='scale'); }

		/* IE6, IE7, IE8 */

		.fancybox-ie .fancybox-bg { background: transparent !important; }

		.fancybox-ie #fancybox-bg-n { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_n.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-ne { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_ne.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-e { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_e.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-se { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_se.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-s { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_s.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-sw { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_sw.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-w { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_w.png', sizingMethod='scale'); }
		.fancybox-ie #fancybox-bg-nw { filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $this->okt['public_url'] . "/modules/lbl_fancybox/fancy_shadow_nw.png', sizingMethod='scale'); }
		");
	}

	/**
	 * Met en place fancybox dans la page
	 *
	 * Voir http://fancybox.net/howto
	 * pour la liste des options possibles
	 *
	 * @param string $element        	
	 * @param string $conteneur        	
	 * @param array $aUserOptions        	
	 * @return void
	 */
	public static function fancybox($element = 'a.modal', $conteneur = '.modal-box', $aUserOptions = [])
	{
		global $okt;
		
		self::getScripts();
		
		$okt->page->js->addScript('
			function loadFancybox() {
				if (jQuery("' . $element . '").length) {
					jQuery("' . $element . '").fancybox(' . json_encode(self::getOptions($aUserOptions)) . ');
				}
			}
		');
		
		$okt->page->js->addReady(self::getJsLoader());
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

	/**
	 * Retourne sous forme de tableau les options de la FancyBox.
	 *
	 * @param array $aUserOptions        	
	 * @return array
	 */
	public static function getOptions($aUserOptions = [])
	{
		global $okt;
		
		$oConfig = $okt->lbl_fancybox->config;
		
		$aOptions = array(
			'padding' => 10,
			'margin' => 40,
			
			'autoScale' => true,
			'autoDimensions' => true,
			
			'modal' => false,
			
			'hideOnOverlayClick' => $oConfig->hideOnOverlayClick,
			'hideOnContentClick' => $oConfig->hideOnContentClick,
			
			'overlayShow' => $oConfig->overlayShow,
			'overlayOpacity' => $oConfig->overlayOpacity,
			'overlayColor' => '#' . $oConfig->overlayColor,
			
			'titleShow' => $oConfig->titleShow,
			'titlePosition' => $oConfig->titlePosition,
			
			'opacity' => true,
			
			'cyclic' => $oConfig->cyclic,
			
			'transitionIn' => $oConfig->transitionIn,
			'speedIn' => $oConfig->speedIn,
			
			'transitionOut' => $oConfig->transitionOut,
			'speedOut' => $oConfig->speedOut,
			
			'easingIn' => 'swing',
			'easingOut' => 'swing'
		);
		
		if (!empty($aUserOptions))
		{
			$aOptions = array_merge($aOptions, (array) $aUserOptions);
		}
		
		return $aOptions;
	}
}
