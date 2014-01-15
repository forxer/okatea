<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\Users\Admin\Controller;

use Okatea\Admin\Controller;

class Groups extends Controller
{
	public function page()
	{

		return $this->render('Users/Admin/Templates/Groups', array(
		));
	}
}
