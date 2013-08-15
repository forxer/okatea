<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration des modules (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# Affichage changelog
if (!empty($_GET['show_changelog']) && file_exists($okt->modules->path.'/'.$_GET['show_changelog'].'/CHANGELOG'))
{
	echo '<pre class="changelog">'.html::escapeHTML(file_get_contents($okt->modules->path.'/'.$_GET['show_changelog'].'/CHANGELOG')).'</pre>';
	die;
}

# Affichage dependance
if (!empty($_GET['show_dependance']) && file_exists($okt->modules->path.'/'.$_GET['show_dependance'].'/DEPENDANCE'))
{
	echo '<pre class="dependance">'.html::escapeHTML(file_get_contents($okt->modules->path.'/'.$_GET['show_dependance'].'/DEPENDANCE')).'</pre>';
	die;
}

# Affichage read me
if (!empty($_GET['show_readme']) && file_exists($okt->modules->path.'/'.$_GET['show_readme'].'/READ_ME'))
{
	echo '<pre class="changelog">'.html::escapeHTML(file_get_contents($okt->modules->path.'/'.$_GET['show_readme'].'/READ_ME')).'</pre>';
	die;
}

# Installation d'un module
if (!empty($_GET['install']) && array_key_exists($_GET['install'], $aUninstalledModules))
{
	@ini_set('memory_limit',-1);
	set_time_limit(0);

	$sInstallClassName = $okt->modules->getInstallClass($_GET['install']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['install']);
	$oInstallModule->doInstall();

	# activation du module
	$oInstallModule->checklist->addItem(
		'add_module_to_db',
		$okt->modules->enableModule($_GET['install']),
		'Enable module',
		'Cannot enable module'
	);

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_correctly_installed'));
	}
	else {
		$okt->error->set(__('c_a_modules_not_installed'));
	}

	# Vidange du cache
	util::deleteOktCacheFiles();

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_install_module_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->warning(array(
		'code' => 20,
		'message' => $_GET['install']
	));

	exit;
}


# Ré-installation d'un module
else if (!empty($_GET['reinstall']) && array_key_exists($_GET['reinstall'], $aInstalledModules))
{
	@ini_set('memory_limit',-1);
	set_time_limit(0);

	# il faut d'abord désactiver le module
	if ($aInstalledModules[$_GET['reinstall']]['status'])
	{
		$okt->modules->disableModule($_GET['reinstall']);

		# cache de la liste de module
		$okt->modules->generateCacheList();

		$okt->redirect('configuration.php?action=modules&reinstall='.$_GET['reinstall']);
	}

	$sInstallClassName = $okt->modules->getInstallClass($_GET['reinstall']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['reinstall']);

	# désinstallation
	$oInstallModule->doUninstall();

	# installation
	$oInstallModule->doInstall();

	# activation du module
	$oInstallModule->checklist->addItem(
		'add_module_to_db',
		$okt->modules->enableModule($_GET['reinstall']),
		'Enable module',
		'Cannot enable module'
	);

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_correctly_reinstalled'));
	}
	else {
		$okt->error->set(__('c_a_modules_not_correctly_reinstalled.'));
	}

	# vidange du cache
	util::deleteOktCacheFiles();

	$okt->page->addGlobalTitle(sprintf(__('Re-install %s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->critical(array(
		'code' => 23,
		'message' => $_GET['reinstall']
	));

	exit;
}


# Installation du jeu de test
else if (!empty($_GET['testset']) && array_key_exists($_GET['testset'], $aInstalledModules))
{
	@ini_set('memory_limit',-1);
	set_time_limit(0);

	$sInstallClassName = $okt->modules->getInstallClass($_GET['testset']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['testset']);

	# d'abord on vident le module
	$oInstallModule->doEmpty();

	# ensuite on installent les données par défaut
	$oInstallModule->doInstallDefaultData();

	# et ensuite on installent le jeu de test
	$oInstallModule->doInstallTestSet();

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_test_set_correctly_installed'));
	}
	else {
		$okt->error->set(__('c_a_modules_test_set_not_correctly_installed'));
	}

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_install_test_set_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->critical(array(
		'message' => 'install test set '.$_GET['testset']
	));

	exit;
}


