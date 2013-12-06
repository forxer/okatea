<?php
/**
 * @ingroup okt_module_rte_tinyMCE_3
 * @brief La classe principale du module.
 *
 */

use Okatea\Modules\Module;

class module_rte_tinymce_3 extends Module
{
	public $config = null;

	protected function prepend()
	{
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# permissions
		$this->okt->addPerm('rte_tinymce_3_config', __('m_rte_tinymce_3_perm_config'), 'configuration');

		# configuration
		$this->config = $this->okt->newConfig('conf_rte_tinymce_3');
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		$this->okt->page->addRte('tinymce_3_simple','tinyMCE 3 simple',array('module_rte_tinymce_3','tinyMCEsimple'));
		$this->okt->page->addRte('tinymce_3_advanced','tinyMCE 3 advanced',array('module_rte_tinymce_3','tinyMCEadvanced'));
		$this->okt->page->addRte('tinymce_3_experts','tinyMCE 3 experts',array('module_rte_tinymce_3','tinyMCEexperts'));

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('TinyMCE 3'),
				'module.php?m=rte_tinymce_3&amp;action=config',
				ON_RTE_TINYMCE_3_MODULE && ($this->okt->page->action === 'config'),
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

		if ($okt->modules->moduleExists('media_manager')) {
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

		if ($okt->modules->moduleExists('media_manager')) {
			self::addMediaManager($options);
		}

		self::addCommon($element,$user_options,$options);
	}

	protected static function addCommon($element, array $user_options=array(), array $options=array())
	{
		global $okt;

		$common_options = array(
			'script_url' => OKT_MODULES_URL.'/rte_tinymce_3/tinyMCE_jquery/tiny_mce.js',
			'language' => $okt->user->language,
		);

		# content CSS
		if ($okt->rte_tinymce_3->config->content_css != '') {
			$common_options['content_css'] = $okt->rte_tinymce_3->config->content_css;
		}

		# editor width
		if ($okt->rte_tinymce_3->config->width != '') {
			$common_options['width'] = $okt->rte_tinymce_3->config->width;
		}

		# editor height
		if ($okt->rte_tinymce_3->config->height != '') {
			$common_options['height'] = $okt->rte_tinymce_3->config->height;
		}

		$final_options = array_merge($options,$common_options,$user_options);

		$okt->page->js->addFile(OKT_MODULES_URL.'/rte_tinymce_3/tinyMCE_jquery/jquery.tinymce.js');

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
