<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Development\Admin\Controller;

use Okatea\Modules\Development\CountingFilesAndLines;
use Okatea\Admin\Controller;

class Counting extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('development_usage') || !$this->okt->checkPerm('development_counting')) {
			return $this->serve401();
		}

		$oCountig = null;

		if ($this->request->request->has('form_sent')) {
			$oCountig = new CountingFilesAndLines($this->okt->options->get('root_dir'));
		}

		return $this->render('Development/Admin/Templates/Counting', array(
			'oCountig' => $oCountig
		));
	}
}
