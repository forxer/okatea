<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Utilities;

class Advanced extends Controller
{
	protected $aPageData;

	public function page()
	{
		if (!$this->okt->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/%s/admin/advanced');

		# Données de la page
		$this->aPageData = new \ArrayObject();
		$this->aPageData['values'] = array();

		$this->othersInit();

		$this->pathUrlInit();

		$this->repositoriesInit();

		$this->updateInit();

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigInit
		$this->okt->triggers->callTrigger('adminAdvancedConfigInit', $this->aPageData);


		# save configuration
		if ($this->request->request->has('form_sent') && $this->okt->error->isEmpty())
		{
			$this->othersHandleRequest();

			$this->pathUrlHandleRequest();

			$this->repositoriesHandleRequest();

			$this->updateHandleRequest();

			# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigHandleRequest
			$this->okt->triggers->callTrigger('adminAdvancedConfigHandleRequest', $this->aPageData);

			# save configuration
			if ($this->okt->error->isEmpty())
			{
				try
				{
					$this->okt->config->write($this->aPageData['values']);

					$this->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('config_advanced'));
				}
				catch (Exception $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new \ArrayObject;

		# onglet chemin et URL
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab_path_url',
			'title' => __('c_a_config_advanced_tab_path_url'),
			'content' => $this->renderView('Config/Advanced/Tabs/PathUrl', array('aPageData' => $this->aPageData))
		);

		# onglet dépôts
		$this->aPageData['tabs'][20] = array(
			'id' => 'tab_repositories',
			'title' => __('c_a_config_advanced_tab_repositories'),
			'content' => $this->renderView('Config/Advanced/Tabs/Repositories', array(
				'aPageData' => $this->aPageData
			))
		);

		# onglet mises à jour
		$this->aPageData['tabs'][30] = array(
			'id' => 'tab_update',
			'title' => __('c_a_config_advanced_tab_update'),
			'content' => $this->renderView('Config/Advanced/Tabs/Update', array('aPageData' => $this->aPageData))
		);

		# onglet autres
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab_others',
			'title' => __('c_a_config_advanced_tab_others'),
			'content' => $this->renderView('Config/Advanced/Tabs/Others', array('aPageData' => $this->aPageData))
		);

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigBuildTabs
		$this->okt->triggers->callTrigger('adminAdvancedConfigBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/Advanced/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function othersInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'maintenance' => array(
				'public' => $this->okt->config->maintenance['public'],
				'admin' => $this->okt->config->maintenance['admin']
			),

			'htmlpurifier_disabled' => $this->okt->config->htmlpurifier_disabled,

			'user_visit' => array(
				'timeout' => $this->okt->config->user_visit['timeout'],
				'remember_time' => $this->okt->config->user_visit['remember_time']
			),

			'log_admin' => array(
				'ttl_months' => $this->okt->config->log_admin['ttl_months']
			),

			'news_feed' => array(
				'enabled' => $this->okt->config->news_feed['enabled'],
				'url' => $this->okt->config->news_feed['url']
			),

			'slug_type' => $this->okt->config->slug_type
		));
	}

	protected function pathUrlInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'app_path' => $this->okt->config->app_path,
			'domain' => $this->okt->config->domain
		));
	}

	protected function repositoriesInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'repositories' => array(
				'themes' => array(
					'enabled' => $this->okt->config->repositories['themes']['enabled'],
					'list' => $this->okt->config->repositories['themes']['list']
				),
				'modules' => array(
					'enabled' => $this->okt->config->repositories['modules']['enabled'],
					'list' => $this->okt->config->repositories['modules']['list']
				)
			)
		));
	}

	protected function updateInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'updates' => array(
				'enabled'   => $this->okt->config->updates['enabled'],
				'url'       => $this->okt->config->updates['url'],
				'type'      => $this->okt->config->updates['type']
			)
		));
	}

	protected function othersHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'maintenance' => array(
				'public'        => $this->request->request->has('p_maintenance_public'),
				'admin'         => $this->request->request->has('p_maintenance_admin')
			),

			'htmlpurifier_disabled' => $this->request->request->has('p_htmlpurifier_disabled'),

			'user_visit' => array(
				'timeout'       => $this->request->request->getInt('p_user_visit_timeout', 1800),
				'remember_time' => $this->request->request->getInt('p_user_visit_remember_time', 1209600)
			),

			'log_admin' => array(
				'ttl_months'    => $this->request->request->getInt('p_log_admin_ttl_months', 3)
			),

			'news_feed' => array(
				'enabled'       => $this->request->request->has('p_news_feed_enabled'),
				'url'           => $this->request->request->get('p_news_feed_url', array())
			),

			'slug_type'         => $this->request->request->get('p_slug_type', 'ascii')
		));
	}

	protected function pathUrlHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'app_path'   => Utilities::formatAppPath($this->request->request->get('p_app_path', '/')),
			'domain'     => Utilities::formatAppPath($this->request->request->get('p_domain', ''), false, false)
		));
	}
	protected function repositoriesHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'repositories' => array(
				'themes' => array(
					'enabled' => $this->request->request->has('p_themes_repositories_enabled'),
					'list' => array_filter(array_combine(
						$this->request->request->get('p_themes_repositories_names', array()),
						$this->request->request->get('p_themes_repositories_urls', array())
					))
				),
				'modules' => array(
					'enabled' => $this->request->request->has('p_modules_repositories_enabled'),
					'list' => array_filter(array_combine(
						$this->request->request->get('p_modules_repositories_names', array()),
						$this->request->request->get('p_modules_repositories_urls', array())
					))
				)
			)
		));
	}

	protected function updateHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'updates' => array(
				'enabled' => $this->request->request->has('p_updates_enabled'),
				'url' => $this->request->request->get('p_updates_url'),
				'type' => $this->request->request->get('p_updates_type')
			)
		));
	}
}
