<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\RteTinymce3\Admin\Controller;

use Okatea\Admin\Controller;

class Config extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('rte_tinymce_3_config')) {
			return $this->serve401();
		}

		if ($this->request->request->has('form_sent'))
		{
			try
			{
				$this->okt->module('RteTinymce3')->config->write(array(
					'width' 		=> $this->request->request->get('p_width'),
					'height' 		=> $this->request->request->get('p_height'),
					'content_css' 	=> $this->request->request->get('p_content_css')
				));

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				$this->redirect($this->generateUrl('RteTinymce3_config'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		return $this->render('RteTinymce3/Admin/Templates/Config', array(
		));
	}
}
