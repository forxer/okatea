<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de configuration du site
 *
 * @addtogroup Okatea
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Locales
$okt->l10n->loadFile(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.site');

# Données de la page
$aPageData = new ArrayObject();
$aPageData['aNewConf'] = array();

# Inclusion des initialisations
require __DIR__.'/site/general/init.php';
require __DIR__.'/site/company/init.php';
require __DIR__.'/site/emails/init.php';
require __DIR__.'/site/seo/init.php';

# -- TRIGGER CORE CONFIG SITE PAGE : adminConfigSitePageInit
$okt->triggers->callTrigger('adminConfigSitePageInit', $okt, $aPageData);


/* Traitements
----------------------------------------------------------*/

# Inclusion des traitements
require __DIR__.'/site/general/actions.php';
require __DIR__.'/site/company/actions.php';
require __DIR__.'/site/emails/actions.php';
require __DIR__.'/site/seo/actions.php';

# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminConfigSitePageProccessing
$okt->triggers->callTrigger('adminConfigSitePageProccessing', $okt, $aPageData);

# enregistrement configuration
if (!empty($_POST['form_sent']) && $okt->error->isEmpty())
{
	try
	{
		$okt->config->write($aPageData['aNewConf']);

		$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

		http::redirect('configuration.php?action=site');
	}
	catch (InvalidArgumentException $e)
	{
		$okt->error->set(__('c_c_error_writing_configuration'));
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_site'));

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Tabs
$okt->page->tabs();


# Construction des onglets
$aPageData['tabs'] = new ArrayObject;


	# onglet général
	$aPageData['tabs'][10] = array(
		'id' => 'tab_general',
		'title' => __('c_a_config_tab_general'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/site/general/display.php';

	$aPageData['tabs'][10]['content'] = ob_get_clean();


	# onglet société
	$aPageData['tabs'][20] = array(
		'id' => 'tab_company',
		'title' => __('c_a_config_tab_company'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/site/company/display.php';

	$aPageData['tabs'][20]['content'] = ob_get_clean();


	# onglet emails
	$aPageData['tabs'][30] = array(
		'id' => 'tab_email',
		'title' => __('c_a_config_tab_email'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/site/emails/display.php';

	$aPageData['tabs'][30]['content'] = ob_get_clean();


	# onglet seo
	$aPageData['tabs'][40] = array(
		'id' => 'tab_seo',
		'title' => __('c_a_config_tab_seo'),
		'content' => ''
	);

	ob_start();

	require __DIR__.'/site/seo/display.php';

	$aPageData['tabs'][40]['content'] = ob_get_clean();


# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminConfigSitePageBuildTabs
$okt->triggers->callTrigger('adminConfigSitePageBuildTabs', $okt, $aPageData);

$aPageData['tabs']->ksort();


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form id="config-site-form" action="configuration.php" method="post">
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

	<p><?php echo form::hidden(array('form_sent'), 1) ?>
	<?php echo form::hidden(array('action'), 'site') ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
