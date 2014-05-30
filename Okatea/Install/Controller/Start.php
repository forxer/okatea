<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Controller;

use Okatea\Install\Controller;

class Start extends Controller
{

	public function page()
	{
		$sSwitchLanguage = $this->request->query->get('switch_language');

		if ($sSwitchLanguage && in_array($sSwitchLanguage, $this->okt->availablesLocales))
		{
			$this->session->set('okt_install_language', $sSwitchLanguage);

			return $this->redirect($this->generateUrl('start'));
		}

		return $this->render('Start', []

		);
	}
}
