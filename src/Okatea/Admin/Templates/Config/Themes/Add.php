<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Themes\Collection as ThemesCollection;

$view->extend('layout');

# infos page
$okt->page->addGlobalTitle(__('c_a_themes_management'), $view->generateUrl('config_themes'));
$okt->page->addGlobalTitle(__('c_a_themes_add'));

# button set
$okt->page->setButtonset('themesBtSt',array(
	'id' => 'themes-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> $view->generateUrl('config_themes'),
			'ui-icon' 		=> 'arrowreturnthick-1-w'
		)
	)
));

# Tabs
$okt->page->tabs();

# strToSlug
$okt->page->strToSlug('#bootstrap_theme_name', '#bootstrap_theme_id');

?>

<?php echo $okt->page->getButtonSet('themesBtSt'); ?>

<div id="tabered">
	<ul>
		<li><a href="#add_theme_repo"><span><?php _e('c_a_themes_add_repo') ?></span></a></li>
		<li><a href="#add_theme_zip"><span><?php _e('c_a_themes_add_archive') ?></span></a></li>
		<li><a href="#add_theme_bootstrap"><span><?php _e('c_a_themes_add_bootstrap') ?></span></a></li>
	</ul>

	<?php # Remote repository ?>
	<div id="add_theme_repo">
		<h3 id="add_theme_repo_title"><?php _e('c_a_themes_add_theme_from_remote_repository') ?></h3>

		<div id="add_theme_repo_content">
		<?php if (!$okt->config->themes_repositories_enabled) : ?>
			<p><?php _e('c_a_themes_repositories_themes_disabled') ?></p>

		<?php elseif (!empty($aThemesRepositories)) : ?>
			<?php foreach($aThemesRepositories as $repo_name=>$aThemes) : ?>

			<h5><?php echo $view->escape($repo_name).' ('.ThemesCollection::pluralizethemesCount(count($aThemes)).')'; ?></h5>

			<table class="common">
				<caption><?php printf('c_a_themes_list_themes_available_%s', $view->escape($repo_name)) ?></caption>
				<thead><tr>
					<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
					<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
					<th scope="col" class="small"><?php _e('c_c_action_Add') ?></th>
					<th scope="col" class="small"><?php _e('c_c_action_Download') ?></th>
				</tr></thead>
				<tbody>
				<?php $line_count = 0;
				foreach ($aThemes as $aTheme) :
					$td_class = $line_count%2 == 0 ? 'even' : 'odd';
					$line_count++; ?>
				<tr>
					<th scope="row" class="<?php echo $td_class; ?> fake-td">
					<?php echo $view->escape($aTheme['name']) ?>
					<?php echo !empty($aTheme['info']) ? '<br />'.$view->escape($aTheme['info']) : ''; ?>
					</th>
					<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($aTheme['version']) ?></td>
					<td class="<?php echo $td_class; ?> center"><a href="<?php echo $view->generateUrl('config_theme_add') ?>?repository=<?php echo urlencode($repo_name) ?>&amp;theme=<?php echo urlencode($aTheme['id']) ?>" class="lazy-load"><?php _e('c_c_action_Add') ?></a></td>
					<td class="<?php echo $td_class; ?> center"><a href="<?php echo $aTheme['href'] ?>"><?php _e('c_c_action_Download') ?></a></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endforeach; ?>
		<?php else : ?>
			<p><?php _e('c_a_themes_no_repository_themes_defined') ?></p>
		<?php endif; ?>
		</div>
	</div><!-- #add_theme_repo -->

	<?php # From zip ?>
	<div id="add_theme_zip">
		<h3 id="add_theme_zip_title"><?php _e('c_a_themes_add_theme_from_zip') ?></h3>

		<div id="add_theme_zip_content" class="two-cols">

			<?php # zip URL ?>
			<form class="col" action="<?php echo $view->generateUrl('config_theme_add') ?>" method="post">
				<fieldset>
					<legend><?php _e('c_a_themes_download_zip_file') ?></legend>
					<p class="field"><label for="pkg_url"><?php _e('c_a_themes_theme_zip_file_url') ?></label>
					<?php echo form::text('pkg_url',40,255) ?></p>
				</fieldset>

				<p><?php echo form::hidden(array('do'), 'add') ?>
				<?php echo form::hidden('fetch_pkg', 1) ?>
				<?php echo $okt->page->formtoken() ?>
				<input type="submit" class="lazy-load" value="<?php _e('c_a_themes_download_theme') ?>" /></p>
			</form>

			<?php # zip file ?>
			<form class="col" action="<?php echo $view->generateUrl('config_theme_add') ?>" method="post" enctype="multipart/form-data">
				<fieldset>
					<legend><?php _e('c_a_themes_upload_zip_file') ?></legend>
					<p class="field"><label for="pkg_file"><?php _e('c_a_themes_theme_zip_file') ?></label>
					<?php echo form::file('pkg_file')?></p>
				</fieldset>

				<p><?php echo form::hidden('upload_pkg', 1) ?>
				<?php echo $okt->page->formtoken() ?>
				<input type="submit" class="lazy-load" value="<?php _e('c_a_themes_upload_theme') ?>" /></p>
			</form>
		</div>
	</div><!-- #add_theme_zip -->

	<?php # Bootstrap theme ?>
	<div id="add_theme_bootstrap">
		<h3 id="add_theme_bootstrap_title"><?php _e('c_a_themes_bootstrap_title') ?></h3>

		<div id="add_theme_bootstrap_content">
			<p><?php _e('c_a_themes_bootstrap_feature_description') ?></p>

			<form action="<?php echo $view->generateUrl('config_theme_add') ?>" method="post">

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_theme_name" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('c_a_themes_bootstrap_name'); ?></label>
					<?php echo form::text('bootstrap_theme_name', 60, 255); ?></p>

					<p class="field col"><label for="bootstrap_theme_id" title="<?php _e('c_c_required_field') ?>"><?php _e('c_a_themes_bootstrap_id'); ?></label>
					<?php echo form::text('bootstrap_theme_id', 60, 255); ?></p>
				</div>

				<p><?php echo form::hidden('bootstrap', 1) ?>
				<?php echo $okt->page->formtoken() ?>
				<input type="submit" value="<?php _e('c_a_themes_bootstrap_submit_value') ?>" /></p>
			</form>
		</div>
	</div><!-- #add_theme_bootstrap -->

</div><!-- #tabered -->
