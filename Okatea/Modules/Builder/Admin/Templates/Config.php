<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# module title tag
$okt->page->addGlobalTitle(__('m_builder_menu'), $view->generateUrl('Builder_index'));

# tabs
$okt->page->tabs();

?>

<form action="<?php echo $view->generateUrl('Builder_config'); ?>" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general">Général</a></li>
			<li><a href="#tab_modules">Modules</a></li>
			<li><a href="#tab_themes">Thèmes</a></li>
		</ul>

		<div id="tab_general">
		</div><!-- #tab_general -->

		<div id="tab_modules">
			<h3>Modules</h3>

			<p>Les modules marqués "dépôt" seront ajoutés au dépôt de modules. Les modules "package" seront ajoutés au package Okatea.</p>

			<table class="common">
				<caption>Liste des modules</caption>
				<thead><tr>
					<th scope="col">Nom</th>
					<th scope="col">ID</th>
					<th scope="col">Version</th>
					<th scope="col">Dépôt</th>
					<th scope="col">Package</th>
				</tr></thead>
				<tbody>
				<?php foreach ($aModules as $sModuleId => $aModuleInfos) : ?>
				<tr>
					<th scope="row" class="fake-td"><?php echo $aModuleInfos['name_l10n'] ?></th>
					<td><?php echo $sModuleId ?></td>
					<td><?php echo $aModuleInfos['version'] ?></td>
					<td class="center small"><?php echo form::checkbox(array('modules_repository[]', 'modules_repository_'.$sModuleId), $sModuleId, in_array($sModuleId, $okt->module('Builder')->config->modules_repository)) ?></td>
					<td class="center small"><?php echo form::checkbox(array('modules_package[]', 'modules_package_'.$sModuleId), $sModuleId, in_array($sModuleId, $okt->module('Builder')->config->modules_package)) ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div><!-- #tab_modules -->

		<div id="tab_themes">
			<h3>Thèmes</h3>

			<p>Les thèmes marqués "dépôt" seront ajoutés au dépôt de thèmes. Les thèmes "package" seront ajoutés au package Okatea.</p>

			<table class="common">
				<caption>Liste des thèmes</caption>
				<thead><tr>
					<th scope="col">Nom</th>
					<th scope="col">ID</th>
					<th scope="col">Version</th>
					<th scope="col">Dépôt</th>
					<th scope="col">Package</th>
				</tr></thead>
				<tbody>
				<?php foreach ($aThemes as $sThemeId => $aThemeInfos) : ?>
				<tr>
					<th scope="row" class="fake-td"><?php echo $aThemeInfos['name_l10n'] ?></th>
					<td><?php echo $sThemeId ?></td>
					<td><?php echo $aThemeInfos['version'] ?></td>
					<td class="center small"><?php echo form::checkbox(array('themes_repository[]', 'themes_repository_'.$sThemeId), $sThemeId, in_array($sThemeId, $okt->module('Builder')->config->themes_repository)) ?></td>
					<td class="center small"><?php echo form::checkbox(array('themes_package[]', 'themes_package_'.$sThemeId), $sThemeId, in_array($sThemeId, $okt->module('Builder')->config->themes_package)) ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div><!-- #tab_themes -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('config_sent', 1) ?>
	<?php echo $okt->page->formtoken() ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

