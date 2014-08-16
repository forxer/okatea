<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Controller;

use Okatea\Tao\Requirements;
use Okatea\Install\Controller;

class Checks extends Controller
{
	public function page()
	{
		if ($this->okt['request']->request->has('sended')) {
			return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
		}

		$this->okt['l10nInstall']->loadFile($this->okt['locales_path'] . '/%s/pre-requisites');

		$oRequirements = new Requirements($this->okt, $this->okt['session']->get('okt_install_language'));

		$aResults = $oRequirements->getResultsFromHtmlCheckList();

		return $this->render('Checks', [
			'title' 			=> __('i_checks_title'),
			'pass_test' 		=> $aResults['bCheckAll'],
			'warning_empty' 	=> $aResults['bCheckWarning'],
			'requirements' 		=> $oRequirements->getRequirements()
		]);
	}
}
