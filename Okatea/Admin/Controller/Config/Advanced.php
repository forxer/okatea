<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Okatea\Admin\Controller\Config;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Utilities;

class Advanced extends Controller
{
	protected $aPageData;

	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/advanced');

		$this->aPageData = new ArrayObject();
		$this->aPageData['values'] = [];

		$this->othersInit();

		$this->pathUrlInit();

		$this->repositoriesInit();

		$this->updateInit();

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigInit
		$this->okt['triggers']->callTrigger('adminAdvancedConfigInit', $this->aPageData);

		# save configuration
		if ($this->okt['request']->request->has('form_sent') && !$this->okt['instantMessages']->hasError())
		{
			$this->othersHandleRequest();

			$this->pathUrlHandleRequest();

			$this->repositoriesHandleRequest();

			$this->updateHandleRequest();

			# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigHandleRequest
			$this->okt['triggers']->callTrigger('adminAdvancedConfigHandleRequest', $this->aPageData);

			# save configuration
			if (!$this->okt['instantMessages']->hasError())
			{
				$this->okt['config']->write($this->aPageData['values']);

				$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_advanced'));
			}
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new ArrayObject();

		# onglet chemin et URL
		$this->aPageData['tabs'][10] = [
			'id' => 'tab_path_url',
			'title' => __('c_a_config_advanced_tab_path_url'),
			'content' => $this->renderView('Config/Advanced/Tabs/PathUrl', [
				'aPageData' => $this->aPageData
			])
		];

		# onglet dépôts
		$this->aPageData['tabs'][20] = [
			'id' => 'tab_repositories',
			'title' => __('c_a_config_advanced_tab_repositories'),
			'content' => $this->renderView('Config/Advanced/Tabs/Repositories', [
				'aPageData' => $this->aPageData
			])
		];

		# onglet mises à jour
		$this->aPageData['tabs'][30] = [
			'id' => 'tab_update',
			'title' => __('c_a_config_advanced_tab_update'),
			'content' => $this->renderView('Config/Advanced/Tabs/Update', [
				'aPageData' => $this->aPageData
			])
		];

		# onglet autres
		$this->aPageData['tabs'][40] = [
			'id' => 'tab_others',
			'title' => __('c_a_config_advanced_tab_others'),
			'content' => $this->renderView('Config/Advanced/Tabs/Others', [
				'aPageData' => $this->aPageData
			])
		];

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigBuildTabs
		$this->okt['triggers']->callTrigger('adminAdvancedConfigBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/Advanced/Page', [
			'aPageData' => $this->aPageData
		]);
	}

	protected function othersInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'maintenance' => [
				'public'    => $this->okt['config']->maintenance['public'],
				'admin'     => $this->okt['config']->maintenance['admin']
			],

			'htmlpurifier_disabled' => $this->okt['config']->htmlpurifier_disabled,

			'user_visit' => [
				'timeout'       => $this->okt['config']->user_visit['timeout'],
				'remember_time' => $this->okt['config']->user_visit['remember_time']
			],

			'log_admin' => [
				'ttl_months' => $this->okt['config']->log_admin['ttl_months']
			],

			'news_feed' => [
				'enabled'   => $this->okt['config']->news_feed['enabled'],
				'url'       => $this->okt['config']->news_feed['url']
			],

			'slug_type' => $this->okt['config']->slug_type
		]);
	}

	protected function pathUrlInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'app_url'    => $this->okt['config']->app_url,
			'domain'     => $this->okt['config']->domain
		]);
	}

	protected function repositoriesInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'repositories' => [
				'themes' => [
					'enabled'  => $this->okt['config']->repositories['themes']['enabled'],
					'list'     => $this->okt['config']->repositories['themes']['list']
				],
				'modules' => [
					'enabled'  => $this->okt['config']->repositories['modules']['enabled'],
					'list'     => $this->okt['config']->repositories['modules']['list']
				]
			]
		]);
	}

	protected function updateInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'updates' => [
				'enabled'   => $this->okt['config']->updates['enabled'],
				'url'       => $this->okt['config']->updates['url'],
				'type'      => $this->okt['config']->updates['type']
			]
		]);
	}

	protected function othersHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'maintenance' => [
				'public'    => $this->okt['request']->request->has('p_maintenance_public'),
				'admin'     => $this->okt['request']->request->has('p_maintenance_admin')
			],

			'htmlpurifier_disabled' => $this->okt['request']->request->has('p_htmlpurifier_disabled'),

			'user_visit' => [
				'timeout'       => $this->okt['request']->request->getInt('p_user_visit_timeout', 1800),
				'remember_time' => $this->okt['request']->request->getInt('p_user_visit_remember_time', 1209600)
			],

			'log_admin' => [
				'ttl_months' => $this->okt['request']->request->getInt('p_log_admin_ttl_months', 3)
			],

			'news_feed' => [
				'enabled'   => $this->okt['request']->request->has('p_news_feed_enabled'),
				'url'       => $this->okt['request']->request->get('p_news_feed_url', [])
			],

			'slug_type' => $this->okt['request']->request->get('p_slug_type', 'ascii')
		]);
	}

	protected function pathUrlHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'app_url'    => Utilities::formatAppPath($this->okt['request']->request->get('p_app_url', '/')),
			'domain'     => Utilities::formatAppPath($this->okt['request']->request->get('p_domain', ''), false, false)
		]);
	}

	protected function repositoriesHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'repositories' => [
				'themes' => [
					'enabled'  => $this->okt['request']->request->has('p_themes_repositories_enabled'),
					'list'     => array_filter(array_combine($this->okt['request']->request->get('p_themes_repositories_names', []), $this->okt['request']->request->get('p_themes_repositories_urls', [])))
				],
				'modules' => [
					'enabled'  => $this->okt['request']->request->has('p_modules_repositories_enabled'),
					'list'     => array_filter(array_combine($this->okt['request']->request->get('p_modules_repositories_names', []), $this->okt['request']->request->get('p_modules_repositories_urls', [])))
				]
			]
		]);
	}

	protected function updateHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'updates' => [
				'enabled'   => $this->okt['request']->request->has('p_updates_enabled'),
				'url'       => $this->okt['request']->request->get('p_updates_url'),
				'type'      => $this->okt['request']->request->get('p_updates_type')
			]
		]);
	}
}
