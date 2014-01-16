<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Admin\Pager;

class Logadmin extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# Locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.logadmin');

		# Filtres
		$this->okt->logAdmin->filtersStart();

		# Suppression automatique des logs
		$this->okt->logAdmin->deleteLogsDate($this->okt->config->log_admin['ttl_months']);

		# Suppression manuelle des logs
		if ($this->request->query->get('truncate'))
		{
			$this->okt->logAdmin->deleteLogs();

			$this->page->flash->success(__('c_a_config_logadmin_truncated'));

			return $this->redirect($this->generateUrl('config_logadmin'));
		}

		# Ré-initialisation filtres
		if ($this->request->query->get('init_filters'))
		{
			$this->okt->logAdmin->filters->initFilters();
			return $this->redirect($this->generateUrl('config_logadmin'));
		}

		# Initialisation des filtres
		$aParams = array();
		$this->okt->logAdmin->filters->setLogsParams($aParams);

		# Création des filtres
		$this->okt->logAdmin->filters->getFilters();

		# Initialisation de la pagination
		$oPager = new Pager($this->okt, $this->okt->logAdmin->filters->params->page, $this->okt->logAdmin->getLogs($aParams,true), $this->okt->logAdmin->filters->params->nb_per_page);
		$iNumPages = $oPager->getNbPages();
		$this->okt->logAdmin->filters->normalizePage($iNumPages);
		$aParams['limit'] = (($this->okt->logAdmin->filters->params->page-1)*$this->okt->logAdmin->filters->params->nb_per_page).','.$this->okt->logAdmin->filters->params->nb_per_page;

		# Récupération des logs
		$rsLogAdmin = $this->okt->logAdmin->getLogs($aParams);


		return $this->render('Config/Logadmin', array(
			'rsLogAdmin' => $rsLogAdmin,
			'oPager' => $oPager,
			'iNumPages' => $iNumPages
		));
	}
}
