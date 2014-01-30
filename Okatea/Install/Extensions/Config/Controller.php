<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Extensions\Config;

use Okatea\Install\Controller as BaseController;

class Controller extends BaseController
{
	public function page()
	{
		if ($this->request->request->has('sended'))
		{

			return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
		}

		return $this->render('Config/Template', array(
		));
	}
}
