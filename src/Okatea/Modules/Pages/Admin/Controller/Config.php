<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\Pages\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Tao\Images\ImageUploadConfig;
use Okatea\Tao\Themes\TemplatesSet;

class Config extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('pages_config')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../locales/'.$this->okt->user->language.'/admin.config');

		# Gestion des images
		$oImageUploadConfig = new ImageUploadConfig($this->okt, $this->okt->Pages->getImageUpload());
		$oImageUploadConfig->setBaseUrl($this->generateUrl('Pages_config').'?');

		# Gestionnaires de templates
		$oTemplatesList = new TemplatesSet($this->okt,
			$this->okt->Pages->config->templates['list'],
			'pages/list',
			'list',
			$this->generateUrl('Pages_config').'?'
		);

		$oTemplatesItem = new TemplatesSet($this->okt,
			$this->okt->Pages->config->templates['item'],
			'pages/item',
			'item',
			$this->generateUrl('Pages_config').'?'
		);

		$oTemplatesInsert = new TemplatesSet($this->okt,
			$this->okt->Pages->config->templates['insert'],
			'pages/insert',
			'insert',
			$this->generateUrl('Pages_config').'?'
		);

		$oTemplatesFeed = new TemplatesSet($this->okt,
			$this->okt->Pages->config->templates['feed'],
			'pages/feed',
			'feed',
			$this->generateUrl('Pages_config').'?'
		);


		# régénération des miniatures
		if ($this->okt->request->query->has('minregen'))
		{
			$this->okt->Pages->regenMinImages();

			$this->okt->page->flash->success(__('c_c_confirm_thumb_regenerated'));

			return $this->redirect($this->generateUrl('Pages_config'));
		}

		# suppression filigrane
		if ($this->okt->request->query->has('delete_watermark'))
		{
			$this->okt->Pages->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));

			$this->okt->page->flash->success(__('c_c_confirm_watermark_deleted'));

			return $this->redirect($this->generateUrl('Pages_config'));
		}

		# enregistrement configuration
		if ($this->okt->request->request->has('form_sent'))
		{
			if ($this->request->request->has('p_perms')) {
				$p_perms = array_map('intval', $this->request->request->get('p_perms'));
			}
			else {
				$p_perms = array(0);
			}

			if ($this->okt->error->isEmpty())
			{
				$new_conf = array(
					'enable_metas' => $this->okt->request->request->has('p_enable_metas'),
					'enable_filters' => $this->okt->request->request->has('p_enable_filters'),

					'perms' => (array)$p_perms,
					'enable_group_perms' => $this->okt->request->request->has('p_enable_group_perms'),

					'categories' => array(
						'enable' => $this->okt->request->request->has('p_categories_enable'),
						'descriptions' => $this->okt->request->request->has('p_categories_descriptions'),
						'rte' => $this->okt->request->request->get('p_categories_rte', '')
					),

					'enable_rte' => $this->okt->request->request->get('p_enable_rte', ''),

					'images' => $oImageUploadConfig->getPostConfig(),

					'files' => array(
						'enable' => $this->okt->request->request->has('p_enable_files'),
						'number' => $this->okt->request->request->getInt('p_number_files', 0),
						'allowed_exts' => $this->okt->request->request->get('p_allowed_exts')
					),

					'templates' => array(
						'list' => $oTemplatesList->getPostConfig(),
						'item' => $oTemplatesItem->getPostConfig(),
						'insert' => $oTemplatesInsert->getPostConfig(),
						'feed' => $oTemplatesFeed->getPostConfig()
					),

					'name' => $this->okt->request->request->get('p_name', array()),
					'name_seo' => $this->okt->request->request->get('p_name_seo', array()),
					'title' => $this->okt->request->request->get('p_title', array()),
					'meta_description' => $this->okt->request->request->get('p_meta_description', array()),
					'meta_keywords' => $this->okt->request->request->get('p_meta_keywords', array())
				);

				try
				{
					$this->okt->Pages->config->write($new_conf);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('Pages_config'));
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
		if ($this->okt->Pages->moduleUsersExists()) {
			$aGroups = $this->okt->Pages->getUsersGroupsForPerms(true,true);
		}

		return $this->render('Pages/Admin/Templates/Config', array(
			'oImageUploadConfig' => $oImageUploadConfig,
			'oTemplatesList' => $oTemplatesList,
			'oTemplatesItem' => $oTemplatesItem,
			'oTemplatesInsert' => $oTemplatesInsert,
			'oTemplatesFeed' => $oTemplatesFeed,
			'aGroups' => $aGroups
		));
	}
}
