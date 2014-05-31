<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\DefaultsModules;

use Okatea\Install\Controller as BaseController;

class Controller extends BaseController
{

	public function page()
	{
		$this->okt->startModules();

		# Install Pages module
		$oInstallModule = $this->okt->modules->getInstaller('Pages');
		$oInstallModule->doInstall();

		$this->okt->modules->getManager()->enableExtension('Pages');

		# Install Contact module
		$oInstallModule = $this->okt->modules->getInstaller('Contact');
		$oInstallModule->doInstall();

		$this->okt->modules->getManager()->enableExtension('Contact');

		return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
	}
}