# Installation des données par défaut
else if (!empty($_GET['defaultdata']) && array_key_exists($_GET['defaultdata'], $aInstalledModules))
{
	@ini_set('memory_limit',-1);
	set_time_limit(0);

	$sInstallClassName = $okt->modules->getInstallClass($_GET['defaultdata']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['defaultdata']);

	# on installent les données par défaut
	$oInstallModule->doInstallDefaultData();

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_test_set_correctly_installed'));
	}
	else {
		$okt->error->set(__('c_a_modules_test_set_not_correctly_installed'));
	}

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_install_default_data_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
			'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->warning(array(
		'message' => 'install default data '.$_GET['defaultdata']
	));

	exit;
}


# Vidage d'un module
else if (!empty($_GET['empty']) && array_key_exists($_GET['empty'], $aInstalledModules))
{
	@ini_set('memory_limit',-1);
	set_time_limit(0);

	$sInstallClassName = $okt->modules->getInstallClass($_GET['empty']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['empty']);
	$oInstallModule->doEmpty();

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_correctly_emptied'));
	}
	else {
		$okt->error->set(__('c_a_modules_not_correctly_emptied'));
	}

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_empty_module_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->critical(array(
		'message' => 'vidage '.$_GET['empty']
	));

	exit;
}


# Désinstallation d'un module
else if (!empty($_GET['uninstall']) && array_key_exists($_GET['uninstall'], $aInstalledModules))
{
	@ini_set('memory_limit',-1);
	set_time_limit(0);

	$sInstallClassName = $okt->modules->getInstallClass($_GET['uninstall']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['uninstall']);
	$oInstallModule->doUninstall();

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_correctly_uninstalled'));
	}
	else {
		$okt->error->set(__('c_a_modules_not_uninstalled'));
	}

	# vidange du cache
	util::deleteOktCacheFiles();

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_uninstall_module_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->critical(array(
		'code' => 22,
		'message' => $_GET['uninstall']
	));

	exit;
}


# Mise à jour d'un module
else if (!empty($_GET['update']) && array_key_exists($_GET['update'], $aInstalledModules))
{
	# D'abord on active le module
	if (!$okt->modules->moduleExists($_GET['update']))
	{
		$okt->modules->enableModule($_GET['update']);
		$okt->modules->generateCacheList();
		$okt->redirect('configuration.php?action=modules&update='.$_GET['update']);
	}

	# Ensuite on met à jour
	$sInstallClassName = $okt->modules->getInstallClass($_GET['update']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['update']);
	$oInstallModule->doUpdate();

	# Confirmations
	if ($oInstallModule->checklist->checkAll()) {
		$okt->page->messages->set(__('c_a_modules_correctly_updated'));
	}
	else {
		$okt->error->set(__('c_a_modules_not_updated'));
	}

	# cache de la liste de module
	$okt->modules->generateCacheList();

	# vidange du cache
	util::deleteOktCacheFiles();

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_update_module_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	if (file_exists($oInstallModule->root().'/_install/tpl/')
	|| file_exists($oInstallModule->root().'/_install/common/')
	|| file_exists($oInstallModule->root().'/_install/public/')) {
		$next_url = 'configuration.php?action=modules&amp;compare='.$oInstallModule->id();
	}
	else {
		$next_url = 'configuration.php?action=modules';
	}

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="'.$next_url.'">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	# log admin
	$okt->logAdmin->critical(array(
		'code' => 21,
		'message' => $_GET['update']
	));

	exit;
}


# Suppression d'un module
else if (!empty($_GET['delete']) && array_key_exists($_GET['delete'], $aUninstalledModules))
{
	if (files::deltree($okt->modules->path.'/'.$_GET['delete'])) {
		$okt->redirect('configuration.php?action=modules&deleted=1');
	}
	else {
		$okt->error->set(__('c_a_modules_not_deleted.'));
	}
}


# Activation d'un module
else if (!empty($_GET['enable']) && array_key_exists($_GET['enable'], $aInstalledModules))
{
	$okt->modules->enableModule($_GET['enable']);

	# vidange du cache
	util::deleteOktCacheFiles();

	# log admin
	$okt->logAdmin->warning(array(
		'code' => 30,
		'message' => $_GET['enable']
	));

	$okt->redirect('configuration.php?action=modules&enabled=1');
}


# Désactivation d'un module
else if (!empty($_GET['disable']) && array_key_exists($_GET['disable'], $aInstalledModules))
{
	$okt->modules->disableModule($_GET['disable']);

	# vidange du cache
	util::deleteOktCacheFiles();

	# log admin
	$okt->logAdmin->warning(array(
		'code' => 31,
		'message' => $_GET['disable']
	));

	$okt->redirect('configuration.php?action=modules&disabled=1');
}


