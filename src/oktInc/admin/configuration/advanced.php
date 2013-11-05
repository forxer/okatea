<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de configuration avancée
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.advanced');

# Données de la page
$aPageData = new ArrayObject();
$aPageData['aNewConf'] = array();

# Inclusion des initialisations
require __DIR__.'/advanced/debug/init.php';
require __DIR__.'/advanced/dev/init.php';
require __DIR__.'/advanced/minify/init.php';
require __DIR__.'/advanced/others/init.php';
require __DIR__.'/advanced/path_url/init.php';
require __DIR__.'/advanced/repositories/init.php';
require __DIR__.'/advanced/update/init.php';

# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigPageInit
$okt->triggers->callTrigger('adminInfosPageInit', $okt, $aPageData);


/* Traitements
----------------------------------------------------------*/

# Inclusion des traitements
require __DIR__.'/advanced/debug/actions.php';
require __DIR__.'/advanced/dev/actions.php';
require __DIR__.'/advanced/minify/actions.php';
require __DIR__.'/advanced/others/actions.php';
require __DIR__.'/advanced/path_url/actions.php';
require __DIR__.'/advanced/repositories/actions.php';
require __DIR__.'/advanced/update/actions.php';

# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigPageProccessing
$okt->triggers->callTrigger('adminAdvancedConfigPageProccessing', $okt, $aPageData);

# enregistrement configuration
if (!empty($_POST['form_sent']) && $okt->error->isEmpty())
{
	try
	{
		$okt->config->write($aPageData['aNewConf']);

		$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

		$okt->redirect('configuration.php?action=advanced');
	}
	catch (InvalidArgumentException $e)
	{
		$okt->error->set(__('c_c_error_writing_configuration'));
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# infos page
$okt->page->addGlobalTitle(__('c_a_config_advanced'));

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Tabs
$okt->page->tabs();


# Construction des onglets
$aPageData['tabs'] = new ArrayObject;


	# onglet chemins et URL
	$aPageData['tabs'][10] = array(
		'id' => 'tab_path_url',
		'title' => __('c_a_config_advanced_tab_path_url'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/path_url/display.php';

	$aPageData['tabs'][10]['content'] = ob_get_clean();


	# onglet dépôts
	$aPageData['tabs'][20] = array(
		'id' => 'tab_repositories',
		'title' => __('c_a_config_advanced_tab_repositories'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/repositories/display.php';

	$aPageData['tabs'][20]['content'] = ob_get_clean();


	# onglet minify
	$aPageData['tabs'][30] = array(
		'id' => 'tab_minify',
		'title' => __('c_a_config_advanced_tab_minify'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/minify/display.php';

	$aPageData['tabs'][30]['content'] = ob_get_clean();


	# onglet debug
	$aPageData['tabs'][40] = array(
		'id' => 'tab_debug',
		'title' => __('c_a_config_advanced_tab_debug'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/debug/display.php';

	$aPageData['tabs'][40]['content'] = ob_get_clean();


	# onglet mises à jour
	$aPageData['tabs'][50] = array(
		'id' => 'tab_update',
		'title' => __('c_a_config_advanced_tab_update'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/update/display.php';

	$aPageData['tabs'][50]['content'] = ob_get_clean();


	# onglet développement
	$aPageData['tabs'][60] = array(
		'id' => 'tab_dev',
		'title' => __('c_a_config_advanced_tab_dev'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/dev/display.php';

	$aPageData['tabs'][60]['content'] = ob_get_clean();


	# onglet autres
	$aPageData['tabs'][70] = array(
		'id' => 'tab_others',
		'title' => __('c_a_config_advanced_tab_others'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/advanced/others/display.php';

	$aPageData['tabs'][70]['content'] = ob_get_clean();


# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminAdvancedConfigPageBuildTabs
$okt->triggers->callTrigger('adminAdvancedConfigPageBuildTabs', $okt, $aPageData);

$aPageData['tabs']->ksort();


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="configuration.php" method="post">
	<div id="tabered">
		<ul>
			<?php foreach ($aPageData['tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($aPageData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'advanced'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

