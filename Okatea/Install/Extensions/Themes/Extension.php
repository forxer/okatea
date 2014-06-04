<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Themes;

use Okatea\Install\AbstractExtension;
use Symfony\Component\Routing\Route;

class Extension extends AbstractExtension
{

	public function load()
	{
		$this->okt->l10n->loadFile(__DIR__ . '/Locales/%s/themes');

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
			'step' => 'themes',
			'title' => __('i_step_themes')
		]);
	}

	public function addRoute()
	{
		$this->okt->router->getRouteCollection()->add('themes', new Route('/themes', array(
			'controller' => 'Okatea\Install\Extensions\Themes\Controller::page'
		)));
	}
}
