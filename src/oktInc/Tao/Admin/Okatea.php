<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tao\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Tao\Admin\Menu as AdminMenu;
use Tao\Admin\Page;
use Tao\Core\Application;
use Tao\Core\LogAdmin;

class Okatea extends Application
{
	/**
	 * L'utilitaire de contenu de page.
	 *
	 * @var Tao\Html\Page
	 */
	public $page;

	/**
	 * Le gestionnaire de log admin.
	 *
	 * @var Tao\Core\LogAdmin
	 */
	public $logAdmin;

	/**
	 * Le routeur interne de l'administration.
	 *
	 * @var Tao\Routing\AdminRouter
	 */
	public $adminRouter;

	/**
	 * Constructor.
	 *
	 * @param Composer\Autoload\ClassLoader $autoloader
	 * @param string $sRootPath
	 * @param array $aOptions
	public function __construct($autoloader, $sRootPath, array $aOptions = array())
	{
		parent::__construct($autoloader, $sRootPath, $aOptions);
	}
	 */

	/**
	 * Run application.
	 *
	 */
	public function run()
	{
		parent::run();

		$this->adminRouter = new Router(
			$this,
			$this->options->get('config_dir').'/routes_admin',
			$this->options->get('cache_dir').'/routing/admin',
			$this->options->get('debug'),
			$this->logger
		);

		$this->loadLogAdmin();

		$this->loadPageHelpers();

		$this->matchRequest();

		if ($this->checkUser() === true)
		{
			$this->defineAdminPerms();

			$this->buildAdminMenu();

			$this->loadTplEngine();

			$this->loadModules('admin');

			$this->callController();
		}

		$this->sendResponse();
	}

	protected function loadLogAdmin()
	{
		$this->logAdmin = new LogAdmin($this);
	}

	/**
	 * Init content page helpers.
	 *
	 * @return \Tao\Website\Page
	 */
	protected function loadPageHelpers()
	{
		$this->page = new Page($this);
	}

	/**
	 * Load templates engine.
	 *
	 * return void
	 */
	protected function loadTplEngine()
	{
		$this->setTplDirectory(__DIR__.'/templates/%name%.php');
		$this->setTplDirectory($this->options->get('modules_dir').'/%name%.php');

		# initialisation
		$this->tpl = new Templating($this, $this->aTplDirectories);

		# assignation par défaut
		$this->tpl->addGlobal('okt', $this);
	}

	protected function checkUser()
	{
		# Validation du CSRF token si un formulaire est envoyé
		if (count($this->request->request) > 0 && (!$this->request->request->has($this->options->get('csrf_token_name')) || !$this->session->isValidToken($this->request->request->get($this->options->get('csrf_token_name')))))
		{
			$this->page->flash->error(__('c_c_auth_bad_csrf_token'));

			$this->user->logout();

			$this->logAdmin->critical(array(
				'user_id' => $this->user->infos->f('id'),
				'username' => $this->user->infos->f('username'),
				'message' => 'Security CSRF blocking',
				'code' => 0
			));

			return $this->response = new RedirectResponse($this->adminRouter->generate('login'));
		}

		# Vérification de l'utilisateur en cours sur les parties de l'administration où l'utilisateur doit être identifié
		if ($this->request->attributes->get('_route') !== 'login' && $this->request->attributes->get('_route') !== 'forget_password')
		{
			# on stocke l'URL de la page dans un cookie
			$this->user->setAuthFromCookie($this->request->getUri());

			# si c'est un invité, il n'a rien à faire ici
			if ($this->user->is_guest)
			{
				$this->page->flash->warning(__('c_c_auth_not_logged_in'));

				$this->response = new RedirectResponse($this->adminRouter->generate('login'));

				return false;
			}

			# il faut au minimum la permission d'utilisation de l'interface d'administration
			elseif (!$this->checkPerm('usage'))
			{
				$this->page->flash->error(__('c_c_auth_restricted_access'));

				$this->user->logout();

				$this->response = new RedirectResponse($this->adminRouter->generate('login'));

				return false;
			}

			# enfin, si on est en maintenance, il faut être superadmin
			elseif ($this->config->admin_maintenance_mode && !$this->user->is_superadmin)
			{
				$this->page->flash->error(__('c_c_auth_admin_maintenance_mode'));

				$this->user->logout();

				$this->response = new RedirectResponse($this->adminRouter->generate('login'));

				return false;
			}
		}

		return true;
	}

