<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\DefaultsModules;

use Okatea\Install\AbstractExtension;
use Symfony\Component\Routing\Route;

class Extension extends AbstractExtension
{

	public function load()
	{
		$this->okt->triggers->registerTrigger('installBeforeLoadPageHelpers', array(
			$this,
			'addRoute'
		));
	}

	public function addRoute()
	{
		$this->okt->router->getRouteCollection()->add('defaults-modules', new Route('/defaults-modules', array(
			'controller' => 'Okatea\Install\Extensions\DefaultsModules\Controller::page'
		)));
	}
}
