<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Development\Admin\Controller;

use Okatea\Admin\Controller;

class Debugbar extends Controller
{

	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('development_usage') || !$this->okt['visitor']->checkPerm('development_debug_bar'))
		{
			return $this->serve401();
		}
		
		if (!empty($_POST['form_sent']))
		{
			$p_admin = !empty($_POST['p_admin']) ? true : false;
			$p_public = !empty($_POST['p_public']) ? true : false;
			
			$p_tabs_super_globales = !empty($_POST['p_tabs_super_globales']) ? true : false;
			$p_tabs_app = !empty($_POST['p_tabs_app']) ? true : false;
			$p_tabs_db = !empty($_POST['p_tabs_db']) ? true : false;
			$p_tabs_tools = !empty($_POST['p_tabs_tools']) ? true : false;
			
			$p_holmes = !empty($_POST['p_holmes']) ? true : false;
			
			if (!$this->okt['flashMessages']->hasError())
			{
				$aNewConf = array(
					'debug_bar' => array(
						'admin' => (boolean) $p_admin,
						'public' => (boolean) $p_public,
						'tabs' => array(
							'super_globales' => (boolean) $p_tabs_super_globales,
							'app' => (boolean) $p_tabs_app,
							'db' => (boolean) $p_tabs_db,
							'tools' => (boolean) $p_tabs_tools
						),
						'holmes' => (boolean) $p_holmes
					)
				);
				
				$this->okt->module('Development')->config->write($aNewConf);
				
				$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));
				
				$this->redirect($this->generateUrl('Development_debugbar'));
			}
		}
		
		return $this->render('Development/Admin/Templates/Debugbar', []);
	}
}
