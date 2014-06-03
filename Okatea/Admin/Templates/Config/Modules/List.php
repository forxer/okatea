<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

$okt->page->addGlobalTitle(__('c_a_modules_management'), $view->generateUrl('config_modules'));

# Tabs
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');

?>

<div id="tabered">
	<ul>
		<li><a href="#tab-installed"><span><?php _e('c_a_modules_installed_modules') ?></span></a></li>
		<li><a href="#tab-uninstalled"><span><?php _e('c_a_modules_uninstalled_modules') ?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('c_a_modules_add_module') ?></span></a></li>
		<?php # des modules à mettre à jour ?
		if (! empty($aUpdatablesModules)) : ?>
		<li><a href="#tab-updates"><span><?php _e('c_a_modules_new_releases') ?></span></a></li>
		<?php endif; ?>
	</ul>

	<?php # render installed modules tab
	echo $view->render('Config/Modules/ListTabs/installed', array(
		'aInstalledModules' => $aInstalledModules,
		'aAllModules' => $aAllModules
	)); ?>

	<?php # render uninstalled modules tab
	echo $view->render('Config/Modules/ListTabs/uninstalled', array(
		'aUninstalledModules' => $aUninstalledModules
	)); ?>

	<?php # render add module tab
	echo $view->render('Config/Modules/ListTabs/add', array(
		'aModulesRepositories' => $aModulesRepositories
	)); ?>

	<?php # render updatables modules tab
	echo $view->render('Config/Modules/ListTabs/updatables', array(
		'aUpdatablesModules' => $aUpdatablesModules
	)); ?>

</div>
<!-- #tabered -->
