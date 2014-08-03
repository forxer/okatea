<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Tao\Images\ImageUploadConfig;
use Okatea\Tao\Themes\TemplatesSet;

class Config extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('pages_config'))
		{
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt['l10n']->loadFile(__DIR__ . '/../../Locales/%s/admin.config');

		# Gestion des images
		$oImageUploadConfig = new ImageUploadConfig($this->okt, $this->okt->module('Pages')->pages->getImageUpload());
		$oImageUploadConfig->setBaseUrl($this->generateUrl('Pages_config') . '?');

		# Gestionnaires de templates
		$oTemplatesList = new TemplatesSet($this->okt, $this->okt->module('Pages')->config->templates['list'], 'Pages/list', 'list', $this->generateUrl('Pages_config') . '?');

		$oTemplatesItem = new TemplatesSet($this->okt, $this->okt->module('Pages')->config->templates['item'], 'Pages/item', 'item', $this->generateUrl('Pages_config') . '?');

		$oTemplatesInsert = new TemplatesSet($this->okt, $this->okt->module('Pages')->config->templates['insert'], 'Pages/insert', 'insert', $this->generateUrl('Pages_config') . '?');

		$oTemplatesFeed = new TemplatesSet($this->okt, $this->okt->module('Pages')->config->templates['feed'], 'Pages/feed', 'feed', $this->generateUrl('Pages_config') . '?');

		# régénération des miniatures
		if ($this->okt['request']->query->has('minregen'))
		{
			$this->okt->module('Pages')->pages->getImageUpload()->regenMinImages();

			$this->okt['flash']->success(__('c_c_confirm_thumb_regenerated'));

			return $this->redirect($this->generateUrl('Pages_config'));
		}

		# suppression filigrane
		if ($this->okt['request']->query->has('delete_watermark'))
		{
			$this->okt->module('Pages')->config->write(array(
				'images' => $oImageUploadConfig->removeWatermak()
			));

			$this->okt['flash']->success(__('c_c_confirm_watermark_deleted'));

			return $this->redirect($this->generateUrl('Pages_config'));
		}

		# enregistrement configuration
		if ($this->okt['request']->request->has('form_sent'))
		{
			if ($this->okt['request']->request->has('p_perms'))
			{
				$p_perms = array_map('intval', $this->okt['request']->request->get('p_perms'));
			}
			else
			{
				$p_perms = array(
					0
				);
			}

			if (! $this->okt['flash']->hasError())
			{
				$aNewConf = array(
					'enable_metas' => $this->okt['request']->request->has('p_enable_metas'),
					'enable_filters' => $this->okt['request']->request->has('p_enable_filters'),

					'perms' => (array) $p_perms,
					'enable_group_perms' => $this->okt['request']->request->has('p_enable_group_perms'),

					'categories' => array(
						'enable' => $this->okt['request']->request->has('p_categories_enable'),
						'descriptions' => $this->okt['request']->request->has('p_categories_descriptions'),
						'rte' => $this->okt['request']->request->get('p_categories_rte', '')
					),

					'enable_rte' => $this->okt['request']->request->get('p_enable_rte', ''),

					'images' => $oImageUploadConfig->getPostConfig(),

					'files' => array(
						'enable' => $this->okt['request']->request->has('p_enable_files'),
						'number' => $this->okt['request']->request->getInt('p_number_files', 0),
						'allowed_exts' => $this->okt['request']->request->get('p_allowed_exts')
					),

					'templates' => array(
						'list' => $oTemplatesList->getPostConfig(),
						'item' => $oTemplatesItem->getPostConfig(),
						'insert' => $oTemplatesInsert->getPostConfig(),
						'feed' => $oTemplatesFeed->getPostConfig()
					),

					'name' => $this->okt['request']->request->get('p_name', array()),
					'name_seo' => $this->okt['request']->request->get('p_name_seo', array()),
					'title' => $this->okt['request']->request->get('p_title', array()),
					'meta_description' => $this->okt['request']->request->get('p_meta_description', array()),
					'meta_keywords' => $this->okt['request']->request->get('p_meta_keywords', array())
				);

				$this->okt->module('Pages')->config->write($aNewConf);

				$this->okt['flash']->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('Pages_config'));
			}
		}

		# Liste des groupes pour les permissions
		$aGroups = $this->okt->module('Pages')->pages->getUsersGroupsForPerms(true, true);

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
