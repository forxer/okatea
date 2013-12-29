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
	 * Run application.
	 *
	 */
	public function run()
	{
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
	 * Load templates engine.
	 *
	 * return void
	 */
	protected function loadTplEngine()
	{
		$this->setTplDirectory($this->theme->path.'/templates/%name%.php');
		$this->setTplDirectory($this->options->get('themes_dir').'/default/templates/%name%.php');

		$this->tpl = $this->getTplEngine();
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
		$this->triggers->callTrigger('publicBeforeMatchRequest', $this);

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
		$this->triggers->callTrigger('publicBeforeCallController', $this);

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
		$this->triggers->callTrigger('publicBeforePrepareResponse', $this);

		$this->response->prepare($this->request);

		# -- CORE TRIGGER : publicBeforeSendResponse
		$this->triggers->callTrigger('publicBeforeSendResponse', $this);

		$this->response->send();
	}
}
