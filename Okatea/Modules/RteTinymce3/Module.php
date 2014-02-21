<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\RteTinymce3;

use Okatea\Tao\Extensions\Modules\Module as BaseModule;

class Module extends BaseModule
{
	public $config = null;

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('rte_tinymce_3_config', __('m_rte_tinymce_3_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_rte_tinymce_3');
	}

	protected function prepend_admin()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'Okatea\Modules\RteTinymce3\Admin\Controller\Config' => __DIR__.'/Admin/Controller/Config.php'
		));

		$this->okt->page->addRte('tinymce_simple','tinyMCE simple',array('Okatea\\Modules\\RteTinymce3\\Module','tinyMCEsimple'));
		$this->okt->page->addRte('tinymce_advanced','tinyMCE advanced',array('Okatea\\Modules\\RteTinymce3\\Module','tinyMCEadvanced'));
		$this->okt->page->addRte('tinymce_experts','tinyMCE experts',array('Okatea\\Modules\\RteTinymce3\\Module','tinyMCEexperts'));

		# on ajoutent un item au menu configuration
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(
				__('TinyMCE 3'),
				$this->okt->adminRouter->generate('RteTinymce3_config'),
				$this->okt->request->attributes->get('_route') === 'RteTinymce3_config',
				40,
				$this->okt->checkPerm('rte_tinymce_3_config'),
				null
			);
		}
	}

	public static function tinyMCEsimple($element='textarea',$user_options=array())
	{
		global $okt;

		$options = array(
			'theme' => 'simple'
		);

		self::addCommon($element,$user_options,$options);
	}

	public static function tinyMCEadvanced($element='textarea',$user_options=array())
	{
		global $okt;

		$options = array(
			'theme' => 'advanced',

			'skin' => 'o2k7',
			'skin_variant' => 'silver',

			'convert_urls' => false,
			'entity_encoding' => 'raw',
			'force_p_newlines' => true,

			'plugins' => 'safari,inlinepopups,contextmenu,table,advlink,advimage,paste',
			'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,forecolorpicker,backcolorpicker,separator,fontselect,fontsizeselect,formatselect,styleselect',
			'theme_advanced_buttons1_add_before' => '',
			'theme_advanced_buttons1_add' => '',
			'theme_advanced_buttons2' => 'tablecontrols,separator,link,unlink,separator,image,separator,pastetext,pasteword,selectall,separator,cleanup,removeformat,code',
			'theme_advanced_buttons2_add_before' => '',
			'theme_advanced_buttons2_add' => '',
			'theme_advanced_buttons3' => '',
			'theme_advanced_buttons3_add_before' => '',
			'theme_advanced_buttons3_add' => '',
			'theme_advanced_buttons4' => '',
			'theme_advanced_buttons4_add_before' => '',
			'theme_advanced_buttons4_add' => '',
			'theme_advanced_toolbar_location' => 'top',
			'theme_advanced_toolbar_align' => 'left',
			'theme_advanced_path_location' => 'bottom',
			'theme_advanced_resizing' => true,

			'extended_valid_elements' => 'hr[class|width|size|noshade],font[face|size|color|style],span[id|class|align|style],script,noscript,object[name|id|classid|codebase|width|height|class|data|type],param[name|value],embed[src|type|width|height|pluginspage|autostart|showcontrols|animationatstart|transparentatstart|AllowChangeDisplaySize|AutoSize|DisplaySize|enabeContextMenu|windowless|ShowStatusBar|vspace|hspace|border|id|name|fullScreen]',

			'theme_advanced_resize_horizontal' => false,
			'theme_advanced_resizing' => true,

			'nonbreaking_force_tab' => true,
			'theme_advanced_font_sizes' => '10px=1,11px=2,12px=3,14px=4,16px=5,18px=6,20px=7',
			'font_size_style_values' => '10px,11px,12px,14px,16px,18px,20px',
			'apply_source_formatting' => true
		);

		if ($okt->modules->isLoaded('media_manager')) {
			self::addMediaManager($options);
		}

		self::addCommon($element,$user_options,$options);
	}

	public static function tinyMCEexperts($element='textarea',$user_options=array())
	{
		global $okt;

		$options = array(
			'theme' => 'advanced',

			'skin' => 'o2k7',
			'skin_variant' => 'silver',

			'convert_urls' => false,
			'entity_encoding' => 'raw',
			'force_p_newlines' => true,

			'plugins' => 'safari,inlinepopups,contextmenu,table,advlink,advimage,media,paste',
			'theme_advanced_buttons1' => 'bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,outdent,indent,separator,forecolorpicker,backcolorpicker,separator,fontselect,fontsizeselect,formatselect,styleselect',
			'theme_advanced_buttons1_add_before' => '',
			'theme_advanced_buttons1_add' => '',
			'theme_advanced_buttons2' => 'tablecontrols,separator,link,unlink,separator,image,media,separator,pastetext,pasteword,selectall,separator,cleanup,removeformat,code',
			'theme_advanced_buttons2_add_before' => '',
			'theme_advanced_buttons2_add' => '',
			'theme_advanced_buttons3' => '',
			'theme_advanced_buttons3_add_before' => '',
			'theme_advanced_buttons3_add' => '',
			'theme_advanced_buttons4' => '',
			'theme_advanced_buttons4_add_before' => '',
			'theme_advanced_buttons4_add' => '',
			'theme_advanced_toolbar_location' => 'top',
			'theme_advanced_toolbar_align' => 'left',
			'theme_advanced_path_location' => 'bottom',
			'theme_advanced_resizing' => true,

			'extended_valid_elements' => 'hr[class|width|size|noshade],font[face|size|color|style],span[id|class|align|style],script,noscript,object[name|id|classid|codebase|width|height|class|data|type],param[name|value],embed[src|type|width|height|pluginspage|autostart|showcontrols|animationatstart|transparentatstart|AllowChangeDisplaySize|AutoSize|DisplaySize|enabeContextMenu|windowless|ShowStatusBar|vspace|hspace|border|id|name|fullScreen]',

			'theme_advanced_resize_horizontal' => false,
			'theme_advanced_resizing' => true,

			'nonbreaking_force_tab' => true,
			'theme_advanced_font_sizes' => '10px=1,11px=2,12px=3,14px=4,16px=5,18px=6,20px=7',
			'font_size_style_values' => '10px,11px,12px,14px,16px,18px,20px',
			'apply_source_formatting' => true
		);

		if ($okt->modules->isLoaded('media_manager')) {
			self::addMediaManager($options);
		}

		self::addCommon($element,$user_options,$options);
	}

	protected static function addCommon($element, array $user_options=array(), array $options=array())
	{
		global $okt;

		$common_options = array(
			'script_url' => $okt->options->get('public_url').'/modules/RteTinymce3/tiny_mce/tiny_mce.js'
		);

		# language
		if (file_exists($okt->options->get('public_dir').'/modules/RteTinymce3/tiny_mce/langs/'.$okt->user->language.'.js')) {
			$common_options['language'] = $okt->user->language;
		}

		# content CSS
		if ($okt->RteTinymce3->config->content_css != '') {
			$common_options['content_css'] = $okt->RteTinymce3->config->content_css;
		}

		# editor width
		if ($okt->RteTinymce3->config->width != '') {
		$common_options['width'] = $okt->RteTinymce3->config->width;
		}

		# editor height
		if ($okt->RteTinymce3->config->height != '') {
			$common_options['height'] = $okt->RteTinymce3->config->height;
		}

		$final_options = array_merge($options,$common_options,$user_options);

		$okt->page->js->addFile($okt->options->get('public_url').'/modules/RteTinymce3/tiny_mce/jquery.tinymce.js');

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

				fileBrowserURL = "'.$okt->config->app_path.'admin/module.php?m=media_manager&popup=1&editor=1&type=" + type;

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
