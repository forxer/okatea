<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Modules;

use Okatea\Install\AbstractExtension;
use Symfony\Component\Routing\Route;

class Extension extends AbstractExtension
{

	public function load()
	{
		$this->okt->l10n->loadFile(__DIR__ . '/Locales/%s/modules');

		$this->okt->triggers->registerTrigger('installBeforeBuildInstallStepper', array(
			$this,
			'addStep'
		));

		$this->okt->triggers->registerTrigger('installBeforeLoadPageHelpers', array(
			$this,
			'addRoute'
		));
	}

	public function addStep($stepper)
	{
		$this->insertStepAfter($stepper, 'supa', [
			'step' => 'modules',
			'title' => __('i_step_modules')
		]);
	}

	public function addRoute()
	{
		$this->okt->router->getRouteCollection()->add('modules', new Route('/modules', array(
			'controller' => 'Okatea\Install\Extensions\Modules\Controller::page'
		)));
	}
}
