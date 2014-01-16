<?php
/**
 * Choix et installation des modules
 *
 * @addtogroup Okatea
 * @subpackage Install interface
 *
 */

use Okatea\Tao\Modules\Collection as ModulesCollection;
use Okatea\Tao\Core\HttpClient;

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

# Inclusion du prepend
require_once __DIR__.'/../../../oktInc/prepend.php';

# Locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.modules');

# Default modules
$aDefaultModules = array(
	'contact',
	'pages',
	'users'
);

# Récupération de la liste des modules dans le système de fichiers (tous les modules)
$aAllModules = $okt->modules->getModulesFromFileSystem();

# Load all modules admin locales files
foreach ($aAllModules as $sModuleId=>$aModuleInfos)
{
	l10n::set($aModuleInfos['root'].'/locales/'.$okt->user->language.'/main');
	$aAllModules[$sModuleId]['name_l10n'] = __($aModuleInfos['name']);
}

# Liste des dépôts de modules
$aModulesRepositories = array();
if ($okt->config->modules_repositories_enabled)
{
	$aRepositories = $okt->config->modules_repositories;
	$aModulesRepositories = $okt->modules->getRepositoriesInfos($aRepositories);
}

# Liste des modules déjà présents, à retirer de ceux dispo sur le dépot
foreach ($aModulesRepositories as $repo_name=>$modules)
{
	foreach ($modules as $module)
	{
		$aModulesRepositories[$repo_name][$module['id']]['name_l10n'] = $module['name'];

		if (isset($aAllModules[$module['id']])) {
	//		unset($aModulesRepositories[$repo_name][$module['id']]);
		}
	}
}

# Tri par ordre alphabétique des listes de modules
ModulesCollection::sortModules($aAllModules);

foreach ($aModulesRepositories as $repo_name=>$modules) {
	ModulesCollection::sortModules($aModulesRepositories[$repo_name]);
}


/* Traitements
------------------------------------------------------------*/

# formulaire envoyé
if (!empty($_POST['sended']))
{
	if (!empty($_POST['p_modules']) && is_array($_POST['p_modules']))
	{
		@ini_set('memory_limit',-1);
		set_time_limit(0);

		foreach ($_POST['p_modules'] as $sModuleId)
		{
			$sInstallClassName = $okt->modules->getInstallClass($sModuleId);
			$oInstallModule = new $sInstallClassName($okt, $okt->options->get('modules_dir'), $sModuleId);
			$oInstallModule->doInstall();
			$okt->modules->enableModule($sModuleId);
		}
	}

	Utilities::deleteOktCacheFiles();

	http::redirect('index.php?step='.$okt->stepper->getNextStep());
}

# Plugin upload
else if (!empty($_GET['repository']) && !empty($_GET['module']) && $okt->config->modules_repositories_enabled)
{
	try
	{
		$repository = urldecode($_GET['repository']);
		$module = urldecode($_GET['module']);
		$url = urldecode($aModulesRepositories[$repository][$module]['href']);
		$dest = $okt->options->get('modules_dir').'/'.basename($url);

		try
		{
			$client = Client('', array('request.options' => array('exceptions' => false)));

			$request = $client->get($url, array(), array(
				'save_to' => $dest
			));

			$request->send();
		}
		catch (Exception $e) {
			throw new Exception(__('An error occurred while downloading the file.'));
		}

		unset($client);

		$ret_code = $okt->modules->installPackage($dest,$okt->modules);
		http::redirect('index.php?step='.$okt->stepper->getCurrentStep());
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
------------------------------------------------------------*/

# Toggle With Legend
$oHtmlPage->toggleWithLegend('add_module_repo_title', 'add_module_repo_content');


# En-tête
$title = __('i_modules_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<form action="index.php" method="post">

	<ul id="modules_list_choice" class="checklist">
		<?php foreach ($aAllModules as $aModule) : ?>
		<li><label for="p_modules_<?php echo $aModule['id'] ?>"><?php echo form::checkbox(array('p_modules[]','p_modules_'.$aModule['id']), $aModule['id'], in_array($aModule['id'],$aDefaultModules)) ?> <?php _e($aModule['name']) ?></label></li>
		<?php endforeach; ?>
	</ul>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $okt->stepper->getCurrentStep() ?>" /></p>
</form>


<h4 id="add_module_repo_title"><?php _e('c_a_modules_add_module_from_remote_repository') ?></h4>

<div id="add_module_repo_content">
<?php if (!$okt->config->modules_repositories_enabled) : ?>
	<p><?php _e('c_a_modules_repositories_modules_disabled') ?></p>

<?php elseif (!empty($aModulesRepositories)) : ?>
	<?php foreach($aModulesRepositories as $repo_name=>$modules) : ?>

	<h5><?php echo html::escapeHTML($repo_name).' ('.oktModules::pluralizeModuleCount(count($modules)).')'; ?></h5>

	<table class="common">
		<caption><?php printf('c_a_modules_list_modules_available_%s', html::escapeHTML($repo_name)) ?></caption>
		<thead><tr>
			<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_modules_version') ?></th>
			<th scope="col" class="small"><?php _e('c_c_action_Add') ?></th>
		</tr></thead>
		<tbody>
		<?php $line_count = 0;
		foreach($modules as $module) :
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++; ?>
		<tr>
			<th scope="row" class="<?php echo $td_class; ?> fake-td">
			<?php echo html::escapeHTML($module['name']) ?>
			<?php echo !empty($module['info']) ? '<br />'.html::escapeHTML($module['info']) : ''; ?>
			</th>
			<td class="<?php echo $td_class; ?> center"><?php echo html::escapeHTML($module['version']) ?></td>
			<td class="<?php echo $td_class; ?> center"><a href="index.php?step=modules&amp;repository=<?php echo urlencode($repo_name) ?>&amp;module=<?php echo urlencode($module['id']) ?>" class="lazy-load"><?php _e('c_c_action_Add') ?></a></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endforeach; ?>
<?php else : ?>
	<p><?php _e('c_a_modules_no_repository_modules_defined') ?></p>
<?php endif; ?>
</div>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
