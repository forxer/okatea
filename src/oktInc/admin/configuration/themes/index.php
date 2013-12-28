<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de gestion de la liste des thèmes.
 *
 * @addtogroup Okatea
 *
 */

use Tao\Admin\Page;
use Tao\Admin\Pager;
use Tao\Forms\Statics\FormElements as form;
use Tao\Themes\Collection as ThemesCollection;
use Tao\Admin\Filters\Themes as ThemesFilters;


# Accès direct interdit
if (!defined('OKT_THEMES_MANAGEMENT')) die;


/* Initialisations
----------------------------------------------------------*/

# Initialisation des filtres
$oFilters = new ThemesFilters($okt, array());

# Liste des thèmes présents
$aInstalledThemes = $oThemes->getThemesAdminList();

# Tri par ordre alphabétique des listes de thème
ThemesCollection::sortThemes($aInstalledThemes);


/* Traitements
----------------------------------------------------------*/

# json themes list for autocomplete
if (!empty($_REQUEST['json']))
{
	$aResults = array();
	foreach ($aInstalledThemes as $aTheme)
	{
		foreach ($aTheme['index'] as $s)
		{
			if (strpos($s, $_GET['term']) !== false) {
				$aResults[] = $s;
			}
		}
	}

	header('Content-type: application/json');
	echo json_encode(array_unique($aResults));

	exit;
}

# affichage des notes d'un thème
if (!empty($_GET['notes']) && file_exists($okt->options->get('themes_dir').'/'.$_GET['notes'].'/notes.md'))
{
	echo Parsedown::instance()->parse(file_get_contents($okt->options->get('themes_dir').'/'.$_GET['notes'].'/notes.md'));

	exit;
}

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$oFilters->initFilters();
	http::redirect('configuration.php?action=themes');
}

# Suppression d'un thème
if (!empty($_GET['delete']) && isset($aInstalledThemes[$_GET['delete']]) && !$aInstalledThemes[$_GET['delete']]['is_active'])
{
	if (files::deltree($okt->options->get('themes_dir').'/'.$_GET['delete']))
	{
		$okt->page->flash->success(__('c_a_themes_successfully_deleted'));

		http::redirect('configuration.php?action=themes');
	}
}

# Utilisation d'un thème
if (!empty($_GET['use']))
{
	try
	{
		# write config
		$okt->config->write(array(
			'theme' => $_GET['use']
		));

		# modules config sheme
		$sTplScheme = $okt->options->get('themes_dir').'/'.$_GET['use'].'/modules_config_scheme.php';

		if (file_exists($sTplScheme)) {
			include $sTplScheme;
		}

		$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

		http::redirect('configuration.php?action=themes');
	}
	catch (InvalidArgumentException $e)
	{
		$okt->error->set(__('c_c_error_writing_configuration'));
		$okt->error->set($e->getMessage());
	}
}

# Utilisation d'un thème mobile
if (!empty($_GET['use_mobile']))
{
	try
	{
		if ($_GET['use_mobile'] == $okt->config->theme_mobile) {
			$_GET['use_mobile'] = '';
		}

		$okt->config->write(array(
			'theme_mobile' => $_GET['use_mobile']
		));

		$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

		http::redirect('configuration.php?action=themes');
	}
	catch (InvalidArgumentException $e)
	{
		$okt->error->set(__('c_c_error_writing_configuration'));
		$okt->error->set($e->getMessage());
	}
}

