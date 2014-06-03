<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# infos page
$okt->page->addGlobalTitle(__('c_a_themes_management'));

# button set
$okt->page->setButtonset('themesBtSt', array(
	'id' => 'themes-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_display_filters'),
			'url' => '#',
			'ui-icon' => 'search',
			'active' => $oFilters->params->show_filters,
			'id' => 'filter-control',
			'class' => 'button-toggleable'
		),
		array(
			'permission' => true,
			'title' => __('c_c_action_Add'),
			'url' => $view->generateUrl('config_theme_add'),
			'ui-icon' => 'plusthick'
		)
	)
));

# Display a UI dialog box
$okt->page->js->addReady("
	$('#filters-form').dialog({
		title:'" . $view->escapeJs(__('c_c_display_filters')) . "',
		autoOpen: false,
		modal: true,
		width: 500,
		height: 300
	});

	$('#filter-control').click(function() {
		$('#filters-form').dialog('open');
	})
");

# CSS
$okt->page->css->addCss('
.ui-autocomplete {
	max-height: 150px;
	overflow-y: auto;
	overflow-x: hidden;
}
.search_form p {
	margin: 0;
}
#search {
	background: transparent url(' . $okt->options->public_url . '/img/admin/preview.png) no-repeat center right;
}
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

$okt->page->openLinkInDialog('.button-notes', array(
	'title' => __('c_a_themes_notes'),
	'width' => 730,
	'height' => 500
));

# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "' . $view->generateUrl('config_themes') . '?json=1",
		minLength: 2
	});
');

if (! empty($sSearch))
{
	$okt->page->js->addFile($okt->options->public_url . '/plugins/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}

?>

<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('themesBtSt'); ?>
	</div>
	<div class="buttonsetB">
		<form action="<?php echo $view->generateUrl('config_themes') ?>"
			method="get" id="search_form" class="search_form">
			<p>
				<label for="search"><?php _e('c_c_action_Search') ?></label>
			<?php echo form::text('search', 20, 255, $view->escape($sSearch)); ?>
			<input type="submit" name="search_submit" id="search_submit"
					value="ok" />
			</p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="<?php echo $view->generateUrl('config_themes') ?>"
	method="get" id="filters-form">
	<fieldset>
		<?php echo $oFilters->getFiltersFields('<div class="three-cols">%s</div>'); ?>
		<p>
			<input type="submit"
				name="<?php echo $oFilters->getFilterSubmitName() ?>"
				value="<?php _e('c_c_action_display') ?>" /> <a
				href="<?php echo $view->generateUrl('config_themes') ?>?init_filters=1"><?php _e('c_c_reset_filters') ?></a>
		</p>
	</fieldset>
</form>

<div id="themes-list">
	<?php foreach ($aInstalledThemes as $aTheme) : ?>

	<div id="theme_<?php echo $aTheme['id'] ?>"
		class="ui-widget ui-helper-reset theme">
		<h4
			class="ui-widget-header ui-corner-top <?php echo ($aTheme['is_active'] ? 'ui-state-active' : ''); ?>"><?php echo $aTheme['name'] ?></h4>

		<div class="ui-widget-content ui-corner-bottom">

			<div class="theme-screenshot">
				<?php if ($aTheme['screenshot']) : ?>
				<img
					src="<?php echo $okt->config->app_path.basename($okt->options->get('themes_dir')).'/'.$aTheme['id'].'/screenshot.jpg' ?>"
					width="100%" height="100%" alt="" />
				<?php else : ?>
				<em class="note center"><?php _e('c_a_themes_no_screenshot') ?></em>
				<?php endif; ?>
			</div>

			<p><?php echo $aTheme['desc'] ?></p>

			<p><?php printf(__('c_a_themes_version_%s'), $aTheme['version'])?>

			<?php
		# buton set
		$aActions = array();
		if ($aTheme['is_active'])
		{
			$aActions[10] = '<a href="#" class="button-used">' . __('c_a_themes_current') . '</a>';
		}
		else
		{
			$aActions[10] = '<a href="' . $view->generateUrl('config_themes') . '?use=' . $aTheme['id'] . '" class="button-use" title="' . __('c_a_themes_use_this_theme') . '">' . __('c_a_themes_use') . '</a>';
		}
		
		if ($aTheme['is_mobile'])
		{
			$aActions[20] = '<a href="' . $view->generateUrl('config_themes') . '?use_mobile=' . $aTheme['id'] . '" class="button-mobile-used">' . __('c_a_themes_current_mobile') . '</a>';
		}
		else
		{
			$aActions[20] = '<a href="' . $view->generateUrl('config_themes') . '?use_mobile=' . $aTheme['id'] . '" class="button-use-mobile">' . __('c_a_themes_use_mobile') . '</a>';
		}
		
		if ($aTheme['is_tablet'])
		{
			$aActions[30] = '<a href="' . $view->generateUrl('config_themes') . '?use_tablet=' . $aTheme['id'] . '" class="button-tablet-used">' . __('c_a_themes_current_tablet') . '</a>';
		}
		else
		{
			$aActions[30] = '<a href="' . $view->generateUrl('config_themes') . '?use_tablet=' . $aTheme['id'] . '" class="button-use-tablet">' . __('c_a_themes_use_tablet') . '</a>';
		}
		
		$aActions[40] = '<a href="configuration.php?action=theme_editor&amp;theme=' . $aTheme['id'] . '" class="button-edit"></span>' . __('c_c_action_Edit') . '</a>';
		
		$aActions[50] = '<a href="' . $view->generateUrl('config_theme', array(
			'theme_id' => $aTheme['id']
		)) . '" class="button-config">' . __('c_a_themes_config') . '</a>';
		
		if (file_exists($okt->options->get('themes_dir') . '/' . $aTheme['id'] . '/notes.md'))
		{
			$aActions[60] = '<a href="' . $view->generateUrl('config_themes') . '?notes=' . $aTheme['id'] . '" class="button-notes">' . __('c_a_themes_notes') . '</a>';
		}
		
		if (! $aTheme['is_active'] && ! $aTheme['is_mobile'] && ! $aTheme['is_tablet'])
		{
			$aActions[90] = '<a href="' . $view->generateUrl('config_themes') . '?delete=' . $aTheme['id'] . '" class="button-delete" onclick="return window.confirm(\'' . $view->escapeJs(__('c_a_themes_delete_confirm')) . '\')"></span>' . __('c_c_action_Delete') . '</a>';
		}
		
		ksort($aActions);
		
		?>

			
			
			
			
			
			
			
			
			
			<div class="themeActions">
				<?php echo implode("\n",$aActions)?>
			</div>
		</div>
	</div>

	<?php endforeach; ?>
<div class="clearer"></div>
</div>
<!-- #themes-list -->

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>
