<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Controller;

use Okatea\Install\Controller;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;

class End extends Controller
{

	public function page()
	{
		# create .htaccess
		if (! file_exists($this->okt['app_path'] . '/.htaccess') && file_exists($this->okt['app_path'] . '/.htaccess.oktDist'))
		{
			copy($this->okt['app_path'] . '/.htaccess.oktDist', $this->okt['app_path'] . '/.htaccess');
		}

		# render HTML
		return $this->render('End', [
			'title' => __('i_end_' . $this->okt['session']->get('okt_install_process_type') . '_title'),
			'user' => $this->okt['session']->get('okt_install_sudo_user'),
			'password' => $this->okt['session']->get('okt_install_sudo_password')
		]);
	}
}
