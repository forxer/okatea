<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de configuration des langues
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.languages');

$iLangId = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : null;

$aAddLanguageData = array(
	'title' => '',
	'code' => '',
	'img' => '',
	'active' => 1
);

$aUpdLanguageData = array(
	'title' => '',
	'code' => '',
	'img' => '',
	'active' => 1
);

if ($iLangId)
{
	$rsLanguage = $okt->languages->getLanguages(array('id'=>$iLangId));

	$aUpdLanguageData = array(
		'title' 	=> $rsLanguage->title,
		'code' 		=> $rsLanguage->code,
		'img' 		=> $rsLanguage->img,
		'active' 	=> $rsLanguage->active
	);

	unset($rsLanguage);
}


/* Traitements
----------------------------------------------------------*/

# switch language status
if (!empty($_GET['switch_status']))
{
	$okt->languages->switchLangStatus($_GET['switch_status']);
	$okt->redirect('configuration.php?action=languages&switched=1');
}

# enable language
if (!empty($_GET['enable']))
{
	$okt->languages->setLangStatus($_GET['enable'], 1);
	$okt->redirect('configuration.php?action=languages&enabled=1');
}

# disable language
if (!empty($_GET['disable']))
{
	$okt->languages->setLangStatus($_GET['disable'], 0);
	$okt->redirect('configuration.php?action=languages&disabled=1');
}

# suppression d'une langue
if (!empty($_GET['delete']))
{
	$okt->languages->delLanguage($_GET['delete']);
	$okt->redirect('configuration.php?action=languages&deleted=1');
}

# ajout d'une langue
if (!empty($_POST['add_languages']))
{
	$aAddLanguageData = array(
		'title' 	=> !empty($_POST['add_title']) ? $_POST['add_title'] : '',
		'code' 		=> !empty($_POST['add_code']) ? $_POST['add_code'] : '',
		'img' 		=> !empty($_POST['add_img']) ? $_POST['add_img'] : '',
		'active' 	=> !empty($_POST['add_active']) ? intval($_POST['add_active']) : 0
	);

	if ($okt->languages->checkPostData($aAddLanguageData))
	{
		$okt->languages->addLanguage($aAddLanguageData);
		$okt->redirect('configuration.php?action=languages&added=1');
	}
}

# modification d'une langue
if (!empty($_POST['edit_languages']) && $iLangId)
{
	$aUpdLanguageData = array(
		'id' 	=> $iLangId,
		'title' 	=> !empty($_POST['edit_title']) ? $_POST['edit_title'] : '',
		'code' 		=> !empty($_POST['edit_code']) ? $_POST['edit_code'] : '',
		'img' 		=> !empty($_POST['edit_img']) ? $_POST['edit_img'] : '',
		'active' 	=> !empty($_POST['edit_active']) ? intval($_POST['edit_active']) : 0
	);

	if ($okt->languages->checkPostData($aUpdLanguageData))
	{
		$okt->languages->updLanguage($aUpdLanguageData);
		$okt->redirect('configuration.php?action=languages&edited=1');
	}
}


# AJAX : changement de l'ordre des langues
if (!empty($_GET['ajax_update_order']))
{
	$aLanguagesOrder = !empty($_GET['ord']) && is_array($_GET['ord']) ? $_GET['ord'] : array();

	if (!empty($aLanguagesOrder))
	{
		foreach ($aLanguagesOrder as $ord=>$id)
		{
			$ord = ((integer)$ord)+1;
			$okt->languages->updLanguageOrder($id,$ord);
		}

		$okt->languages->generateCacheList();
	}

	exit();
}

# POST : changement de l'ordre des langues
if (!empty($_POST['order_languages']))
{
	$aLanguagesOrder = !empty($_POST['p_order']) && is_array($_POST['p_order']) ? $_POST['p_order'] : array();

	asort($aLanguagesOrder);

	$aLanguagesOrder = array_keys($aLanguagesOrder);

	if (!empty($aLanguagesOrder))
	{
		foreach ($aLanguagesOrder as $ord=>$id)
		{
			$ord = ((integer)$ord)+1;
			$okt->languages->updLanguageOrder($id,$ord);
		}

		$okt->languages->generateCacheList();

		$okt->redirect('configuration.php?action=languages&neworder=1');
	}
}

