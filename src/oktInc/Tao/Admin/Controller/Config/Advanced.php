<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Misc\Utilities;

class Advanced extends Controller
{
	protected $aPageData;

	public function page()
	{
		if (!$this->okt->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.advanced');

		# Données de la page
		$this->aPageData = new \ArrayObject();
		$this->aPageData['aNewConf'] = array();

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigInit
		$this->okt->triggers->callTrigger('adminAdvancedConfigInit', $this->okt, $this->aPageData);

		$this->othersHandleRequest();

		$this->pathUrlHandleRequest();

		$this->repositoriesHandleRequest();

		$this->updateHandleRequest();

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigHandleRequest
		$this->okt->triggers->callTrigger('adminAdvancedConfigHandleRequest', $this->okt, $this->aPageData);

		# save configuration
		if (!empty($_POST['form_sent']) && $this->okt->error->isEmpty())
		{
			try
			{
				$this->okt->config->write($this->aPageData['aNewConf']);

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_advanced'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new \ArrayObject;

		# onglet chemin et URL
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab_path_url',
			'title' => __('c_a_config_advanced_tab_path_url'),
			'content' => $this->renderView('Config/Advanced/Tabs/PathUrl')
		);

		# onglet dépôts
		$this->aPageData['tabs'][20] = array(
			'id' => 'tab_repositories',
			'title' => __('c_a_config_advanced_tab_repositories'),
			'content' => $this->renderView('Config/Advanced/Tabs/Repositories', array(
				'aModulesRepositories' => (array)$this->okt->config->modules_repositories,
				'aThemesRepositories' => (array)$this->okt->config->themes_repositories
			))
		);

		# onglet mises à jour
		$this->aPageData['tabs'][30] = array(
			'id' => 'tab_update',
			'title' => __('c_a_config_advanced_tab_update'),
			'content' => $this->renderView('Config/Advanced/Tabs/Update')
		);

		# onglet autres
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab_others',
			'title' => __('c_a_config_advanced_tab_others'),
			'content' => $this->renderView('Config/Advanced/Tabs/Others')
		);

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigBuildTabs
		$this->okt->triggers->callTrigger('adminAdvancedConfigBuildTabs', $this->okt, $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/Advanced/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function othersHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'public_maintenance_mode' => $this->request->request->has('p_public_maintenance_mode'),
			'admin_maintenance_mode' => $this->request->request->has('p_admin_maintenance_mode'),

			'htmlpurifier_disabled' => $this->request->request->has('p_htmlpurifier_disabled'),

			'user_visit' => array(
				'timeout' => $this->request->request->getInt('p_user_visit_timeout', 1800),
				'remember_time' => $this->request->request->getInt('p_user_visit_remember_time', 1209600)
			),

			'log_admin' => array(
				'ttl_months' => $this->request->request->getInt('p_log_admin_ttl_months', 3)
			),

			'news_feed' => array(
				'enabled' => $this->request->request->has('p_news_feed_enabled'),
				'url' => $this->request->request->get('p_news_feed_url', array())
			),

			'slug_type' => $this->request->request->get('p_slug_type', 'ascii')
		));
	}

	protected function pathUrlHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'app_path' => Utilities::formatAppPath($this->request->request->get('p_app_path', '/')),
			'domain' => Utilities::formatAppPath($this->request->request->get('p_domain', ''), false, false)
		));
	}
	protected function repositoriesHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'modules_repositories_enabled' => $this->request->request->has('p_modules_repositories_enabled'),
			'modules_repositories' => array_combine(
				$this->request->request->get('p_modules_repositories_names', array()),
				$this->request->request->get('p_modules_repositories_urls', array())
			),

			'themes_repositories_enabled' => $this->request->request->has('p_themes_repositories_enabled'),
			'themes_repositories' => array_combine(
				$this->request->request->get('p_themes_repositories_names', array()),
				$this->request->request->get('p_themes_repositories_urls', array())
			)
		));
	}

	protected function updateHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'update_enabled' => $this->request->request->has('p_update_enabled'),
			'update_url' => $this->request->request->get('p_update_url'),
			'update_type' => $this->request->request->get('p_update_type')
		));
	}
}