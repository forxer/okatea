<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install;

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Okatea\Install\Routing\Router;

use Tao\Admin\Page;
use Tao\Core\Application as BaseApplication;
use Tao\Core\ApplicationOptions;
use Tao\Core\Errors;
use Tao\Core\Localisation;
use Tao\Core\Session;
use Tao\Misc\FlashMessages;
use Tao\Core\Triggers;

class Application extends BaseApplication
{
	public $options;

	public $page;

	/**
	 * La requete en cours.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	public $request;

	/**
	 * Le contexte de le requete en cours.
	 *
	 * @var Symfony\Component\Routing\RequestContext
	 */
	public $requestContext;

	/**
	 * La réponse qui va être renvoyée.
	 *
	 * @var Symfony\Component\HttpFoundation\Response
	 */
	public $response;

	/**
	 * Le "stepper".
	 *
	 * @var Tao\Html\Stepper
	 */
	public $stepper;

	/**
	 * Le gestionnaire de déclencheurs.
	 *
	 * @var Tao\Core\Triggers
	 */
	public $triggers;

	public $version;

	public $oldVersion;

	public function __construct($autoloader, $sRootPath, array $aOptions = array())
	{
		# Autoloader shortcut
		$this->autoloader = $autoloader;

		$this->options = new ApplicationOptions($sRootPath, $aOptions);

		$this->start();

		$this->triggers = new Triggers($this);

		$this->httpFoundation();

		$this->router = new Router(
			$this,
			__DIR__.'/Routing/RouteProvider.php'
		);

		if ($this->session->has('okt_old_version')) {
			$this->oldVersion = $this->session->get('okt_old_version');
		}

		if ($this->request->query->has('old_version'))
		{
			$this->oldVersion = $this->request->query->get('old_version');
			$this->session->set('okt_old_version', $this->oldVersion);
		}

		# Initialisation localisation
		$this->aAvailablesLocales = array('fr','en');
		if (!$this->session->has('okt_install_language')) {
			$this->session->set('okt_install_language', $this->request->getPreferredLanguage($this->aAvailablesLocales));
		}

		$this->l10n = new Localisation($this->options->get('locales_dir'), $this->session->get('okt_install_language'), 'Europe/Paris');

		$this->l10n->loadFile(__DIR__.'/Locales/'.$this->session->get('okt_install_language').'/install');

		# Install or update ?
		if (!$this->session->has('okt_install_process_type'))
		{
			$this->session->set('okt_install_process_type', 'install');

			if (file_exists($this->options->get('config_dir').'/connexion.php')) {
				$this->session->set('okt_install_process_type', 'update');
			}
		}

		$this->loadPageHelpers();

		$this->matchRequest();

		# load page from stepper
		if ($this->session->get('okt_install_process_type') == 'install') {
			$this->stepper = new Stepper\Install($this->request->attributes->get('_route'));
		}
		else {
			$this->stepper = new Stepper\Update($this->request->attributes->get('_route'));
		}

		$this->loadTplEngine();

		$this->callController();

		$this->sendResponse();
	}

	public function getDb()
	{
		return $this->database();
	}

	/**
	 * Init content page helpers.
	 *
	 * @return Tao\Html\Page
	 */
	protected function loadPageHelpers()
	{
		$this->page = new Page($this, 'install');
	}

	/**
	 * Résolution de la route à utiliser
	 */
	protected function matchRequest()
	{
		# -- CORE TRIGGER : installBeforeMatchRequest
		$this->triggers->callTrigger('installBeforeMatchRequest');

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

	/**
	 * Load templates engine.
	 *
	 * return void
	 */
	protected function loadTplEngine()
	{
		# initialisation
		$this->tpl = new Templating($this, array(__DIR__.'/templates/%name%.php'));

		# assignation par défaut
		$this->tpl->addGlobal('okt', $this);
	}

	protected function callController()
	{
		# -- CORE TRIGGER : installBeforeCallController
		$this->triggers->callTrigger('installBeforeCallController');

		if ($this->router->callController() === false)
		{
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->response->setContent('Unable to load controller.');
		}
	}

	protected function sendResponse()
	{
		# -- CORE TRIGGER : installBeforePrepareResponse
		$this->triggers->callTrigger('installBeforePrepareResponse');

		$this->response->prepare($this->request);

		# -- CORE TRIGGER : installBeforeSendResponse
		$this->triggers->callTrigger('installBeforeSendResponse');

		$this->response->send();
	}
}
