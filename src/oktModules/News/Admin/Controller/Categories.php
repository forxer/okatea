<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\News\Admin\Controller;

use Tao\Admin\Controller;

class Categories extends Controller
{
	public function page()
	{
		if (!$this->okt->News->config->categories['enable'] || !$this->okt->checkPerm('news_categories')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../locales/'.$this->okt->user->language.'/admin.categories');

		# Récupération de la liste complète des rubriques
		$rsCategories = $this->okt->News->categories->getCategories(array(
			'active' => 2,
			'with_count' => true,
			'language' => $this->okt->user->language
		));

		# switch statut
		if ($switchCategoryStatus = $this->switchCategoryStatus() !== false) {
			return $switchCategoryStatus;
		}

		# suppression d'une rubrique
		if ($deleteCategory = $this->deleteCategory() !== false) {
			return $deleteCategory;
		}

		return $this->render('news/Admin/Templates/Categories', array(
			'rsCategories' => $rsCategories
		));
	}

	protected function switchCategoryStatus()
	{
		$iCategoryId = $this->request->query->getInt('switch_status');

		if (!$iCategoryId) {
			return false;
		}

		try
		{
			$this->okt->News->categories->switchCategoryStatus($iCategoryId);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 32,
				'component' => 'news',
				'message' => 'category #'.$iCategoryId
			));

			$this->redirect($this->generateUrl('News_categories'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function deleteCategory()
	{
		$iCategoryId = $this->request->query->getInt('delete');

		if (!$iCategoryId) {
			return false;
		}

		try
		{
			$this->okt->News->categories->delCategory($iCategoryId);

			# log admin
			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'component' => 'news',
				'message' => 'category #'.$iCategoryId
			));

			$this->okt->page->flash->success(__('m_news_cat_deleted'));

			$this->redirect($this->generateUrl('News_categories'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}
}
