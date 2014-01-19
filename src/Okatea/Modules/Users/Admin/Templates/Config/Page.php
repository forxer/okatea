<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');


# infos page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# JS pour qu'on ne puissent activer la page de connexion/inscription unifiée
# que si les deux pages connexion ET inscription sont activées
$okt->page->js->addScript('
	function setEnableLogRegStatus() {
		if ($("#p_enable_login_page").is(":checked") && $("#p_enable_register_page").is(":checked")) {
			$("#p_enable_log_reg_page").removeAttr("disabled")
				.parent().removeClass("disabled")
				.parent().find(".note").hide();
		} else {
			$("#p_enable_log_reg_page").attr("disabled", "")
				.parent().addClass("disabled")
				.parent().find(".note").show();
		}
	}

	function handleValidateOptionStatus() {
		if ($("#p_validate_users_registration").is(":checked")) {
			$("#p_user_choose_group,#p_auto_log_after_registration").attr("disabled", "")
				.parent().addClass("disabled");
		}
		else {
			$("#p_user_choose_group,#p_auto_log_after_registration").removeAttr("disabled")
				.parent().removeClass("disabled");
		}
	}
');
$okt->page->js->addReady('
	setEnableLogRegStatus();
	$("#p_enable_login_page,#p_enable_register_page").change(function(){setEnableLogRegStatus();});

	handleValidateOptionStatus();
	$("#p_validate_users_registration").change(function(){handleValidateOptionStatus();});
');

?>

<form action="<?php echo $view->generateUrl('Users_config') ?>" method="post">
	<div id="tabered">
		<ul>
		<?php foreach ($aConfigTabs as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
		<?php endforeach; ?>
		</ul>

		<?php foreach ($aConfigTabs as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>
