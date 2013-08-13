<?php
/**
 * Page d'administration des modules (partie initialisations)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# Modules locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.modules');


# Récupération de la liste des modules dans le système de fichiers (tous les modules)
$aAllModules = $okt->modules->getModulesFromFileSystem();


# Load all modules admin locales files
foreach ($aAllModules as $id=>$infos) {
	l10n::set($infos['root'].'/locales/'.$okt->user->language.'/main');
}


# Récupération de la liste des modules dans la base de données (les modules installés)
$aInstalledModules = $okt->modules->getInstalledModules();

/*
$rsInstalledModules = $okt->modules->getModulesFromDB();

$aInstalledModules = array();
while ($rsInstalledModules->fetch())
{
	$sModuleRoot = $okt->modules->path.'/'.$rsInstalledModules->module_id.'/';

	$rsInstalledModules->setField('root',$sModuleRoot);

	$aInstalledModules[$rsInstalledModules->module_id] = array(
		'id' 			=> $rsInstalledModules->module_id,
		'root' 			=> $sModuleRoot,
		'name' 			=> $rsInstalledModules->module_name,
		'name_l10n' 	=> __($rsInstalledModules->module_name),
		'desc' 			=> $rsInstalledModules->module_description,
		'desc_l10n' 	=> __($rsInstalledModules->module_description),
		'author' 		=> $rsInstalledModules->module_author,
		'version' 		=> $rsInstalledModules->module_version,
		'priority' 		=> $rsInstalledModules->module_priority,
		'status' 		=> $rsInstalledModules->module_status,
		'updatable' 	=> $rsInstalledModules->module_updatable
	);
}
unset($rsInstalledModules);
*/

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
uasort($aInstalledModules, array('oktModules', 'sortModulesList'));
uasort($aUninstalledModules, array('oktModules', 'sortModulesList'));

foreach ($aModulesRepositories as $repo_name=>$modules) {
	uasort($aModulesRepositories[$repo_name], array('oktModules', 'sortModulesList'));
}
