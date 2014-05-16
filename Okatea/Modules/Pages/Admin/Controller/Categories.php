<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages\Admin\Controller;

use Okatea\Admin\Controller;

class Categories extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('pages_categories'))
		{
			return $this->serve401();
		}
		elseif (! $this->okt->module('Pages')->config->categories['enable'])
		{
			return $this->serve404();
		}
		
		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.categories');
		
		# Récupération de la liste complète des rubriques
		$rsCategories = $this->okt->module('Pages')->categories->getCategories(array(
			'active' => 2,
			'with_count' => true,
			'language' => $this->okt->user->language
		));
		
		# switch statut
		if (($action = $this->switchCategoryStatus()) !== false)
		{
			return $action;
		}
		
		# suppression d'une rubrique
		if (($action = $this->deleteCategory()) !== false)
		{
			return $action;
		}
		
		return $this->render('Pages/Admin/Templates/Categories', array(
			'rsCategories' => $rsCategories
		));
	}

	protected function switchCategoryStatus()
	{
		$iCategoryId = $this->request->query->getInt('switch_status');
		
		if (! $iCategoryId)
		{
			return false;
		}
		
		try
		{
			$this->okt->module('Pages')->categories->switchCategoryStatus($iCategoryId);
			
			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 32,
				'component' => 'pages',
				'message' => 'category #' . $iCategoryId
			));
			
			return $this->redirect($this->generateUrl('Pages_categories'));
		}
		catch (Exception $e)
		{
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function deleteCategory()
	{
		$iCategoryId = $this->request->query->getInt('delete');
		
		if (! $iCategoryId)
		{
			return false;
		}
		
		try
		{
			$this->okt->module('Pages')->categories->delCategory($iCategoryId);
			
			# log admin
			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'component' => 'pages',
				'message' => 'category #' . $iCategoryId
			));
			
			$this->okt->page->flash->success(__('m_pages_cat_deleted'));
			
			return $this->redirect($this->generateUrl('Pages_categories'));
		}
		catch (Exception $e)
		{
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}
}
