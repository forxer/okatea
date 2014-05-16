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
		if (! file_exists($this->okt->options->get('root_dir') . '/.htaccess') && file_exists($this->okt->options->get('root_dir') . '/.htaccess.oktDist'))
		{
			copy($this->okt->options->get('root_dir') . '/.htaccess.oktDist', $this->okt->options->get('root_dir') . '/.htaccess');
		}
		
		# install default theme
		$this->okt->db = $this->okt->getDb();
		
		$this->okt->themes = new ThemesCollection($this->okt, $this->okt->options->get('themes_dir'));
		
		$oInstallTheme = $this->okt->themes->getInstaller('DefaultTheme');
		$oInstallTheme->doInstall();
		
		$this->okt->themes->getManager()->enableExtension('DefaultTheme');
		
		# render HTML
		return $this->render('End', [
			'title' => __('i_end_' . $this->session->get('okt_install_process_type') . '_title'),
			'user' => $this->session->get('okt_install_sudo_user'),
			'password' => $this->session->get('okt_install_sudo_password')
		]);
	}
}
