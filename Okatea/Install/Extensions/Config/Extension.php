<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Config;

use Okatea\Install\AbstractExtension;
use Symfony\Component\Routing\Route;

class Extension extends AbstractExtension
{

	public function load()
	{
		$this->okt['l10nInstall']->loadFile(__DIR__ . '/Locales/%s/config');

		$this->okt['triggers']->registerTrigger('installBeforeBuildInstallStepper', array(
			$this,
			'insertStep'
		));

		$this->okt['triggers']->registerTrigger('installBeforeLoadPageHelpers', array(
			$this,
			'addRoute'
		));
	}

	public function insertStep($stepper)
	{
		$this->addStep($stepper, [
			'step' => 'configuration',
			'title' => __('i_step_config'),
			'position' => 310
		]);
	}

	public function addRoute()
	{
		$this->okt['router']->getRouteCollection()->add('configuration', new Route('/configuration', array(
			'controller' => 'Okatea\Install\Extensions\Config\Controller::page'
		)));
	}
}
