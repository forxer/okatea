<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\RteTinymce4;

use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Extensions\Modules\Module as BaseModule;

class Module extends BaseModule
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
		$this->okt->page->addRte('tinymce_4', 'tinyMCE 4', array(
			'Okatea\\Modules\\RteTinymce4\\Module',
			'tinyMCE'
		));
		
		# on ajoutent un item au menu configuration
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(__('TinyMCE 4'), $this->okt->adminRouter->generate('RteTinymce4_config'), $this->okt->request->attributes->get('_route') === 'RteTinymce4_config', 40, $this->okt->checkPerm('rte_tinymce_4_config'), null);
		}
	}

	public static function tinyMCE($sSelector = 'textarea', array $aUserOptions = array())
	{
		global $okt;
		
		$aOptions = array();
		
		$aOptions[] = 'relative_urls: true';
		$aOptions[] = 'document_base_url: "' . Escaper::js($okt->request->getSchemeAndHttpHost() . $okt['config']->app_path) . '"';
		
		# selector
		$aOptions[] = 'selector: "' . $sSelector . '"';
		
		# theme
		$aOptions[] = 'theme: "modern"';
		
		# language
		$sLanguageCode = strtolower($okt->user->language);
		$sSpecificLanguageCode = strtolower($okt->user->language) . '_' . strtoupper($okt->user->language);
		
		if (file_exists($okt->options->get('public_dir') . '/modules/RteTinymce4/tinymce/langs/' . $sLanguageCode . '.js'))
		{
			$aOptions[] = 'language: "' . $sLanguageCode . '"';
		}
		elseif (file_exists($okt->options->get('public_dir') . '/modules/RteTinymce4/tinymce/langs/' . $sSpecificLanguageCode . '.js'))
		{
			$aOptions[] = 'language: "' . $sSpecificLanguageCode . '"';
		}
		
		# plugins
		$aOptions[] = 'plugins: "advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table contextmenu paste"';
		
		# toolbar
		$aOptions[] = 'toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"';
		
		# content CSS
		if ($okt->module('RteTinymce4')->config->content_css != '')
		{
			$aOptions[] = 'content_css: "' . $okt->module('RteTinymce4')->config->content_css . '"';
		}
		
		# editor width
		if ($okt->module('RteTinymce4')->config->width != '')
		{
			$aOptions[] = 'width: "' . $okt->module('RteTinymce4')->config->width . '"';
		}
		
		# editor height
		if ($okt->module('RteTinymce4')->config->height != '')
		{
			$aOptions[] = 'height: "' . $okt->module('RteTinymce4')->config->height . '"';
		}
		
		# gestionnaire de media
		if ($okt->modules->isLoaded('media_manager'))
		{
			$aOptions[] = 'file_browser_callback: function (field_name, url, type, win) {
					tinymce.activeEditor.windowManager.open({
						title: "Media manager",
						url: "' . $okt['config']->app_path . 'admin/module.php?m=media_manager&popup=1&editor=1&type=" + type,
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
		
		$okt->page->js->addFile($okt->options->get('public_url') . '/modules/RteTinymce4/tinymce/tinymce.min.js');
		
		$okt->page->js->addScript('

			tinymce.init({' . implode(',', $aOptions) . '});

		');
	}
}
