<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\RteTinymce4\Admin\Controller;

use Okatea\Admin\Controller;

class Config extends Controller
{

	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('rte_tinymce_4_config'))
		{
			return $this->serve401();
		}
		
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->okt->module('RteTinymce4')->config->write(array(
				'width' => $this->okt['request']->request->get('p_width'),
				'height' => $this->okt['request']->request->get('p_height'),
				'content_css' => $this->okt['request']->request->get('p_content_css')
			));
			
			$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));
			
			$this->redirect($this->generateUrl('RteTinymce4_config'));
		}
		
		return $this->render('RteTinymce4/Admin/Templates/Config', []);
	}
}
