<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Routing\Helpers\Config;
use Okatea\Tao\Routing\Helpers\Website;

class Router extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('is_superadmin'))
		{
			return $this->serve401();
		}

		# Locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/router');

		# Informations sur les routes du site
		$oWebsiteHelpersConfig = new Website($this->okt, $this->okt['config_path'] . '/Routes', $this->okt['router']->getRouteCollection()->all());

		$aWebsiteRoutesInfos = $oWebsiteHelpersConfig->getRoutesInfos();

		# Informations sur les routes de l'adminbistration
		$oAdminHelpersConfig = new Config($this->okt, $this->okt['config_path'] . '/RoutesAdmin', $this->okt['adminRouter']->getRouteCollection()->all());

		$aAdminRoutesInfos = $oAdminHelpersConfig->getRoutesInfos();

		return $this->render('Config/Router', array(
			'aWebsiteRoutesInfos' => $aWebsiteRoutesInfos,
			'aAdminRoutesInfos' => $aAdminRoutesInfos
		));
	}
}