	protected function buildAdminMenu()
	{
		if (!$this->page->display_menu) {
			return null;
		}

		# Menu principal
		$this->page->mainMenu = new AdminMenu(
			'mainMenu-'.($this->config->admin_sidebar_position == 0 ? 'left' : 'right'),
			Page::$formatHtmlMainMenu);

		# Accueil
		$this->page->mainMenu->add(
			/* titre*/ 		__('c_a_menu_home'),
			/* URL */ 		$this->adminRouter->generate('home'),
			/* actif ? */	$this->request->attributes->get('_route') === 'home',
			/* position */	1,
			/* visible ? */	true,
			/* ID */ 		null,
			/* Sub */		($this->page->homeSubMenu = new AdminMenu(null,Page::$formatHtmlSubMenu)),
			/* Icon */		$this->options->public_url.'/img/admin/start-here.png'
		);
			$this->page->homeSubMenu->add(
				__('c_a_menu_roundabout'),
				$this->adminRouter->generate('home'),
				$this->request->attributes->get('_route') === 'home',
				10,
				true
			);

			# Configuration
			$this->page->mainMenu->add(
				__('c_a_menu_configuration'),
				$this->adminRouter->generate('config_general'),
					$this->request->attributes->get('_route') === 'config_general',
				10000000,
				$this->checkPerm('configsite'),
				null,
				($this->page->configSubMenu = new AdminMenu(null,Page::$formatHtmlSubMenu)),
				$this->options->public_url.'/img/admin/network-server.png'
			);
				$this->page->configSubMenu->add(__('c_a_menu_general'), $this->adminRouter->generate('config_general'),
					$this->request->attributes->get('_route') === 'config_general',
					10,
					$this->checkPerm('configsite')
				);
				$this->page->configSubMenu->add(__('c_a_menu_display'), $this->adminRouter->generate('config_display'),
					$this->request->attributes->get('_route') === 'config_display',
					20,
					$this->checkPerm('display')
				);
				$this->page->configSubMenu->add(__('c_a_menu_localization'), $this->adminRouter->generate('config_languages'),
					$this->request->attributes->get('_route') === 'config_languages',
					60,
					$this->checkPerm('languages')
				);
				$this->page->configSubMenu->add(__('c_a_menu_modules'), $this->adminRouter->generate('config_modules'),
					$this->request->attributes->get('_route') === 'config_modules',
					70,
					$this->checkPerm('modules')
				);
				$this->page->configSubMenu->add(__('c_a_menu_themes'), $this->adminRouter->generate('config_themes'),
					in_array($this->request->attributes->get('_route'), array('config_themes', 'config_theme', 'config_theme_add')),
					80,
					$this->checkPerm('themes')
				);
				$this->page->configSubMenu->add(__('c_a_menu_navigation'), $this->adminRouter->generate('config_navigation'),
					$this->request->attributes->get('_route') === 'config_navigation',
					90,
					$this->checkPerm('navigation')
				);
				$this->page->configSubMenu->add(__('c_a_menu_permissions'), $this->adminRouter->generate('config_permissions'),
					$this->request->attributes->get('_route') === 'config_permissions',
					100,
					$this->checkPerm('permissions')
				);
				$this->page->configSubMenu->add(__('c_a_menu_tools'), $this->adminRouter->generate('config_tools'),
					$this->request->attributes->get('_route') === 'config_tools',
					110,
					$this->checkPerm('tools')
				);
				$this->page->configSubMenu->add(__('c_a_menu_infos'), $this->adminRouter->generate('config_infos'),
					$this->request->attributes->get('_route') === 'config_infos',
					120,
					$this->checkPerm('infos')
				);
				$this->page->configSubMenu->add(__('c_a_menu_update'), $this->adminRouter->generate('config_update'),
					$this->request->attributes->get('_route') === 'config_update',
					130,
					$this->config->update_enabled && $this->checkPerm('is_superadmin')
				);
				$this->page->configSubMenu->add(__('c_a_menu_log_admin'), $this->adminRouter->generate('config_logadmin'),
					$this->request->attributes->get('_route') === 'config_logadmin',
					140,
					$this->checkPerm('is_superadmin')
				);
				$this->page->configSubMenu->add(__('c_a_menu_router'), $this->adminRouter->generate('config_router'),
					$this->request->attributes->get('_route') === 'config_router',
					150,
					$this->checkPerm('is_superadmin')
				);
				$this->page->configSubMenu->add(__('c_a_menu_advanced'), $this->adminRouter->generate('config_advanced'),
					$this->request->attributes->get('_route') === 'config_advanced',
					160,
					$this->checkPerm('is_superadmin')
				);
	}

	protected function defineAdminPerms()
	{
		$this->addPerm('usage', __('c_a_def_perm_usage'));

		$this->addPermGroup('configuration', __('c_a_def_perm_config'));
			$this->addPerm('configsite', 	__('c_a_def_perm_config_website'), 'configuration');
			$this->addPerm('display', 		__('c_a_def_perm_config_display'), 'configuration');
			$this->addPerm('languages', 	__('c_a_def_perm_config_local'), 'configuration');
			$this->addPerm('modules', 		__('c_a_def_perm_config_modules'), 'configuration');
			$this->addPerm('themes', 		__('c_a_def_perm_config_themes'), 'configuration');
			$this->addPerm('themes_editor', __('c_a_def_perm_config_themes_editor'), 'configuration');
			$this->addPerm('navigation', 	__('c_a_def_perm_config_navigation'), 'configuration');
			$this->addPerm('permissions', 	__('c_a_def_perm_config_perms'), 'configuration');
			$this->addPerm('tools', 		__('c_a_def_perm_config_tools'), 'configuration');
			$this->addPerm('infos', 		__('c_a_def_perm_config_infos'), 'configuration');
	}

	/**
	 * Résolution de la route à utiliser
	 */
	protected function matchRequest()
	{
		# -- CORE TRIGGER : adminBeforeMatchRequest
		$this->triggers->callTrigger('adminBeforeMatchRequest');

		try {
			$this->request->attributes->add(
				$this->adminRouter->matchRequest($this->request)
			);
		}
		catch (ResourceNotFoundException $e) {
			$this->page->serve404();
		}
		catch (Exception $e) {
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			$this->response->setContent($e->getMessage());
		}
	}

	protected function callController()
	{
		# -- CORE TRIGGER : adminBeforeCallController
		$this->triggers->callTrigger('adminBeforeCallController');

		$this->response = $this->adminRouter->callController();

		if (null === $this->response || false === $this->response)
		{
			$this->response = new Response();
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->response->setContent('Unable to load controller.');
		}
	}

	protected function sendResponse()
	{
		# -- CORE TRIGGER : adminBeforePrepareResponse
		$this->triggers->callTrigger('adminBeforePrepareResponse');

		$this->response->prepare($this->request);

		# -- CORE TRIGGER : adminBeforeSendResponse
		$this->triggers->callTrigger('adminBeforeSendResponse');

		$this->response->send();
	}
}
