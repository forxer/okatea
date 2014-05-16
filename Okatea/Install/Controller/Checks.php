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
		$oRequirements = new Requirements($this->okt, $this->session->get('okt_install_language'));
		
		$aResults = $oRequirements->getResultsFromHtmlCheckList();
		
		return $this->render('Checks', [
			'title' => __('i_checks_title'),
			'pass_test' => $aResults['bCheckAll'],
			'warning_empty' => $aResults['bCheckWarning'],
			'requirements' => $oRequirements->getRequirements()
		]);
	}
}
