<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Modules\Builder\Stepper;

class Builder extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('okatea_builder')) {
			return $this->serve401();
		}

		$stepper = new Stepper($this->generateUrl('Builder_index'), $this->request->query->get('step'));

		return $this->render('Builder/Admin/Templates/Builder', array(
			'stepper' => $stepper
		));
	}

}
