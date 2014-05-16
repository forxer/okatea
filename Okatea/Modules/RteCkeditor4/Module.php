<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\RteCkeditor4;

use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Extensions\Modules\Module as BaseModule;

class Module extends BaseModule
{

	public $config = null;

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('rte_ckeditor_4_config', __('m_rte_ckeditor_4_perm_config'), 'configuration');
		
		# configuration
		//$this->config = $this->okt->newConfig('conf_rte_ckeditor_4');
	}

	protected function prepend_admin()
	{
		$this->okt->page->addRte('ckeditor_4', 'CKEditor 4', array(
			'Okatea\\Modules\\RteCkeditor4\\Module',
			'CKEditor'
		));
		
		# on ajoutent un item au menu configuration
		/*
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(
				__('TinyMCE 4'),
				$this->okt->adminRouter->generate('RteTinymce4_config'),
				$this->okt->request->attributes->get('_route') === 'RteTinymce4_config',
				40,
				$this->okt->checkPerm('rte_ckeditor_4_config'),
				null
			);
		}
		*/
	}

	public static function CKEditor($sSelector = 'textarea', array $aUserOptions = array())
	{
		global $okt;
		
		$aOptions = array();
		
		$okt->page->js->addFile($okt->options->get('public_url') . '/modules/RteCkeditor4/ckeditor/ckeditor.js');
		$okt->page->js->addFile($okt->options->get('public_url') . '/modules/RteCkeditor4/ckeditor/adapters/jquery.js');
		
		$okt->page->js->addScript('

			$("' . $sSelector . '").ckeditor({' . implode(',', $aOptions) . '});

		');
	}
}