# configuration
if (!empty($_POST['config_sent']))
{
	$p_language = !empty($_POST['p_language']) ? $_POST['p_language'] : '';
	$p_timezone = !empty($_POST['p_timezone']) ? $_POST['p_timezone'] : '';
	$p_admin_lang_switcher = !empty($_POST['p_admin_lang_switcher']) ? (boolean)$_POST['p_admin_lang_switcher'] : false;

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'language' => $p_language,
			'timezone' => $p_timezone,
			'admin_lang_switcher' => $p_admin_lang_switcher
		);

		try
		{
			$okt->config->write($new_conf);
			$okt->languages->generateCacheList();
			$okt->redirect('configuration.php?action=languages&updated=1');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des langues
$rsLanguages = $okt->languages->getLanguages();

$aLanguages = array();
while ($rsLanguages->fetch()) {
	$aLanguages[html::escapeHTML($rsLanguages->title)] = $rsLanguages->code;
}

# Liste des fuseaux horraires
$aTimezones = dt::getZones(true,true);

# Liste des icônes
$aFlags = array();
foreach (new DirectoryIterator(OKT_PUBLIC_PATH.'/img/flags/') as $oFileInfo)
{
	if ($oFileInfo->isDot() || !$oFileInfo->isFile() || files::getExtension($oFileInfo->getFilename()) !== 'png') {
		continue;
	}

	$aFlags[str_replace('.png', '', $oFileInfo->getFilename())] = $oFileInfo->getFilename();
}
natsort($aFlags);


# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_l10n'));


# Javascript
$okt->page->tabs();

$okt->page->validate('add-language-form',array(
	array(
		'id' => 'add_title',
		'rules' => array(
			'required: true'
		)
	),
	array(
		'id' => 'add_code',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));

$okt->page->validate('edit-language-form',array(
	array(
		'id' => 'edit_title',
		'rules' => array(
			'required: true'
		)
	),
	array(
		'id' => 'edit_code',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	)
));


$okt->page->css->addFile(OKT_PUBLIC_URL.'/plugins/select2/select2.css');
$okt->page->js->addFile(OKT_PUBLIC_URL.'/plugins/select2/select2.min.js');
$okt->page->js->addReady('

	function format(flag) {
		return \'<img class="flag" src="'.OKT_PUBLIC_URL.'/img/flags/\' + flag.id + \'" /> <strong>\' + flag.text + \'</strong> - \' + flag.id
	}

	$("#add_img, #edit_img").select2({
		width: "165px",
		formatResult: format,
		formatSelection: format,
		escapeMarkup: function(m) { return m; }
	});

	$("#add_code").keyup(function() {
		$("#add_img").val( $(this).val() + ".png" ).trigger("change");
	});

	$("#edit_code").keyup(function() {
		$("#edit_img").val( $(this).val() + ".png" ).trigger("change");
	});

');


# Sortable
$okt->page->js->addReady('
	$("#sortable").sortable({
		placeholder: "ui-state-highlight",
		axis: "y",
		revert: true,
		cursor: "move",
		change: function(event, ui) {
			$("#page,#sortable").css("cursor", "progress");
		},
		update: function(event, ui) {
			var result = $("#sortable").sortable("serialize");

			$.ajax({
				data: result,
				url: "configuration.php?action=languages&ajax_update_order=1",
				success: function(data) {
					$("#page").css("cursor", "default");
					$("#sortable").css("cursor", "move");
				},
				error: function(data) {
					$("#page").css("cursor", "default");
					$("#sortable").css("cursor", "move");
				}
			});
		}
	});

	$("#sortable").find("input").hide();
	$("#save_order").hide();
	$("#sortable").css("cursor", "move");
');


# Buttons
$okt->page->js->addReady('

	$("#p_admin_lang_switcher").button({
		icons: {
			primary: "ui-icon-flag"
		}
	});

	$("#edit_active").button();

	$("#add_active_container, #edit_active_container").buttonset();
');


# Confirmationss
$okt->page->messages->success('added', __('c_a_config_l10n_added'));
$okt->page->messages->success('edited', __('c_a_config_l10n_edited'));
$okt->page->messages->success('deleted', __('c_a_config_l10n_deleted'));
$okt->page->messages->success('updated', __('c_c_confirm_configuration_updated'));
$okt->page->messages->success('neworder', __('c_a_config_l10n_neworder'));
$okt->page->messages->success('enabled', __('c_a_config_l10n_enabled'));
$okt->page->messages->success('disabled', __('c_a_config_l10n_disabled'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div id="tabered">
	<ul>
		<?php if ($iLangId) : ?>
		<li><a href="#tab-edit"><span><?php _e('c_a_config_l10n_tab_edit') ?></span></a></li>
		<?php endif; ?>
		<li><a href="#tab-list"><span><?php _e('c_a_config_l10n_tab_list') ?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('c_a_config_l10n_tab_add') ?></span></a></li>
		<li><a href="#tab-config"><span><?php _e('c_a_config_l10n_tab_config') ?></span></a></li>
	</ul>

	<?php if ($iLangId) : ?>
	<div id="tab-edit">
		<form id="edit-language-form" action="configuration.php" method="post">
			<h3><?php _e('c_a_config_l10n_tab_edit') ?></h3>

			<div class="two-cols">
				<p class="field col"><label for="edit_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_title') ?></label>
				<?php echo form::text('edit_title', 40, 255, html::escapeHTML($aUpdLanguageData['title'])) ?></p>

				<p id="edit_active_container" class="col">
					<?php echo form::radio(array('edit_active', 'edit_active_1'), 1, ($aUpdLanguageData['active'] == 1)) ?><label for="edit_active_1"><?php _e('c_c_action_Enable') ?></label>
					<?php echo form::radio(array('edit_active', 'edit_active_0'), 0, ($aUpdLanguageData['active'] == 0)) ?><label for="edit_active_0"><?php _e('c_c_action_Disable') ?></label>
				</p>
			</div>

			<div class="two-cols">
				<p class="field col"><label for="edit_code" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_code') ?></label>
				<?php echo form::text('edit_code', 10, 255, html::escapeHTML($aUpdLanguageData['code'])) ?></p>

				<p class="field col"><label for="edit_img"><?php _e('c_a_config_l10n_icon') ?></label>
				<?php echo form::select('edit_img', $aFlags, html::escapeHTML($aUpdLanguageData['img'])) ?></p>
			</div>

			<p><?php echo form::hidden('action', 'languages') ?>
			<?php echo form::hidden('edit_languages', 1) ?>
			<?php echo form::hidden('id', $iLangId) ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_edit') ?>" />
			<a href="configuration.php?action=languages" class="button"><?php _e('c_c_action_cancel') ?></a></p>
		</form>
	</div><!-- #tab-edit -->
	<?php endif; ?>

	<div id="tab-list">
		<h3><?php _e('c_a_config_l10n_tab_list') ?></h3>

		<form action="configuration.php" method="post" id="ordering">
			<ul id="sortable" class="ui-sortable">
			<?php $i = 1;
			while ($rsLanguages->fetch()) : ?>
			<li id="ord_<?php echo $rsLanguages->id ?>" class="ui-state-default"><label for="p_order_<?php echo $rsLanguages->id ?>">

				<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

				<?php if (file_exists(OKT_PUBLIC_PATH.'/img/flags/'.$rsLanguages->img)) : ?>
				<img src="<?php echo OKT_PUBLIC_URL.'/img/flags/'.$rsLanguages->img ?>" alt="" />
				<?php endif; ?>

				<?php echo html::escapeHTML($rsLanguages->title) ?></label>

				<?php echo form::text(array('p_order['.$rsLanguages->id.']','p_order_'.$rsLanguages->id), 5, 10, $i++) ?>

				(<?php echo $rsLanguages->code ?>)

				<?php if ($rsLanguages->active) : ?>
				- <a href="configuration.php?action=languages&amp;disable=<?php echo $rsLanguages->id ?>"
				title="<?php echo util::escapeAttrHTML(sprintf(__('c_c_action_Disable_%s'), $rsLanguages->title)) ?>"
				class="link_sprite ss_tick"><?php _e('c_c_action_Disable') ?></a>
				<?php else : ?>
				- <a href="configuration.php?action=languages&amp;enable=<?php echo $rsLanguages->id ?>"
				title="<?php echo util::escapeAttrHTML(sprintf(__('c_c_action_Enable_%s'), $rsLanguages->title)) ?>"
				class="link_sprite ss_cross"><?php _e('c_c_action_Enable') ?></a>
				<?php endif; ?>

				- <a href="configuration.php?action=languages&amp;id=<?php echo $rsLanguages->id ?>"
				title="<?php echo util::escapeAttrHTML(sprintf(__('c_c_action_Edit_%s'), $rsLanguages->title)) ?>"
				class="link_sprite ss_pencil"><?php _e('c_c_action_Edit') ?></a>

				- <a href="configuration.php?action=languages&amp;delete=<?php echo $rsLanguages->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_config_l10n_confirm_delete')) ?>')"
				title="<?php echo util::escapeAttrHTML(sprintf(__('c_c_action_Delete_%s'), $rsLanguages->title)) ?>"
				class="link_sprite ss_delete"><?php _e('c_c_action_Delete') ?></a>

			</li>
			<?php endwhile; ?>
			</ul>
			<p><?php echo form::hidden('action', 'languages') ?>
			<?php echo form::hidden('ordered', 1); ?>
			<?php echo form::hidden('order_languages', 1); ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>
		</form>
	</div><!-- #tab-list -->

	<div id="tab-add">
		<form id="add-language-form" action="configuration.php" method="post">
			<h3><?php _e('c_a_config_l10n_tab_add') ?></h3>

			<div class="two-cols">
				<p class="field col"><label for="add_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_title') ?></label>
				<?php echo form::text('add_title', 40, 255, html::escapeHTML($aAddLanguageData['title'])) ?></p>

				<p id="add_active_container" class="col">
					<?php echo form::radio(array('add_active', 'add_active_1'), 1, ($aAddLanguageData['active'] == 1)) ?><label for="add_active_1"><?php _e('c_c_action_Enable') ?></label>
					<?php echo form::radio(array('add_active', 'add_active_0'), 0, ($aAddLanguageData['active'] == 0)) ?><label for="add_active_0"><?php _e('c_c_action_Disable') ?></label>
				</p>
			</div>

			<div class="two-cols">
				<p class="field col"><label for="add_code" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_code') ?></label>
				<?php echo form::text('add_code', 10, 255, html::escapeHTML($aAddLanguageData['code'])) ?></p>

				<p class="field col"><label for="add_img"><?php _e('c_a_config_l10n_icon') ?></label>
				<?php echo form::select('add_img', $aFlags, html::escapeHTML($aAddLanguageData['img'])) ?></p>
			</div>

			<p><?php echo form::hidden('action', 'languages') ?>
			<?php echo form::hidden('add_languages', 1) ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_add') ?>" /></p>
		</form>
	</div><!-- #tab-add -->

	<div id="tab-config">
		<form action="configuration.php" method="post">
			<h3><?php _e('c_a_config_l10n_tab_config') ?></h3>

			<div class="three-cols">

				<p class="field col"><label for="p_language"><?php _e('c_a_config_l10n_default_language') ?></label>
				<?php echo form::select('p_language', $aLanguages, $okt->config->language) ?></p>

				<p class="field col"><label for="p_timezone"><?php _e('c_a_config_l10n_default_timezone') ?></label>
				<?php echo form::select('p_timezone', $aTimezones, $okt->config->timezone) ?></p>

				<p class="col"><?php echo form::checkbox('p_admin_lang_switcher',1,$okt->config->admin_lang_switcher) ?>
				<label for="p_admin_lang_switcher"><?php _e('c_a_config_l10n_enable_switcher') ?></label></p>

			</div>

			<p><?php echo form::hidden('action', 'languages') ?>
			<?php echo form::hidden('config_sent', 1) ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>
	</div><!-- #tab-config -->

</div><!-- #tabered -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