# Utilisation d'un thème tablette
if (!empty($_GET['use_tablet']))
{
	try
	{
		if ($_GET['use_tablet'] == $okt->config->theme_tablet) {
			$_GET['use_tablet'] = '';
		}

		$okt->config->write(array(
			'theme_tablet' => $_GET['use_tablet']
		));

		$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

		http::redirect('configuration.php?action=themes');
	}
	catch (InvalidArgumentException $e)
	{
		$okt->error->set(__('c_c_error_writing_configuration'));
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Initialisation des filtres
$sSearch = null;

if (!empty($_REQUEST['search']))
{
	$sSearch = strtolower(trim($_REQUEST['search']));

	foreach ($aInstalledThemes as $iThemeId=>$aTheme)
	{
		if (!in_array($sSearch, $aTheme['index'])) {
			unset($aInstalledThemes[$iThemeId]);
		}
	}
}

# Création des filtres
$oFilters->getFilters();


# Initialisation de la pagination
$iNumInstalledThemes = count($aInstalledThemes);

$oPager = new Pager($oFilters->params->page, $iNumInstalledThemes, $oFilters->params->nb_per_page);

$iNumPages = $oPager->getNbPages();

$oFilters->normalizePage($iNumPages);

$aInstalledThemes = array_slice($aInstalledThemes, (($oFilters->params->page-1)*$oFilters->params->nb_per_page), $oFilters->params->nb_per_page);


# button set
$okt->page->setButtonset('themesBtSt',array(
	'id' => 'themes-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_display_filters'),
			'url' 			=> '#',
			'ui-icon' 		=> 'search',
			'active' 		=> $oFilters->params->show_filters,
			'id'			=> 'filter-control',
			'class'			=> 'button-toggleable'
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Add'),
			'url' 			=> 'configuration.php?action=themes&amp;do=add',
			'ui-icon' 		=> 'plusthick'
		)
	)
));

# Display a UI dialog box
$okt->page->js->addReady("
	$('#filters-form').dialog({
		title:'".html::escapeJS(__('c_c_display_filters'))."',
		autoOpen: false,
		modal: true,
		width: 500,
		height: 300
	});

	$('#filter-control').click(function() {
		$('#filters-form').dialog('open');
	})
");

# infos page
$okt->page->addGlobalTitle(__('c_a_themes_management'));

# CSS
$okt->page->css->addCss('

#themes-list .theme {
	width: 24%;
	float: left;
	margin:  0 1% 1% 0;
}
#themes-list .theme .ui-widget-header {
	margin: 0;
	padding: 0.3em;
}
#themes-list .theme .ui-widget-content>h4 {
	margin-top: 0.3em;

	}
#themes-list .theme .ui-widget-content {
	margin: 0;
	padding: 0.8em;
	min-height: 420px;
	position: relative;
}
#themes-list .theme .themeActions {
	position: absolute;
	left: 0.8em;
	bottom: 0.8em;
}

');

# Loader
$okt->page->loader('.lazy-load');

if (!empty($_GET['added'])) {
	$okt->page->success->set(($_GET['added'] == 2
		? __('c_a_themes_successfully_upgraded')
		: __('c_a_themes_successfully_added')
	));
}

# JS buttons sets
$okt->page->js->addReady('

	$(".themeActions").buttonset();

	$(".button-use").button("option", "icons", {
		primary: "ui-icon-transferthick-e-w"
	});

	$(".button-use-mobile, .button-use-tablet").button("option", "icons", {
		primary: "ui-icon-transfer-e-w"
	});

	$(".button-used, .button-mobile-used, .button-tablet-used").button("option", {
		icons: {
			primary: "ui-icon-check"
		},
		disabled: true
	}).addClass("ui-state-active").removeClass("ui-state-disabled");

	$(".button-mobile-used").button("option", {
		disabled: false
	});

	$(".button-tablet-used").button("option", {
		disabled: false
	});

	$(".button-edit").button("option", {
		icons: {
			primary: "ui-icon-pencil"
		},
		text: false
	});

	$(".button-config").button("option", {
		icons: {
			primary: "ui-icon-gear"
		},
		text: false
	});

	$(".button-notes").button("option", {
		icons: {
			primary: "ui-icon-script"
		},
		text: false
	});

	$(".button-delete").button("option", {
		icons: {
			primary: "ui-icon-trash"
		},
		text: false
	});
');

$okt->page->openLinkInDialog('.button-notes',array(
	'title' => __('c_a_themes_notes'),
	'width' => 730,
	'height' => 500
));

# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "configuration.php?search=&action=themes&json=1",
		minLength: 2
	});
');

