<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Install\Controller;

use Tao\Install\Controller;

class Modules extends Controller
{
	public function page()
	{
		return $this->render('Modules', array(
			'title' => __('i_modules_title'),

		));
	}
}
