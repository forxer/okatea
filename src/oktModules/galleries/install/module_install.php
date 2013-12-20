<?php
/**
 * @ingroup okt_module_galleries
 * @brief La classe d'installation du module galerie.
 *
 */

use Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_galleries extends ModuleInstall
{
	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_galleries_thumbnails',
			$this->okt->galleries->tree->regenMinImages(),
			'Regeneration of galleries thumbnails',
			'Cannot regenerate galleries thumbnails'
		);
		$this->checklist->addItem(
			'regenerate_items_thumbnails',
			$this->okt->galleries->items->regenMinImages(),
			'Regeneration of items thumbnails',
			'Cannot regenerate items thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'galleries',
			'galleries_manage',
			'galleries_add',
			'galleries_remove',
			'galleries_display'
		));
	}

	public function update()
	{
	}

}
