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
		$this->okt->l10n->loadFile(__DIR__.'/Locales/'.$this->okt->session->get('okt_install_language').'/config');

		$this->okt->triggers->registerTrigger('installBeforeBuildInstallStepper', array($this, 'addStep'));
		$this->okt->triggers->registerTrigger('installBeforeLoadPageHelpers', array($this, 'addRoute'));
	}

	public function addStep($okt, $stepper)
	{
		$this->insertStepAfter($stepper, 'supa', array(
			'step' 		=> 'configuration',
			'title' 	=> __('i_step_config')
		));
	}

	public function addRoute($okt)
	{
		$okt->router->getRouteCollection()->add( 'configuration',
			new Route('/configuration', array('controller' => 'Okatea\Install\Extensions\Config\Controller::page'))
		);
	}
}