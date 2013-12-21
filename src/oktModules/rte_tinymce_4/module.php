<?php
/**
 * @ingroup okt_module_rte_tinyMCE_4
 * @brief La classe principale du module.
 *
 */

use Tao\Modules\Module;

class module_rte_tinymce_4 extends Module
{
	public $config = null;

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('rte_tinymce_4_config', __('m_rte_tinymce_4_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_rte_tinymce_4');
	}

	protected function prepend_admin()
	{
		$this->okt->page->addRte('tinymce_4','tinyMCE 4',array('module_rte_tinymce_4','tinyMCE'));

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('TinyMCE 4'),
				'module.php?m=rte_tinymce_4&amp;action=config',
				$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
				40,
				$this->okt->checkPerm('rte_tinymce_4_config'),
				null
			);
		}
	}

	public static function tinyMCE($element='textarea', $user_options=array())
	{
		global $okt;

		$aOptions = array();

		# selector
		$aOptions[] = 'selector: "'.$element.'"';

		# theme
		$aOptions[] = 'theme: "modern"';

		# language
		$sLanguageCode = strtolower($okt->user->language);
		$sSpecificLanguageCode = strtolower($okt->user->language).'_'.strtoupper($okt->user->language);

		if (file_exists(OKT_MODULES_PATH.'/rte_tinymce_4/tinymce/langs/'.$sLanguageCode.'.js')) {
			$aOptions[] = 'language: "'.$sLanguageCode.'"';
		}
		elseif (file_exists(OKT_MODULES_PATH.'/rte_tinymce_4/tinymce/langs/'.$sSpecificLanguageCode.'.js')) {
			$aOptions[] = 'language: "'.$sSpecificLanguageCode.'"';
		}

		# plugins
		$aOptions[] = 'plugins: "advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table contextmenu paste"';

		# toolbar
		$aOptions[] = 'toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"';

		# content CSS
		if ($okt->rte_tinymce_4->config->content_css != '') {
			$aOptions[] = 'content_css: "'.$okt->rte_tinymce_4->config->content_css.'"';
		}

		# editor width
		if ($okt->rte_tinymce_4->config->width != '') {
			$aOptions[] = 'width: "'.$okt->rte_tinymce_4->config->width.'"';
		}

		# editor height
		if ($okt->rte_tinymce_4->config->height != '') {
			$aOptions[] = 'height: "'.$okt->rte_tinymce_4->config->height.'"';
		}

		# gestionnaire de media
		if ($okt->modules->moduleExists('media_manager'))
		{
			$aOptions[] = 'file_browser_callback: function (field_name, url, type, win) {
					tinymce.activeEditor.windowManager.open({
						title: "Media manager",
						url: "'.$okt->config->app_path.OKT_ADMIN_DIR.'/module.php?m=media_manager&popup=1&editor=1&type=" + type,
						width: 700,
						height: 450
					}, {
					oninsert: function(url) {
						var fieldElm = win.document.getElementById(field_name);

						fieldElm.value = url;
						if ("createEvent" in document) {
							var evt = document.createEvent("HTMLEvents");
							evt.initEvent("change", false, true);
							fieldElm.dispatchEvent(evt);
						} else {
							fieldElm.fireEvent("onchange");
						}
					}
				});
			}';
		}

		$okt->page->js->addFile(OKT_MODULES_URL.'/rte_tinymce_4/tinymce/tinymce.min.js');

		$okt->page->js->addScript('

			tinymce.init({'.
				implode(',', $aOptions).
			'});

		');
	}

}
