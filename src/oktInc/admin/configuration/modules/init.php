<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration des modules (partie initialisations)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Modules\Collection as ModulesCollection;

# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# Modules locales
$okt->l10n->loadFile(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.modules');


# Récupération de la liste des modules dans le système de fichiers (tous les modules)
$aAllModules = $okt->modules->getModulesFromFileSystem();


# Load all modules admin locales files
foreach ($aAllModules as $id=>$infos) {
	$okt->l10n->loadFile($infos['root'].'/locales/'.$okt->user->language.'/main');
}


# Récupération de la liste des modules dans la base de données (les modules installés)
$aInstalledModules = $okt->modules->getInstalledModules();

$iNumInstalledModules = count($aInstalledModules);


# Calcul de la liste des modules non-installés
$aUninstalledModules = array_diff_key($aAllModules,$aInstalledModules);

foreach ($aUninstalledModules as $sModuleId=>$aModuleInfos) {
	$aUninstalledModules[$sModuleId]['name_l10n'] = __($aModuleInfos['name']);
}


# Liste des dépôts de modules
$aModulesRepositories = array();
if ($okt->config->modules_repositories_enabled)
{
	$aRepositories = $okt->config->modules_repositories;
	$aModulesRepositories = $okt->modules->getRepositoriesInfos($aRepositories);
}


# Liste des éventuelles mise à jours disponibles sur les dépots
$aUpdatables = array();
foreach ($aModulesRepositories as $repo_name=>$modules)
{
	foreach ($modules as $module)
	{
		$aModulesRepositories[$repo_name][$module['id']]['name_l10n'] = $module['name'];

		if (isset($aAllModules[$module['id']]) && $aAllModules[$module['id']]['updatable'] && version_compare($aAllModules[$module['id']]['version'],$module['version'], '<'))
		{
			$updatables[$module['id']] = array(
				'id' => $module['id'],
				'name' => $module['name'],
				'version' => $module['version'],
				'info' => $module['info'],
				'repository' => $repo_name
			);
		}
	}
}


# Tri par ordre alphabétique des listes de modules
ModulesCollection::sortModules($aInstalledModules);
ModulesCollection::sortModules($aUninstalledModules);

foreach ($aModulesRepositories as $repo_name=>$modules) {
	ModulesCollection::sortModules($aModulesRepositories[$repo_name]);
}
