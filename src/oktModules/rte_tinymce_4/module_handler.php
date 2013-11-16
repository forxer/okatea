<?php
/**
 * @ingroup okt_module_rte_tinyMCE_4
 * @brief La classe principale du module.
 *
 */

class module_rte_tinymce_4 extends oktModule
{
	public $config = null;

	protected function prepend()
	{
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# permissions
		$this->okt->addPerm('rte_tinymce_4_config', __('m_rte_tinymce_4_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_rte_tinymce_4');
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		$this->okt->page->addRte('tinymce_4_normal','tinyMCE 4 normal',array('module_rte_tinymce_4','tinyMCEnormal'));

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('TinyMCE 4'),
				'module.php?m=rte_tinymce_4&amp;action=config',
				ON_RTE_TINYMCE_4_MODULE && ($this->okt->page->action === 'config'),
				40,
				$this->okt->checkPerm('rte_tinymce_4_config'),
				null
			);
		}
	}

	public static function tinyMCEnormal($element='textarea',$user_options=array())
	{
		global $okt;

		$options = array(
			'plugins' => array(
				'advlist autolink lists link image charmap print preview anchor',
				'searchreplace visualblocks code fullscreen',
				'insertdatetime media table contextmenu paste'
			),
			'toolbar' => 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
		);

//		if ($okt->modules->moduleExists('media_manager')) {
//			self::addMediaManager($options);
//		}

		self::addCommon($element,$user_options,$options);
	}

	protected static function addCommon($element, array $user_options=array(), array $options=array())
	{
		global $okt;

		$common_options = array(
			'script_url' => OKT_MODULES_URL.'/rte_tinymce_4/tinyMCE_jquery/tinymce.min.js'
		);

		# language
		$sLanguageCode = strtolower($okt->user->language);
		$sSpecificLanguageCode = strtolower($okt->user->language).'_'.strtoupper($okt->user->language);

		if (file_exists(OKT_MODULES_PATH.'/rte_tinymce_4/tinyMCE_jquery/langs/'.$sLanguageCode.'.js')) {
			$common_options['language'] = $sLanguageCode;
		}
		elseif (file_exists(OKT_MODULES_PATH.'/rte_tinymce_4/tinyMCE_jquery/langs/'.$sSpecificLanguageCode.'.js')) {
			$common_options['language'] = $sSpecificLanguageCode;
		}

		# content CSS
		if ($okt->rte_tinymce_4->config->content_css != '') {
			$common_options['content_css'] = $okt->rte_tinymce_4->config->content_css;
		}

		# editor width
		if ($okt->rte_tinymce_4->config->width != '') {
			$common_options['width'] = $okt->rte_tinymce_4->config->width;
		}

		# editor height
		if ($okt->rte_tinymce_4->config->height != '') {
			$common_options['height'] = $okt->rte_tinymce_4->config->height;
		}

		$final_options = array_merge($options,$common_options,$user_options);

		$okt->page->js->addFile(OKT_MODULES_URL.'/rte_tinymce_4/tinyMCE_jquery/jquery.tinymce.min.js');

		$okt->page->js->addReady('
			jQuery("'.$element.'").tinymce('.json_encode($final_options).');
			jQuery("'.$element.'").closest("form").find(":submit").click(function() {
				tinyMCE.triggerSave();
			});
		');
	}

	protected static function addMediaManager(&$options)
	{
		global $okt;

		$options['file_browser_callback'] = 'filebrowser';

		$okt->page->js->addScript('
			function filebrowser(field_name, url, type, win) {

				fileBrowserURL = "'.$okt->config->app_path.OKT_ADMIN_DIR.'/module.php?m=media_manager&popup=1&editor=1&type=" + type;

				tinyMCE.activeEditor.windowManager.open({
						title: "Media manager",
						url: fileBrowserURL,
						width: 700,
						height: 450,
						inline: 1,
						maximizable: 1,
						resizable: 1,
						close_previous: 0,
						scrollbars: 1,
						popup_css : false
					},{
						window : win,
						input : field_name
					}
				);
			}
		');
	}

}
