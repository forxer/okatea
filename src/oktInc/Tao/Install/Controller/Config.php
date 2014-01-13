<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Install\Controller;

use Tao\Install\Controller;

class Config extends Controller
{
	public function page()
	{
		return $this->render('Config', array(
			'title' => __('i_config_title'),

		));
	}
}
