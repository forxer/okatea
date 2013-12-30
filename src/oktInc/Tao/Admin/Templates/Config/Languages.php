<?php

use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');


# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_l10n'));


# Javascript
$okt->page->tabs();

$okt->page->validate('add-language-form', array(
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

$okt->page->validate('edit-language-form', array(
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


$okt->page->css->addFile($okt->options->public_url.'/plugins/select2/select2.css');
$okt->page->js->addFile($okt->options->public_url.'/plugins/select2/select2.min.js');
$okt->page->js->addReady('

	function format(flag) {
		return \'<img class="flag" src="'.$okt->options->public_url.'/img/flags/\' + flag.id + \'" /> <strong>\' + flag.text + \'</strong> - \' + flag.id
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
				url: "'.$view->generateUrl('config_languages').'?ajax_update_order=1",
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
?>

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
		<form id="edit-language-form" action="<?php echo $view->generateUrl('config_languages') ?>" method="post">
			<h3><?php _e('c_a_config_l10n_tab_edit') ?></h3>

			<div class="two-cols">
				<p class="field col"><label for="edit_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_title') ?></label>
				<?php echo form::text('edit_title', 40, 255, $view->escape($aUpdLanguageData['title'])) ?></p>

				<p id="edit_active_container" class="col">
					<?php echo form::radio(array('edit_active', 'edit_active_1'), 1, ($aUpdLanguageData['active'] == 1)) ?><label for="edit_active_1"><?php _e('c_c_action_Enable') ?></label>
					<?php echo form::radio(array('edit_active', 'edit_active_0'), 0, ($aUpdLanguageData['active'] == 0)) ?><label for="edit_active_0"><?php _e('c_c_action_Disable') ?></label>
				</p>
			</div>

			<div class="two-cols">
				<p class="field col"><label for="edit_code" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_code') ?></label>
				<?php echo form::text('edit_code', 10, 255, $view->escape($aUpdLanguageData['code'])) ?></p>

				<p class="field col"><label for="edit_img"><?php _e('c_a_config_l10n_icon') ?></label>
				<?php echo form::select('edit_img', $aFlags, $view->escape($aUpdLanguageData['img'])) ?></p>
			</div>

			<p><?php echo form::hidden('edit_languages', 1) ?>
			<?php echo form::hidden('id', $iLangId) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_edit') ?>" />
			<a href="<?php echo $view->generateUrl('config_languages') ?>" class="button"><?php _e('c_c_action_cancel') ?></a></p>
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

				<?php if (file_exists($okt->options->public_dir.'/img/flags/'.$rsLanguages->img)) : ?>
				<img src="<?php echo $okt->options->public_url.'/img/flags/'.$rsLanguages->img ?>" alt="" />
				<?php endif; ?>

				<?php echo $view->escape($rsLanguages->title) ?></label>

				<?php echo form::text(array('p_order['.$rsLanguages->id.']','p_order_'.$rsLanguages->id), 5, 10, $i++) ?>

				(<?php echo $rsLanguages->code ?>)

				<?php if ($rsLanguages->active) : ?>
				- <a href="<?php echo $view->generateUrl('config_languages') ?>?disable=<?php echo $rsLanguages->id ?>"
				title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Disable_%s'), $rsLanguages->title)) ?>"
				class="icon tick"><?php _e('c_c_action_Disable') ?></a>
				<?php else : ?>
				- <a href="<?php echo $view->generateUrl('config_languages') ?>?enable=<?php echo $rsLanguages->id ?>"
				title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Enable_%s'), $rsLanguages->title)) ?>"
				class="icon cross"><?php _e('c_c_action_Enable') ?></a>
				<?php endif; ?>

				- <a href="<?php echo $view->generateUrl('config_languages') ?>?id=<?php echo $rsLanguages->id ?>"
				title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Edit_%s'), $rsLanguages->title)) ?>"
				class="icon pencil"><?php _e('c_c_action_Edit') ?></a>

				- <a href="<?php echo $view->generateUrl('config_languages') ?>?delete=<?php echo $rsLanguages->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_config_l10n_confirm_delete')) ?>')"
				title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Delete_%s'), $rsLanguages->title)) ?>"
				class="icon delete"><?php _e('c_c_action_Delete') ?></a>

			</li>
			<?php endwhile; ?>
			</ul>
			<p><?php echo form::hidden('ordered', 1); ?>
			<?php echo form::hidden('order_languages', 1); ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>
		</form>
	</div><!-- #tab-list -->

	<div id="tab-add">
		<form id="add-language-form" action="<?php echo $view->generateUrl('config_languages') ?>" method="post">
			<h3><?php _e('c_a_config_l10n_tab_add') ?></h3>

			<div class="two-cols">
				<p class="field col"><label for="add_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_title') ?></label>
				<?php echo form::text('add_title', 40, 255, $view->escape($aAddLanguageData['title'])) ?></p>

				<p id="add_active_container" class="col">
					<?php echo form::radio(array('add_active', 'add_active_1'), 1, ($aAddLanguageData['active'] == 1)) ?><label for="add_active_1"><?php _e('c_c_action_Enable') ?></label>
					<?php echo form::radio(array('add_active', 'add_active_0'), 0, ($aAddLanguageData['active'] == 0)) ?><label for="add_active_0"><?php _e('c_c_action_Disable') ?></label>
				</p>
			</div>

			<div class="two-cols">
				<p class="field col"><label for="add_code" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_l10n_code') ?></label>
				<?php echo form::text('add_code', 10, 255, $view->escape($aAddLanguageData['code'])) ?></p>

				<p class="field col"><label for="add_img"><?php _e('c_a_config_l10n_icon') ?></label>
				<?php echo form::select('add_img', $aFlags, $view->escape($aAddLanguageData['img'])) ?></p>
			</div>

			<p><?php echo form::hidden('add_languages', 1) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_add') ?>" /></p>
		</form>
	</div><!-- #tab-add -->

	<div id="tab-config">
		<form action="<?php echo $view->generateUrl('config_languages') ?>" method="post">
			<h3><?php _e('c_a_config_l10n_tab_config') ?></h3>

			<div class="three-cols">

				<p class="field col"><label for="p_language"><?php _e('c_a_config_l10n_default_language') ?></label>
				<?php echo form::select('p_language', $aLanguages, $okt->config->language) ?></p>

				<p class="field col"><label for="p_timezone"><?php _e('c_a_config_l10n_default_timezone') ?></label>
				<?php echo form::select('p_timezone', $aTimezones, $okt->config->timezone) ?></p>

				<p class="col"><?php echo form::checkbox('p_admin_lang_switcher', 1, $okt->config->admin_lang_switcher) ?>
				<label for="p_admin_lang_switcher"><?php _e('c_a_config_l10n_enable_switcher') ?></label></p>

			</div>

			<p><?php echo form::hidden('config_sent', 1) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>
	</div><!-- #tab-config -->

</div><!-- #tabered -->

