<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Tao\Core\Application;
use Tao\Misc\PublicAdminBar;
use Tao\Website\Page;

class Okatea extends Application
{
	/**
	 * L'utilitaire de contenu de page.
	 *
	 * @var Tao\Html\Page
	 */
	public $page;

	/**
	 * Le theme.
	 *
	 * @var Tao\Themes\Theme
	 */
	public $theme;

	/**
	 * L'identifiant tu theme à afficher.
	 *
	 * @var string
	 */
	public $theme_id;

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

		$this->theme_id = $this->getTheme();

		$this->loadPageHelpers();

		$this->loadTheme();

		$this->loadTplEngine();

		if ($this->config->public_maintenance_mode && !$this->user->is_superadmin) {
			$this->page->serve503();
		}

		$this->loadModules('public');

		$this->loadAdminBar();

		$this->matchRequest();

		$this->callController();

		$this->sendResponse();
	}

	/**
	 * Return the theme id to use.
	 *
	 * @return string
	 */
	protected function getTheme()
	{
		$sOktTheme = $this->config->theme;

		if ($this->session->has('okt_theme')) {
			$sOktTheme = $this->session->get('okt_theme');
		}
		elseif (!empty($this->config->theme_mobile) || !empty($this->config->theme_tablet))
		{
			$oMobileDetect = new \Mobile_Detect();
			$isMobile = $oMobileDetect->isMobile() && !empty($this->config->theme_mobile);
			$isTablet = $oMobileDetect->isTablet() && !empty($this->config->theme_tablet);

			if ($isMobile && !$isTablet) {
				$sOktTheme = $this->config->theme_mobile;
			}
			elseif ($isTablet) {
				$sOktTheme = $this->config->theme_tablet;
			}

			$this->session->set('okt_theme', $sOktTheme);
		}

		return $sOktTheme;
	}

	/**
	 * Load public theme instance.
	 *
	 * @return void
	 */
	protected function loadTheme()
	{
		require $this->options->get('themes_dir').'/'.$this->theme_id.'/oktTheme.php';

		$this->theme = new \oktTheme($this);
	}

	/**
	 * Load templates engine.
	 *
	 * return void
	 */
	protected function loadTplEngine()
	{
		$this->setTplDirectory($this->theme->path.'/templates/%name%.php');
		$this->setTplDirectory($this->options->get('themes_dir').'/default/templates/%name%.php');

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

	/**
	 * Load public admin bar.
	 *
	 * @return void
	 */
	protected function loadAdminBar()
	{
		if ($this->user->is_superadmin || ($this->user->is_admin && $this->config->enable_admin_bar)) {
			$this->publicAdminBar = new PublicAdminBar($this);
		}
	}

	/**
	 * Résolution de la route à utiliser
	 */
	protected function matchRequest()
	{
		# -- CORE TRIGGER : publicBeforeMatchRequest
		$this->triggers->callTrigger('publicBeforeMatchRequest');

		try {
			$this->request->attributes->add(
				$this->router->matchRequest($this->request)
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
		# -- CORE TRIGGER : publicBeforeCallController
		$this->triggers->callTrigger('publicBeforeCallController');

		if ($this->router->callController() === false)
		{
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->response->setContent('Unable to load controller.');
		}
	}

	protected function sendResponse()
	{
		# -- CORE TRIGGER : publicBeforePrepareResponse
		$this->triggers->callTrigger('publicBeforePrepareResponse');

		$this->response->prepare($this->request);

		# -- CORE TRIGGER : publicBeforeSendResponse
		$this->triggers->callTrigger('publicBeforeSendResponse');

		$this->response->send();
	}
}
