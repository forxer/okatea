<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Routing\ConfigHelpers;

class Router extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# Locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.router');

		$oConfigHelpers = new ConfigHelpers($this->okt, $this->okt->options->config_dir.'/routes');

		# Liste des routes chargées
		$aRouteInfos = $oConfigHelpers->getRoutesInfos();

		return $this->render('Config/Router', array(
			'aRouteInfos' => $aRouteInfos,
		));
	}
}