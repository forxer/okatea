<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Install\Controller;

use Tao\Install\Controller;

class Theme extends Controller
{
	public function page()
	{
		return $this->render('Theme', array(
			'title' => __('i_theme_title'),

		));
	}
}
