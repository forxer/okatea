<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page d'outils pour les superadmin
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.tools');

# Données de la page
$aPageData = new ArrayObject();

require dirname(__FILE__).'/tools/cache/init.php';

require dirname(__FILE__).'/tools/cleanup/init.php';

require dirname(__FILE__).'/tools/backup/init.php';

require dirname(__FILE__).'/tools/htaccess/init.php';

# -- TRIGGER CORE TOOLS PAGE : adminToolsPageInit
$okt->triggers->callTrigger('adminToolsPageInit', $okt, $aPageData);


/* Traitements
----------------------------------------------------------*/

require dirname(__FILE__).'/tools/cache/actions.php';

require dirname(__FILE__).'/tools/cleanup/actions.php';

require dirname(__FILE__).'/tools/backup/actions.php';

require dirname(__FILE__).'/tools/htaccess/actions.php';

# -- TRIGGER CORE TOOLS PAGE : adminToolsPageProcessing
$okt->triggers->callTrigger('adminToolsPageProcessing', $okt, $aPageData);


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_tools'));

# js
$okt->page->tabs();


# Construction des onglets
$aPageData['tabs'] = new ArrayObject;

	# onglet cache
	$aPageData['tabs'][10] = array(
		'id' => 'tab-cache',
		'title' => __('c_a_tools_cache'),
		'content' => ''
	);

	ob_start();

	require dirname(__FILE__).'/tools/cache/display.php';

	$aPageData['tabs'][10]['content'] = ob_get_clean();


	# onglet cleanup
	$aPageData['tabs'][20] = array(
		'id' => 'tab-cleanup',
		'title' => __('c_a_tools_cleanup'),
		'content' => ''
	);

	ob_start();

	require dirname(__FILE__).'/tools/cleanup/display.php';

	$aPageData['tabs'][20]['content'] = ob_get_clean();


	# onglet backup
	$aPageData['tabs'][30] = array(
		'id' => 'tab-backup',
		'title' => __('c_a_tools_backup'),
		'content' => ''
	);

	ob_start();

	require dirname(__FILE__).'/tools/backup/display.php';

	$aPageData['tabs'][30]['content'] = ob_get_clean();


	# onglet htaccess
	$aPageData['tabs'][40] = array(
		'id' => 'tab-htaccess',
		'title' => __('c_a_tools_htaccess'),
		'content' => ''
	);

	ob_start();

	require dirname(__FILE__).'/tools/htaccess/display.php';

	$aPageData['tabs'][40]['content'] = ob_get_clean();



# -- TRIGGER CORE INFOS PAGE : adminInfosPageBuildTabs
$okt->triggers->callTrigger('adminInfosPageBuildTabs', $okt, $aPageData);

$aPageData['tabs']->ksort();


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

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

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