# Package d'un module
else if (!empty($_GET['download']) && array_key_exists($_GET['download'], $aAllModules))
{
	$okt->modules->dowloadModule($_GET['download']);
}


# Replace templates
else if (!empty($_GET['templates']) && array_key_exists($_GET['templates'], $aInstalledModules))
{
	$sInstallClassName = $okt->modules->getInstallClass($_GET['templates']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['templates']);
	$oInstallModule->forceReplaceTpl();

	# cache de la liste de module
	$okt->modules->generateCacheList();

	$okt->redirect('configuration.php?action=modules&templates_replaced=1');
}


# Replace common
else if (!empty($_GET['common']) && array_key_exists($_GET['common'], $aInstalledModules))
{
	$sInstallClassName = $okt->modules->getInstallClass($_GET['common']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['common']);
	foreach (oktThemes::getThemes() as $sThemeId=>$sTheme) {
		$oInstallModule->forceReplaceAssets(OKT_THEMES_PATH.'/'.$sThemeId, oktThemes::getLockedFiles($sThemeId));
	}

	# cache de la liste de module
	$okt->modules->generateCacheList();

	$okt->redirect('configuration.php?action=modules&common_replaced=1');
}


# Replace public
else if (!empty($_GET['public']) && array_key_exists($_GET['public'], $aInstalledModules))
{
	$sInstallClassName = $okt->modules->getInstallClass($_GET['public']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['public']);
	$oInstallModule->forceReplacePublic();

	# cache de la liste de module
	$okt->modules->generateCacheList();

	$okt->redirect('configuration.php?action=modules&public_replaced=1');
}


# Compare files
else if (!empty($_GET['compare']) && array_key_exists($_GET['compare'], $aInstalledModules))
{

	$sInstallClassName = $okt->modules->getInstallClass($_GET['compare']);
	$oInstallModule = new $sInstallClassName($okt, OKT_MODULES_PATH, $_GET['compare']);
	$oInstallModule->compareFiles();

	$okt->page->addGlobalTitle(sprintf(__('c_a_modules_file_comparison_module_%s'),$oInstallModule->name()));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo $oInstallModule->checklist->getHTML();

	echo '<div class="checklistlegend">';
	echo '<p>'.__('c_c_checklist_legend').'</p>';
	echo $oInstallModule->checklist->getLegend();
	echo '</div>';

	echo '<p class="ui-helper-clearfix"><a class="button" '.
	'href="configuration.php?action=modules">'.__('Continue').'</a></p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;
	exit;
}


# Plugin upload
else if ((!empty($_POST['upload_pkg']) && !empty($_FILES['pkg_file'])) ||
	(!empty($_POST['fetch_pkg']) && !empty($_POST['pkg_url'])) ||
	(!empty($_GET['repository']) && !empty($_GET['module']) && $okt->config->modules_repositories_enabled))
{
	try
	{
		if (!empty($_POST['upload_pkg']))
		{
			util::uploadStatus($_FILES['pkg_file']);

			if (array_key_exists($_FILES['pkg_file']['name'], $aUninstalledModules)) {
				throw new Exception(__('c_a_modules_module_already_exists_not_installed_install_before_update'));
			}

			$dest = OKT_MODULES_PATH.'/'.$_FILES['pkg_file']['name'];
			if (!move_uploaded_file($_FILES['pkg_file']['tmp_name'],$dest)) {
				throw new Exception(__('Unable to move uploaded file.'));
			}
		}
		else
		{
			if (!empty($_GET['repository']) && !empty($_GET['module']))
			{
				$repository = urldecode($_GET['repository']);
				$module = urldecode($_GET['module']);
				$url = urldecode($aModulesRepositories[$repository][$module]['href']);
			}
			else {
				$url = urldecode($_POST['pkg_url']);
			}

			$dest = OKT_MODULES_PATH.'/'.basename($url);


			if (array_key_exists(basename($url), $aUninstalledModules)) {
				throw new Exception(__('c_a_modules_module_already_exists_not_installed_install_before_update'));
			}

			try
			{
				$client = netHttp::initClient($url,$path);
				$client->setUserAgent('Okatea');
				$client->useGzip(false);
				$client->setPersistReferers(false);
				$client->setOutput($dest);
				$client->get($path);
			}
			catch( Exception $e) {
				throw new Exception(__('An error occurred while downloading the file.'));
			}

			unset($client);
		}

		$ret_code = $okt->modules->installPackage($dest,$okt->modules);
		$okt->redirect('configuration.php?action=modules&added='.$ret_code);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}
