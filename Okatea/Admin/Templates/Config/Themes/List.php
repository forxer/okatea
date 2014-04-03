<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

$okt->page->addGlobalTitle(__('c_a_themes_management'), $view->generateUrl('config_themes')); 

# Tabs
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');

# CSS
$okt->page->css->addCss ( '
.no-icon {
	width: 64px;
	height: 64px;
	background: #f1f1f1;
	border: 1px solid #e1e1e1;
	text-align: center;
}
.no-icon em {
	position: relative;
	top: 45%;
	color: #999;
}
');
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
	echo $view->render('Config/Themes/ListTabs/installed', array(
		'aInstalledThemes' => $aInstalledThemes,
		'aAllThemes' => $aAllThemes
	)); ?>

	<?php # render uninstalled themes tab
	echo $view->render('Config/Themes/ListTabs/uninstalled', array(
		'aUninstalledThemes' => $aUninstalledThemes
	)); ?>

	<?php # render add theme tab
	echo $view->render('Config/Themes/ListTabs/add', array(
		'aThemesRepositories' => $aThemesRepositories
	)); ?>

	<?php # render updatables themes tab
	echo $view->render('Config/Themes/ListTabs/updatables', array(
		'aUpdatablesThemes' => $aUpdatablesThemes
	)); ?>

</div><!-- #tabered -->
