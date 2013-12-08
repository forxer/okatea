<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page d'ajout de thème.
 *
 * @addtogroup Okatea
 *
 */

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Forms\StaticFormElements as form;
use Tao\Themes\Collection as ThemesCollection;


# Accès direct interdit
if (!defined('OKT_THEMES_MANAGEMENT')) die;


/* Initialisations
----------------------------------------------------------*/

# Liste de thèmes des dépôts de thèmes
$aThemesRepositories = array();
if ($okt->config->themes_repositories_enabled)
{
	$aRepositories = $okt->config->themes_repositories;
	$aThemesRepositories = $oThemes->getRepositoriesInfos($aRepositories);
}

# Tri par ordre alphabétique des listes de thèmes des dépots
foreach ($aThemesRepositories as $repo_name=>$themes) {
	ThemesCollection::sortThemes($aThemesRepositories[$repo_name]);
}


/* Traitements
----------------------------------------------------------*/

# Theme upload
if ((!empty($_POST['upload_pkg']) && !empty($_FILES['pkg_file'])) ||
	(!empty($_POST['fetch_pkg']) && !empty($_POST['pkg_url'])) ||
	(!empty($_GET['repository']) && !empty($_GET['theme']) && $okt->config->themes_repositories_enabled))
{
	try
	{
		if (!empty($_POST['upload_pkg']))
		{
			util::uploadStatus($_FILES['pkg_file']);

			$dest = OKT_THEMES_PATH.'/'.$_FILES['pkg_file']['name'];
			if (!move_uploaded_file($_FILES['pkg_file']['tmp_name'],$dest)) {
				throw new Exception(__('Unable to move uploaded file.'));
			}
		}
		else
		{
			if (!empty($_GET['repository']) && !empty($_GET['theme']))
			{
				$repository = urldecode($_GET['repository']);
				$theme = urldecode($_GET['theme']);
				$url = urldecode($aThemesRepositories[$repository][$theme]['href']);
			}
			else {
				$url = urldecode($_POST['pkg_url']);
			}

			$dest = OKT_THEMES_PATH.'/'.basename($url);

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

		$ret_code = $oThemes->installPackage($dest, $oThemes);

		http::redirect('configuration.php?action=themes&added='.$ret_code);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Bootstrap a theme
else if (!empty($_POST['bootstrap']))
{
	try {
		$oThemes->bootstrapTheme($_POST['bootstrap_theme_name'], (!empty($_POST['bootstrap_theme_id']) ? $_POST['bootstrap_theme_id'] : null));
		http::redirect('configuration.php?action=themes&bootstraped=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# button set
$okt->page->setButtonset('themesBtSt',array(
	'id' => 'themes-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'configuration.php?action=themes&amp;do=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w'
		)
	)
));

# Tabs
$okt->page->tabs();

# strToSlug
$okt->page->strToSlug('#bootstrap_theme_name', '#bootstrap_theme_id');

# Infos page
$okt->page->addGlobalTitle(__('c_a_themes_management'), 'configuration.php?action=themes');
$okt->page->addGlobalTitle(__('c_a_themes_add'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

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

			<h5><?php echo html::escapeHTML($repo_name).' ('.ThemesCollection::pluralizethemesCount(count($aThemes)).')'; ?></h5>

			<table class="common">
				<caption><?php printf('c_a_themes_list_themes_available_%s', html::escapeHTML($repo_name)) ?></caption>
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
					<?php echo html::escapeHTML($aTheme['name']) ?>
					<?php echo !empty($aTheme['info']) ? '<br />'.html::escapeHTML($aTheme['info']) : ''; ?>
					</th>
					<td class="<?php echo $td_class; ?> center"><?php echo html::escapeHTML($aTheme['version']) ?></td>
					<td class="<?php echo $td_class; ?> center"><a href="configuration.php?action=themes&amp;do=add&amp;repository=<?php echo urlencode($repo_name) ?>&amp;theme=<?php echo urlencode($aTheme['id']) ?>" class="lazy-load"><?php _e('c_c_action_Add') ?></a></td>
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
			<form class="col" action="configuration.php" method="post">
				<fieldset>
					<legend><?php _e('c_a_themes_download_zip_file') ?></legend>
					<p class="field"><label for="pkg_url"><?php _e('c_a_themes_theme_zip_file_url') ?></label>
					<?php echo form::text('pkg_url',40,255) ?></p>
				</fieldset>

				<p><?php echo form::hidden(array('action'), 'themes') ?>
				<?php echo form::hidden(array('do'), 'add') ?>
				<?php echo form::hidden('fetch_pkg', 1) ?>
				<?php echo Page::formtoken() ?>
				<input type="submit" class="lazy-load" value="<?php _e('c_a_themes_download_theme') ?>" /></p>
			</form>

			<?php # zip file ?>
			<form class="col" action="configuration.php" method="post" enctype="multipart/form-data">
				<fieldset>
					<legend><?php _e('c_a_themes_upload_zip_file') ?></legend>
					<p class="field"><label for="pkg_file"><?php _e('c_a_themes_theme_zip_file') ?></label>
					<?php echo form::file('pkg_file')?></p>
				</fieldset>

				<p><?php echo form::hidden(array('action'), 'themes') ?>
				<?php echo form::hidden(array('do'), 'add') ?>
				<?php echo form::hidden('upload_pkg', 1) ?>
				<?php echo Page::formtoken() ?>
				<input type="submit" class="lazy-load" value="<?php _e('c_a_themes_upload_theme') ?>" /></p>
			</form>
		</div>
	</div><!-- #add_theme_zip -->

	<?php # Bootstrap theme ?>
	<div id="add_theme_bootstrap">
		<h3 id="add_theme_bootstrap_title"><?php _e('c_a_themes_bootstrap_title') ?></h3>

		<div id="add_theme_bootstrap_content">
			<p><?php _e('c_a_themes_bootstrap_feature_description') ?></p>

			<form action="configuration.php" method="post">

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_theme_name" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('c_a_themes_bootstrap_name'); ?></label>
					<?php echo form::text('bootstrap_theme_name', 60, 255, ''); ?></p>

					<p class="field col"><label for="bootstrap_theme_id" title="<?php _e('c_c_required_field') ?>"><?php _e('c_a_themes_bootstrap_id'); ?></label>
					<?php echo form::text('bootstrap_theme_id', 60, 255, ''); ?></p>
				</div>

				<p><?php echo form::hidden(array('action'), 'themes') ?>
				<?php echo form::hidden(array('do'), 'add') ?>
				<?php echo form::hidden('bootstrap', 1) ?>
				<?php echo Page::formtoken() ?>
				<input type="submit" value="<?php _e('c_a_themes_bootstrap_submit_value') ?>" /></p>
			</form>
		</div>
	</div><!-- #add_theme_bootstrap -->

</div><!-- #tabered -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
