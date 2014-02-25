<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Okatea\Tao\Application;

use Okatea\Website\AdminBar;
use Okatea\Website\Page;

class Okatea extends Application
{
	/**
	 * L'utilitaire de contenu de page.
	 *
	 * @var Okatea\Website\Page
	 */
	public $page;

	/**
	 * Le theme.
	 *
	 * @var Okatea\Tao\Themes\Theme
	 */
	public $theme;

	/**
	 * L'identifiant tu theme à afficher.
	 *
	 * @var string
	 */
	public $theme_id;

	/**
	 * The admin bar for the website part.
	 *
	 * @var Okatea\Website\AdminBar
	 */
	public $websiteAdminBar;

	/**
	 * Run application.
	 *
	 */
	public function run()
	{
		parent::run();

		$this->theme_id = $this->getTheme();

		$this->loadPageHelpers();

		$this->loadThemes('public');

		$this->loadTheme();

		$this->loadTplEngine();

		if ($this->config->maintenance['public'] && !$this->user->is_superadmin) {
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
		$sOktTheme = $this->config->themes['desktop'];

		if ($this->session->has('okt_theme')) {
			$sOktTheme = $this->session->get('okt_theme');
		}
		elseif (!empty($this->config->themes['mobile']) || !empty($this->config->themes['tablet']))
		{
			$oMobileDetect = new \Mobile_Detect();
			$isMobile = $oMobileDetect->isMobile() && !empty($this->config->themes['mobile']);
			$isTablet = $oMobileDetect->isTablet() && !empty($this->config->themes['tablet']);

			if ($isMobile && !$isTablet) {
				$sOktTheme = $this->config->themes['mobile'];
			}
			elseif ($isTablet) {
				$sOktTheme = $this->config->themes['tablet'];
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
		$this->theme = $this->themes->getInstance($this->theme_id);
	}

	/**
	 * Load templates engine.
	 *
	 * return void
	 */
	protected function loadTplEngine()
	{
		$this->setTplDirectory($this->options->get('themes_dir').'/'.$this->theme_id.'/templates/%name%.php');
		$this->setTplDirectory($this->options->get('themes_dir').'/DefaultTheme/templates/%name%.php');

		# initialisation
		$this->tpl = new Templating($this, $this->aTplDirectories);

		# assignation par défaut
		$this->tpl->addGlobal('okt', $this);
	}

	/**
	 * Init content page helpers.
	 *
	 * @return \Okatea\Website\Page
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
		if (null === $this->websiteAdminBar && $this->user->is_superadmin || ($this->user->is_admin && $this->config->enable_admin_bar)) {
			$this->websiteAdminBar = new AdminBar($this);
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

		$this->response = $this->router->callController();

		if (null === $this->response || false === $this->response)
		{
			$this->response = new Response();
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->response->setContent('Unable to load controller '.$this->request->attributes->get('controller'));
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
