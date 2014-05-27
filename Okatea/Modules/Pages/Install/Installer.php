<?php
/**
 * @ingroup okt_module_pages
 * @brief La classe d'installation du Module Pages.
 */
namespace Okatea\Modules\Pages\Install;

use Okatea\Tao\Extensions\Modules\Manage\Installer as BaseInstaller;

class Installer extends BaseInstaller
{

	public function installTestSet()
	{
		$this->checklist->addItem('regenerate_thumbnails', $this->okt->module('Pages')
			->pages->getImageUpload()->regenMinImages(), 'Regeneration of thumbnails', 'Cannot regenerate thumbnails');
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'pages',
			'pages_categories',
			'pages_add',
			'pages_remove',
			'pages_display'
		));
	}

	public function update()
	{
	}
}
