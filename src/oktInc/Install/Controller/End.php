<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Install\Controller;

use Okatea\Install\Controller;

class End extends Controller
{
	public function page()
	{

		return $this->render('End', array(
			'title' 	=> __('i_end_'.$this->session->get('okt_install_process_type').'_title'),
			'user' 		=> $this->session->get('okt_install_sudo_user'),
			'password' 	=> $this->session->get('okt_install_sudo_password')
		));
	}
}
