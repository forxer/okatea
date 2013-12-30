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
use Tao\Routing\AdminRouter;

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

	public $adminRouter;

	public function __construct($autoloader, $sRootPath, $sEnv = 'prod', $bDebug = false)
	{
		parent::__construct($autoloader, $sRootPath, $sEnv, $bDebug);

		$this->adminRouter = new AdminRouter($this, __DIR__.'/routes.php', $this->options->get('cache_dir').'/routing/admin', $bDebug);
	}

	/**
	 * Run application.
	 *
	 */
	public function run()
	{
		$this->loadPageHelpers();

		$this->loadLogAdmin();

		$this->defineAdminPerms();

		$this->matchRequest();

		$this->checkUser();

		$this->buildAdminMenu();

	//	$this->loadTheme();

		$this->loadTplEngine();

		$this->loadModules('admin');

		$this->callController();

		$this->sendResponse();
	}

	/**
	 * Load templates engine.
	 *
	 * return void
	 */
	protected function loadTplEngine()
	{
		$this->setTplDirectory(__DIR__.'/templates/%name%.php');

		# initialisation
		$this->tpl = new Templating($this, $this->aTplDirectories);

		# assignation par défaut
		$this->tpl->addGlobal('okt', $this);
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

	protected function loadLogAdmin()
	{
		$this->logAdmin = new LogAdmin($this);
	}

	protected function checkUser()
	{
		# Vérification de l'utilisateur en cours
		if ($this->request->attributes->get('_route') !== 'login')
		{
			# on stocke l'URL de la page dans un cookie
			$this->user->setAuthFromCookie($this->request->getUri());

			# si c'est un invité, rien à faire ici
			if ($this->user->is_guest)
			{
				$this->page->flash->warning(__('c_c_auth_not_logged_in'));

				return $this->response = new RedirectResponse($this->adminRouter->generate('login'));
			}

			# si il n'a pas la permission, il dégage
			elseif (!$this->checkPerm('usage'))
			{
				$this->user->logout();
				$this->page->flash->error(__('c_c_auth_restricted_access'));
				return $this->response = new RedirectResponse($this->adminRouter->generate('login'));
			}

			# enfin, si on est en maintenance, il faut être superadmin
			elseif ($this->config->admin_maintenance_mode && !$this->user->is_superadmin)
			{
				$this->user->logout();
				$this->page->flash->error(__('c_c_auth_admin_maintenance_mode'));
				return $this->response = new RedirectResponse($this->adminRouter->generate('login'));
			}
		}

		# Demande de déconnexion
		if (!empty($_REQUEST['logout']))
		{
			$this->user->setAuthFromCookie('');
			$this->user->logout();
			return $this->response = new RedirectResponse($this->adminRouter->generate('login'));
		}

		# Validation du CSRF token
		if (!defined('OKT_SKIP_CSRF_CONFIRM') && !empty($_POST) && (!isset($_POST['csrf_token']) || $this->user->csrf_token !== $_POST['csrf_token']))
		{
			$this->user->logout();
			$this->page->flash->error(__('c_c_auth_bad_csrf_token'));
			return $this->response = new RedirectResponse($this->adminRouter->generate('login'));
		}
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
				$this->page->configSubMenu->add(__('c_a_menu_localization'), 'configuration.php?action=languages',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'languages'),
					60,
					$this->checkPerm('languages')
				);
				$this->page->configSubMenu->add(__('c_a_menu_modules'), 'configuration.php?action=modules',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'modules'),
					70,
					$this->checkPerm('modules')
				);
				$this->page->configSubMenu->add(__('c_a_menu_themes'), 'configuration.php?action=themes',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'themes' || $this->page->action === 'theme'),
					80,
					$this->checkPerm('themes')
				);
				$this->page->configSubMenu->add(__('c_a_menu_navigation'), 'configuration.php?action=navigation',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'navigation' || $this->page->action === 'navigation'),
					90,
					$this->checkPerm('navigation')
				);
				$this->page->configSubMenu->add(__('c_a_menu_permissions'), 'configuration.php?action=permissions',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'permissions'),
					100,
					$this->checkPerm('permissions')
				);
				$this->page->configSubMenu->add(__('c_a_menu_tools'), 'configuration.php?action=tools',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'tools'),
					110,
					$this->checkPerm('tools')
				);
				$this->page->configSubMenu->add(__('c_a_menu_infos'), 'configuration.php?action=infos',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'infos'),
					120,
					$this->checkPerm('infos')
				);
				$this->page->configSubMenu->add(__('c_a_menu_update'), 'configuration.php?action=update',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'update'),
					130,
					$this->config->update_enabled && $this->checkPerm('is_superadmin')
				);
				$this->page->configSubMenu->add(__('c_a_menu_log_admin'), 'configuration.php?action=logadmin',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'logadmin'),
					140,
					$this->checkPerm('is_superadmin')
				);
				$this->page->configSubMenu->add(__('c_a_menu_router'), 'configuration.php?action=router',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'router'),
					150,
					$this->checkPerm('is_superadmin')
				);
				$this->page->configSubMenu->add(__('c_a_menu_advanced'), 'configuration.php?action=advanced',
					(OKT_FILENAME == 'configuration.php') && ($this->page->action === 'advanced'),
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
		$this->triggers->callTrigger('adminBeforeMatchRequest', $this);

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
		$this->triggers->callTrigger('adminBeforeCallController', $this);

		if ($this->adminRouter->callController() === false)
		{
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->response->setContent('Unable to load controller.');
		}
	}

	protected function sendResponse()
	{
		# -- CORE TRIGGER : adminBeforePrepareResponse
		$this->triggers->callTrigger('adminBeforePrepareResponse', $this);

		$this->response->prepare($this->request);

		# -- CORE TRIGGER : adminBeforeSendResponse
		$this->triggers->callTrigger('adminBeforeSendResponse', $this);

		$this->response->send();
	}
}
