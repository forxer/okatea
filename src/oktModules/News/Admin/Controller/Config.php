<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\News\Admin\Controller;

use Tao\Admin\Controller;
use Tao\Images\ImageUploadConfig;
use Tao\Themes\TemplatesSet;

class Config extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('news_config')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../locales/'.$this->okt->user->language.'/admin.config');

		# Gestion des images
		$oImageUploadConfig = new ImageUploadConfig($this->okt,$this->okt->News->getImageUpload());
		$oImageUploadConfig->setBaseUrl($this->generateUrl('news_config').'?');

		# Gestionnaires de templates
		$oTemplatesList = new TemplatesSet($this->okt,
			$this->okt->News->config->templates['list'],
			'news/list',
			'list',
			$this->generateUrl('news_config').'?'
		);

		$oTemplatesItem = new TemplatesSet($this->okt,
			$this->okt->News->config->templates['item'],
			'news/item',
			'item',
			$this->generateUrl('news_config').'?'
		);

		$oTemplatesInsert = new TemplatesSet($this->okt,
			$this->okt->News->config->templates['insert'],
			'news/insert',
			'insert',
			$this->generateUrl('news_config').'?'
		);

		$oTemplatesFeed = new TemplatesSet($this->okt,
			$this->okt->News->config->templates['feed'],
			'news/feed',
			'feed',
			$this->generateUrl('news_config').'?'
		);

		# régénération des miniatures
		if (!empty($_GET['minregen']))
		{
			$this->okt->News->regenMinImages();

			$this->okt->page->flash->success(__('c_c_confirm_thumb_regenerated'));

			return $this->redirect($this->generateUrl('news_config'));
		}

		# suppression filigrane
		if (!empty($_GET['delete_watermark']))
		{
			$this->okt->News->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));

			$this->okt->page->flash->success(__('c_c_confirm_watermark_deleted'));

			return $this->redirect($this->generateUrl('news_config'));
		}

		# enregistrement configuration
		if (!empty($_POST['form_sent']))
		{
			$p_enable_metas = !empty($_POST['p_enable_metas']) ? true : false;
			$p_enable_filters = !empty($_POST['p_enable_filters']) ? true : false;

			$p_perms = !empty($_POST['p_perms']) && is_array($_POST['p_perms']) ? array_map('intval',$_POST['p_perms']) : array(0);
			$p_enable_group_perms = !empty($_POST['p_enable_group_perms']) ? true : false;

			$p_enable_rte = !empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';

			$p_categories_enable = !empty($_POST['p_categories_enable']) ? true : false;
			$p_categories_descriptions = !empty($_POST['p_categories_descriptions']) ? true : false;
			$p_categories_rte = !empty($_POST['p_categories_rte']) ? $_POST['p_categories_rte'] : '';

			$p_tpl_list = $oTemplatesList->getPostConfig();
			$p_tpl_item = $oTemplatesItem->getPostConfig();
			$p_tpl_insert = $oTemplatesInsert->getPostConfig();
			$p_tpl_feed = $oTemplatesFeed->getPostConfig();

			$aImagesConfig = $oImageUploadConfig->getPostConfig();

			$p_enable_files = !empty($_POST['p_enable_files']) ? true : false;
			$p_number_files = !empty($_POST['p_number_files']) ? intval($_POST['p_number_files']) : 0;
			$p_allowed_exts = !empty($_POST['p_allowed_exts']) ? $_POST['p_allowed_exts'] : '';

			$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name'])  ? $_POST['p_name'] : array();
			$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo'])  ? $_POST['p_name_seo'] : array();
			$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
			$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
			$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();

			if ($this->okt->error->isEmpty())
			{
				$new_conf = array(
						'enable_metas' => (boolean)$p_enable_metas,
						'enable_filters' => (boolean)$p_enable_filters,

						'perms' => (array)$p_perms,
						'enable_group_perms' => (boolean)$p_enable_group_perms,

						'categories' => array(
								'enable' => (boolean)$p_categories_enable,
								'descriptions' => (boolean)$p_categories_descriptions,
								'rte' => $p_categories_rte
						),

						'enable_rte' => $p_enable_rte,

						'images' => $aImagesConfig,

						'files' => array(
								'enable' => (boolean)$p_enable_files,
								'number' => (integer)$p_number_files,
								'allowed_exts' => $p_allowed_exts
						),

						'templates' => array(
								'list' => $p_tpl_list,
								'item' => $p_tpl_item,
								'insert' => $p_tpl_insert,
								'feed' => $p_tpl_feed
						),

						'name' => $p_name,
						'name_seo' => $p_name_seo,
						'title' => $p_title,
						'meta_description' => $p_meta_description,
						'meta_keywords' => $p_meta_keywords
				);

				try
				{
					$this->okt->News->config->write($new_conf);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('news_config'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		# Liste des groupes pour les permissions
		$aGroups = null;
		if ($this->okt->News->moduleUsersExists()) {
			$aGroups = $this->okt->News->getUsersGroupsForPerms(true,true);
		}

		return $this->render('news/Admin/Templates/Config', array(
			'oImageUploadConfig' 	=> $oImageUploadConfig,
			'oTemplatesList' 		=> $oTemplatesList,
			'oTemplatesItem' 		=> $oTemplatesItem,
			'oTemplatesInsert' 		=> $oTemplatesInsert,
			'oTemplatesFeed' 		=> $oTemplatesFeed,
			'aGroups' 				=> $aGroups
		));
	}
}
