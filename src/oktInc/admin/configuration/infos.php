<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page d'information pour les superadmin
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


/* Initialisations
----------------------------------------------------------*/

# locales
$okt->l10n->loadFile($okt->options->locales_dir.'/'.$okt->user->language.'/admin.infos');

# Données de la page
$aPageData = new ArrayObject();

require __DIR__.'/infos/notes/init.php';

require __DIR__.'/infos/okatea/init.php';

require __DIR__.'/infos/mysql/init.php';

require __DIR__.'/infos/php/init.php';

# -- TRIGGER CORE INFOS PAGE : adminInfosPageInit
$okt->triggers->callTrigger('adminInfosPageInit', $okt, $aPageData);


/* Traitements
----------------------------------------------------------*/

require __DIR__.'/infos/notes/actions.php';

require __DIR__.'/infos/okatea/actions.php';

require __DIR__.'/infos/php/actions.php';

require __DIR__.'/infos/mysql/actions.php';

# -- TRIGGER CORE INFOS PAGE : adminInfosPageProcessing
$okt->triggers->callTrigger('adminInfosPageProcessing', $okt, $aPageData);


/* Affichage
----------------------------------------------------------*/

$okt->page->openLinkInDialog('#changelog_link',array(
	'title' => 'CHANGELOG',
	'width' => 730,
	'height' => 500
));


# Titre de la page
$okt->page->addGlobalTitle(__('c_a_infos'));

# js
$okt->page->tabs();


# Construction des onglets
$aPageData['tabs'] = new ArrayObject;

	# onglet notes
	$aPageData['tabs'][10] = array(
		'id' => 'tab-notes',
		'title' => __('c_a_infos_install_notes'),
		'content' => ''
	);

	ob_start();

		require __DIR__.'/infos/notes/display.php';

	$aPageData['tabs'][10]['content'] = ob_get_clean();


	# onglet okatea
	$aPageData['tabs'][20] = array(
		'id' => 'tab-okatea',
		'title' => __('c_a_infos_okatea'),
		'content' => ''
	);

	ob_start();

		require __DIR__.'/infos/okatea/display.php';

	$aPageData['tabs'][20]['content'] = ob_get_clean();


	# onglet php
	$aPageData['tabs'][30] = array(
		'id' => 'tab-php',
		'title' => __('c_a_infos_php'),
		'content' => ''
	);

	ob_start();

		require __DIR__.'/infos/php/display.php';

	$aPageData['tabs'][30]['content'] = ob_get_clean();


	# onglet mysql
	$aPageData['tabs'][40] = array(
		'id' => 'tab-mysql',
		'title' => __('c_a_infos_mysql'),
		'content' => ''
	);

	ob_start();

		require __DIR__.'/infos/mysql/display.php';

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
