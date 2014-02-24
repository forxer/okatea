<?php
/**
 * @ingroup okt_module_pages
 * @brief La classe d'installation du Module Pages.
 *
 */

use Okatea\Tao\Extensions\Modules\Manage\Installer;

class Pages_installer extends Installer
{
	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->Pages->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
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
