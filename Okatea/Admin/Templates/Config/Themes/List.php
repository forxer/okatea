<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

# Infos page par défaut
$okt->page->addGlobalTitle(__('c_a_themes_management'));

# Tabs
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');

?>

<div id="tabered">
	<ul>
		<li><a href="#tab-installed"><span><?php _e('c_a_themes_installed_themes') ?></span></a></li>
		<li><a href="#tab-uninstalled"><span><?php _e('c_a_themes_uninstalled_themes') ?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('c_a_themes_add_theme') ?></span></a></li>
		<?php # des themes à mettre à jour ?
		if (!empty($aUpdatablesThemes)) : ?>
		<li><a href="#tab-updates"><span><?php _e('c_a_themes_new_releases') ?></span></a></li>
		<?php endif; ?>
	</ul>

	<?php # render installed themes tab
	echo $view->render('Config/Themes/Tabs/installed', array(
		'aInstalledThemes' => $aInstalledThemes,
		'aAllThemes' => $aAllThemes
	)); ?>

	<?php # render uninstalled themes tab
	echo $view->render('Config/Themes/Tabs/uninstalled', array(
		'aUninstalledThemes' => $aUninstalledThemes
	)); ?>

	<?php # render add theme tab
	echo $view->render('Config/Themes/Tabs/add', array(
		'aUninstalledThemes' => $aUninstalledThemes
	)); ?>

	<?php # render updatables themes tab
	echo $view->render('Config/Themes/Tabs/updatables', array(
		'aUpdatablesThemes' => $aUpdatablesThemes
	)); ?>

</div><!-- #tabered -->
