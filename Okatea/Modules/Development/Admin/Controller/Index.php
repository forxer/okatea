<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Development\Admin\Controller;

use Okatea\Admin\Controller;

class Index extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('development_usage'))
		{
			return $this->serve401();
		}
		
		return $this->render('Development/Admin/Templates/Index', array());
	}
}
