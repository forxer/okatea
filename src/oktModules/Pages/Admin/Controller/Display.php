<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\Pages\Admin\Controller;

use Tao\Admin\Controller;

class Display extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('pages_usage')) {
			return $this->serve401();
		}


		return $this->render('Pages/Admin/Templates/Index', array(
		));
	}
}
