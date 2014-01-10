<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Controller;

use Okatea\Install\Controller;

class Pages extends Controller
{
	public function page()
	{
		return $this->render('Pages', array(
			'title' => __('i_pages_title'),

		));
	}
}
