<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\News\Install;

use Okatea\Tao\Extensions\Modules\Manage\Installer as BaseInstaller;

class Installer extends BaseInstaller
{

	public function installTestSet()
	{
		$this->checklist->addItem('regenerate_thumbnails', $this->okt->module('News')
			->regenMinImages(), 'Regeneration of thumbnails', 'Cannot regenerate thumbnails');
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
}
