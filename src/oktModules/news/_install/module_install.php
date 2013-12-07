<?php
/**
 * @ingroup okt_module_news
 * @brief La classe d'installation du Module News.
 *
 */

use Tao\Modules\ModuleInstall;

class moduleInstall_news extends ModuleInstall
{
	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->news->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'news_usage',
			'news_publish',
			'news_delete',
			'news_contentadmin',
			'news_categories',
			'news_display'
		));
	}

	public function update()
	{
	}

} # class
