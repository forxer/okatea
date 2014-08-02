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
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Okatea\Admin\Page;
use Okatea\Install\Routing\Router;
use Okatea\Tao\Application;
use Okatea\Tao\ApplicationOptions;
use Okatea\Tao\Errors;
use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;
use Okatea\Tao\L10n\Localization;
use Okatea\Tao\Misc\FlashMessages;
use Okatea\Tao\Session;
use Okatea\Tao\Triggers;

class Okatea extends Application
{
	/**
	 * Tableau des codes de langues disponibles pour l'interface d'installation.
	 *
	 * @var array
	 */
	public $availablesLocales = [
		'fr',
		'en'
	];

	/**
	 * Le numéro de version que nous mettons à jour.
	 *
	 * @var string
	 */
	public $oldVersion;

	/**
	 * Le "stepper".
	 *
	 * @var Okatea\Tao\Html\Stepper
	 */
	public $stepper;

	/**
	 * Le numéro de version que nous installons.
	 *
	 * @var string
	 */
	public $version;

	public $extensions = array();

	public function run()
	{
		if ($this['session']->has('okt_old_version'))
		{
			$this->oldVersion = $this['session']->get('okt_old_version');
		}

		if ($this->okt['request']->query->has('old_version'))
		{
			$this->oldVersion = $this->okt['request']->query->get('old_version');
			$this['session']->set('okt_old_version', $this->oldVersion);
		}

		# Initialisation localisation
		if (! $this['session']->has('okt_install_language'))
		{
			$this['session']->set('okt_install_language', $this->okt['request']->getPreferredLanguage($this->availablesLocales));
		}

		$this->l10n = new Localization($this['session']->get('okt_install_language'), $this['session']->get('okt_install_language'), 'Europe/Paris');

		$this->l10n->loadFile($this['locales_dir'] . '/%s/main');
		$this->l10n->loadFile($this['locales_dir'] . '/%s/users');
		$this->l10n->loadFile(__DIR__ . '/Locales/%s/install');

		# Install or update ?
		if (! $this['session']->has('okt_install_process_type'))
		{
			$this['session']->set('okt_install_process_type', 'install');

			if (file_exists($this['config_dir'] . '/connection.php'))
			{
				$this['session']->set('okt_install_process_type', 'update');
			}
		}

		$this->loadExtensions();

		# -- CORE TRIGGER : installBeforeStartRouter
		$this['triggers']->callTrigger('installBeforeStartRouter');

		$this->router = new Router($this, __DIR__ . '/Routing/RouteProvider.php');

		# -- CORE TRIGGER : installBeforeLoadPageHelpers
		$this['triggers']->callTrigger('installBeforeLoadPageHelpers');

		$this->loadPageHelpers();

		# -- CORE TRIGGER : installBeforeMatchRequest
		$this['triggers']->callTrigger('installBeforeMatchRequest');

		$this->matchRequest();

		# -- CORE TRIGGER : installBeforeLoadStepper
		$this['triggers']->callTrigger('installBeforeLoadStepper');

		$this->loadStepper();

		# -- CORE TRIGGER : installBeforeLoadTplEngine
		$this['triggers']->callTrigger('installBeforeLoadTplEngine');

		$this->loadTplEngine();

		# -- CORE TRIGGER : installBeforeCallController
		$this['triggers']->callTrigger('installBeforeCallController');

		$this->callController();

		# -- CORE TRIGGER : installBeforePrepareResponse
		$this['triggers']->callTrigger('installBeforePrepareResponse');

		$this->response->prepare($this->okt['request']);

		# -- CORE TRIGGER : installBeforeSendResponse
		$this['triggers']->callTrigger('installBeforeSendResponse');

		$this->response->send();
	}

	/**
	 * Load install extensions.
	 */
	protected function loadExtensions()
	{
		$finder = (new Finder())
			->files()
			->in(__DIR__ . '/Extensions')
			->name('Extension.php');

		foreach ($finder as $file)
		{
			if (! file_exists(dirname($file->getRealPath()) . '/_disabled'))
			{
				$sExtensionId = $file->getRelativePath();

				$sExtensionClass = 'Okatea\\Install\\Extensions\\' . $sExtensionId . '\\Extension';

				$this->extensions[$sExtensionId] = new $sExtensionClass($this);
				$this->extensions[$sExtensionId]->load();
			}
		}
	}

	/**
	 * Init content page helpers.
	 *
	 * @return Okatea\Tao\Html\Page
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
		try
		{
			$this->okt['request']->attributes->add($this->router->matchRequest($this->okt['request']));
		}
		catch (ResourceNotFoundException $e)
		{
			$this->page->serve404();
		}
		catch (\Exception $e)
		{
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			$this->response->setContent($e->getMessage());
		}
	}

	/**
	 * Load stepper.
	 *
	 * return void
	 */
	protected function loadStepper()
	{
		if ($this['session']->get('okt_install_process_type') == 'install')
		{
			$this->stepper = new Stepper\Install($this, $this->okt['request']->attributes->get('_route'));
		}
		else
		{
			$this->stepper = new Stepper\Update($this, $this->okt['request']->attributes->get('_route'));
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
		$this->tpl = new Templating($this, [
			__DIR__ . '/Templates/%name%.php',
			__DIR__ . '/Extensions/%name%.php'
		]);

		# assignation par défaut
		$this->tpl->addGlobal('okt', $this);
	}

	protected function callController()
	{
		$this->response = $this->router->callController();

		if (null === $this->response || false === $this->response)
		{
			$this->response->headers->set('Content-Type', 'text/plain');
			$this->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->response->setContent('Unable to load controller.');
		}
	}
}
