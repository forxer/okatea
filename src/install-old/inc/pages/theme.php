<?php
/**
 * Choix du thème
 *
 * @addtogroup Okatea
 * @subpackage Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;

use Okatea\Admin\Page;
use Tao\Themes\Collection as ThemesCollection;
use Tao\Core\HttpClient;


/* Initialisations
------------------------------------------------------------*/

# Inclusion du prepend
require_once __DIR__.'/../../../oktInc/prepend.php';

# Locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.themes');

# Themes object
$oThemes = new ThemesCollection($okt, $okt->options->get('themes_dir'));

# Liste des thèmes présents
$aInstalledThemes = $oThemes->getThemesAdminList();

# Liste des dépôts de thèmes
$aThemesRepositories = array();
if ($okt->config->themes_repositories_enabled)
{
	$aRepositories = $okt->config->themes_repositories;
	$aThemesRepositories = $oThemes->getRepositoriesInfos($aRepositories);
}

# Tri par ordre alphabétique des listes de thème
ThemesCollection::sortThemes($aInstalledThemes);

foreach ($aThemesRepositories as $repo_name=>$themes) {
	ThemesCollection::sortThemes($aThemesRepositories[$repo_name]);
}

$p_theme = !empty($_REQUEST['p_theme']) && isset($aInstalledThemes[$_REQUEST['p_theme']]) ? $_REQUEST['p_theme'] : 'okatea';



/* Traitements
------------------------------------------------------------*/

# formulaire envoyé, on enregistre et on passent à l'étape suivante
if (!empty($_POST['sended']) && !empty($_POST['p_theme']) && isset($aInstalledThemes[$_POST['p_theme']]))
{
	try
	{
		$okt->config->write(array('theme'=>$_POST['p_theme']));

		$_SESSION['okt_install_theme'] = $_POST['p_theme'];

		http::redirect('index.php?step='.$okt->stepper->getNextStep());
	}
	catch (InvalidArgumentException $e)
	{
		$okt->error->set(__('c_c_error_writing_configuration'));
		$okt->error->set($e->getMessage());

	}
}

# Theme upload
else if ((!empty($_GET['repository']) && !empty($_GET['theme']) && $okt->config->themes_repositories_enabled))
{
	try
	{
		$repository = urldecode($_GET['repository']);
		$theme = urldecode($_GET['theme']);
		$url = urldecode($aThemesRepositories[$repository][$theme]['href']);

		$dest = $okt->options->get('themes_dir').'/'.basename($url);

		try
		{
			$client = new HttpClient();

			$request = $client->get($url, array(), array(
				'save_to' => $dest
			));

			$request->send();
		}
		catch (Exception $e) {
			throw new Exception(__('An error occurred while downloading the file.'));
		}

		unset($client);

		$ret_code = $oThemes->installPackage($dest, $oThemes);

		http::redirect('index.php?step=theme&p_theme='.$theme);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}
else if (!empty($_POST['bootstrap']))
{
	try {
		$theme = $oThemes->bootstrapTheme($_POST['bootstrap_theme_name'], (!empty($_POST['bootstrap_theme_id']) ? $_POST['bootstrap_theme_id'] : null));

		http::redirect('index.php?step='.$okt->stepper->getCurrentStep().'&p_theme='.$theme);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


# bootstrap a dedicated theme
//if (!isset($aInstalledThemes[$okt->config->domain])) {
//	$p_theme = $oThemes->bootstrapTheme($okt->config->domain);
//	http::redirect('index.php?step=theme&p_theme='.$p_theme);
//}


/* Affichage
------------------------------------------------------------*/


# Toggle With Legend
$oHtmlPage->toggleWithLegend('add_theme_repo_title', 'add_theme_repo_content');
$oHtmlPage->toggleWithLegend('add_theme_bootstrap_title', 'add_theme_bootstrap');

# strToSlug
$oHtmlPage->strToSlug('#bootstrap_theme_name', '#bootstrap_theme_id');


# En-tête
$title = __('i_theme_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<form action="index.php" method="post">
	<ul id="themes_list_choice" class="checklist">
		<?php foreach ($aInstalledThemes as $aTheme) : ?>
		<li><?php echo form::radio(array('p_theme','p_theme_'.$aTheme['id']), $aTheme['id'], ($p_theme == $aTheme['id'])) ?> <label for="p_theme_<?php echo $aTheme['id'] ?>"><?php echo $aTheme['name'] ?></label></li>
		<?php endforeach; ?>
	</ul>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $okt->stepper->getCurrentStep() ?>" /></p>
</form>


<h3 id="add_theme_repo_title"><?php _e('c_a_themes_add_theme_from_remote_repository') ?></h3>

<div id="add_theme_repo_content">

<?php if (!$okt->config->themes_repositories_enabled) : ?>
	<p><?php _e('c_a_themes_repositories_themes_disabled') ?></p>

<?php elseif (!empty($aThemesRepositories)) : ?>
	<?php foreach($aThemesRepositories as $repo_name=>$aThemes) : ?>

	<h4><?php echo html::escapeHTML($repo_name).' ('.ThemesCollection::pluralizethemesCount(count($aThemes)).')'; ?></h4>

	<table class="common">
		<caption><?php printf('c_a_themes_list_themes_available_%s', html::escapeHTML($repo_name)) ?></caption>
		<thead><tr>
			<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
			<th scope="col" class="small"><?php _e('c_c_action_Add') ?></th>
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
			<td class="<?php echo $td_class; ?> center"><a href="index.php?step=theme&amp;repository=<?php echo urlencode($repo_name) ?>&amp;theme=<?php echo urlencode($aTheme['id']) ?>" class="lazy-load"><?php _e('c_c_action_Add') ?></a></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endforeach; ?>
<?php else : ?>
	<p><?php _e('c_a_themes_no_repository_themes_defined') ?></p>
<?php endif; ?>
</div><!-- #add_theme_repo_content -->


<h3 id="add_theme_bootstrap_title"><?php _e('c_a_themes_bootstrap_title') ?></h3>

<div id="add_theme_bootstrap">

	<div id="add_theme_bootstrap_content">
		<p><?php _e('c_a_themes_bootstrap_feature_description') ?></p>

		<form action="index.php" method="post">

			<div class="two-cols">
				<p class="field col"><label for="bootstrap_theme_name" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('c_a_themes_bootstrap_name'); ?></label>
				<?php echo form::text('bootstrap_theme_name', 60, 255, ''); ?></p>

				<p class="field col"><label for="bootstrap_theme_id" title="<?php _e('c_c_required_field') ?>"><?php _e('c_a_themes_bootstrap_id'); ?></label>
				<?php echo form::text('bootstrap_theme_id', 60, 255, ''); ?></p>
			</div>

			<p><?php echo form::hidden(array('step'), $okt->stepper->getCurrentStep()) ?>
			<?php echo form::hidden('bootstrap', 1) ?>
			<?php echo Page::formtoken() ?>
			<input type="submit" value="<?php _e('c_a_themes_bootstrap_submit_value') ?>" /></p>
		</form>
	</div>
</div><!-- #add_theme_bootstrap -->

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