if (!empty($sSearch))
{
	$okt->page->js->addFile($okt->options->public_url.'/js/jquery/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('themesBtSt'); ?>
	</div>
	<div class="buttonsetB">
		<form action="configuration.php" method="get" id="search_form" class="search_form">
			<p><label for="search"><?php _e('c_c_action_Search') ?></label>
			<?php echo form::text('search',20,255,html::escapeHTML((isset($sSearch) ? $sSearch : ''))); ?>

			<?php echo form::hidden(array('action'), 'themes') ?>
			<input type="submit" name="search_submit" id="search_submit" value="ok" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="configuration.php" method="get" id="filters-form">
	<fieldset>

		<?php echo $oFilters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden(array('action'), 'themes') ?>
		<input type="submit" name="<?php echo $oFilters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display') ?>" />
		<a href="configuration.php?action=themes&amp;init_filters=1"><?php _e('c_c_reset_filters') ?></a></p>

	</fieldset>
</form>

<div id="themes-list">
	<?php foreach ($aInstalledThemes as $aTheme) : ?>

	<div id="theme_<?php echo $aTheme['id'] ?>" class="ui-widget ui-helper-reset theme">
		<h4 class="ui-widget-header ui-corner-top <?php echo ($aTheme['is_active'] ? 'ui-state-active' : ''); ?>"><?php echo $aTheme['name'] ?></h4>

		<div class="ui-widget-content ui-corner-bottom">

			<div class="theme-screenshot">
				<?php if ($aTheme['screenshot']) : ?>
				<img src="<?php echo $okt->config->app_path.basename($okt->options->get('themes_dir')).'/'.$aTheme['id'].'/screenshot.jpg' ?>" width="100%" height="100%" alt="" />
				<?php else : ?>
				<em class="note center"><?php _e('c_a_themes_no_screenshot') ?></em>
				<?php endif; ?>
			</div>

			<p><?php echo $aTheme['desc'] ?></p>

			<p><?php printf(__('c_a_themes_version_%s'), $aTheme['version']) ?>

			<?php # buton set
			$aActions = array();
			if ($aTheme['is_active']) {
				$aActions[10] = '<a href="#" class="button-used">'.__('c_a_themes_current').'</a>';
			}
			else {
				$aActions[10] = '<a href="configuration.php?action=themes&amp;use='.$aTheme['id'].'" class="button-use" title="'.__('c_a_themes_use_this_theme').'">'.__('c_a_themes_use').'</a>';
			}

			if ($aTheme['is_mobile']) {
				$aActions[20] = '<a href="configuration.php?action=themes&amp;use_mobile='.$aTheme['id'].'" class="button-mobile-used">'.__('c_a_themes_current_mobile').'</a>';
			}
			else {
				$aActions[20] = '<a href="configuration.php?action=themes&amp;use_mobile='.$aTheme['id'].'" class="button-use-mobile">'.__('c_a_themes_use_mobile').'</a>';
			}

			if ($aTheme['is_tablet']) {
				$aActions[30] = '<a href="configuration.php?action=themes&amp;use_tablet='.$aTheme['id'].'" class="button-tablet-used">'.__('c_a_themes_current_tablet').'</a>';
			}
			else {
				$aActions[30] = '<a href="configuration.php?action=themes&amp;use_tablet='.$aTheme['id'].'" class="button-use-tablet">'.__('c_a_themes_use_tablet').'</a>';
			}

			$aActions[40] = '<a href="configuration.php?action=theme_editor&amp;theme='.$aTheme['id'].'" class="button-edit"></span>'.__('c_c_action_Edit').'</a>';

			$aActions[50] = '<a href="configuration.php?action=theme&amp;theme_id='.$aTheme['id'].'" class="button-config">'.__('c_a_themes_config').'</a>';

			if (file_exists($okt->options->get('themes_dir').'/'.$aTheme['id'].'/notes.md')) {
				$aActions[60] = '<a href="configuration.php?action=themes&amp;notes='.$aTheme['id'].'" class="button-notes">'.__('c_a_themes_notes').'</a>';
			}

			if (!$aTheme['is_active'] && !$aTheme['is_mobile'] && !$aTheme['is_tablet']) {
				$aActions[90] = '<a href="configuration.php?action=themes&amp;delete='.$aTheme['id'].'" class="button-delete" onclick="return window.confirm(\''.html::escapeJS(__('c_a_themes_delete_confirm')).'\')"></span>'.__('c_c_action_Delete').'</a>';
			}

			ksort($aActions);

			?>

			<div class="themeActions">
				<?php echo implode("\n",$aActions) ?>
			</div>
		</div>
	</div>

	<?php endforeach; ?>
<div class="clearer"></div>
</div><!-- #themes-list -->

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
