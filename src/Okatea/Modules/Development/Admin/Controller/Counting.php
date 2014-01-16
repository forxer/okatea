<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\Development\Admin\Controller;

use Okatea\Module\Development\CountingFilesAndLines;
use Okatea\Admin\Controller;

class Counting extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('m_development_perm_usage') || !$this->okt->checkPerm('m_development_perm_counting')) {
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
