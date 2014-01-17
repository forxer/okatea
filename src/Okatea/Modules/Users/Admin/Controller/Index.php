<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Users\Admin\Controller;

use Okatea\Admin\Controller;

class Index extends Controller
{
	public function page()
	{

		return $this->render('Users/Admin/Templates/Index', array(
		));
	}
}
