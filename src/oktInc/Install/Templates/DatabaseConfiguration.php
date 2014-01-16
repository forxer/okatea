<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');


$okt->page->css->addCss('
	.selectedPart {
		border: 1px solid #F09100;
		background-color: inherit;
		opacity: 1;
	}
	.unselectedPart {
		border: 1px solid #f1f1f1;
		background-color: #f1f1f1;
		opacity: 0.7;
	}
');

$okt->page->js->addReady('
	function focusEnvironmentPart() {
		if ($("#connect_prod").is(":checked")) {
			$("#dev-part").addClass("unselectedPart").removeClass("selectedPart");
			$("#prod-part").addClass("selectedPart").removeClass("unselectedPart");
		}
		else if ($("#connect_dev").is(":checked")) {
			$("#dev-part").addClass("selectedPart").removeClass("unselectedPart");
			$("#prod-part").addClass("unselectedPart").removeClass("selectedPart");
		}
	}

	focusEnvironmentPart();
	$(\'input[name="connect"]\').click(focusEnvironmentPart);
');
?>

<?php if ($bDatabaseConfigurationOk && $okt->error->isEmpty()) : ?>

	<form action="<?php echo $view->generateUrl($okt->stepper->getNextStep()) ?>" method="post">

		<p><?php _e('i_db_conf_ok') ?></p>

		<p><input type="submit" value="<?php _e('c_c_next') ?>" /></p>
	</form>

<?php else : ?>
<form action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<p><?php _e('i_db_conf_environement_choice') ?></p>
	<ul class="checklist">
		<li><label for="connect_prod"><input type="radio" name="connect" id="connect_prod" value="prod"<?php if ($aDatabaseParams['env'] == 'prod') echo ' checked="checked"'; ?> /> <strong><?php _e('i_db_conf_environement_prod') ?></strong></label></li>
		<li><label for="connect_dev"><input type="radio" name="connect" id="connect_dev" value="dev"<?php if ($aDatabaseParams['env'] == 'dev') echo ' checked="checked"'; ?> /> <?php _e('i_db_conf_environement_dev') ?></label></li>
	</ul>
	<p class="note"><?php _e('i_db_conf_environement_note') ?></p>

	<div class="two-cols">
		<div id="prod-part" class="col">
		<fieldset>
			<legend><?php _e('i_db_conf_prod_server') ?></legend>

			<p class="field"><label for="prod_host" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_host') ?></label>
			<?php echo form::text('prod_host', 40, 256, $view->escape($aDatabaseParams['prod']['host'])) ?></p>

			<p class="field"><label for="prod_name" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_name') ?></label>
			<?php echo form::text('prod_name', 40, 256, $view->escape($aDatabaseParams['prod']['name'])) ?></p>

			<p class="field"><label for="prod_user" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_username') ?></label>
			<?php echo form::text('prod_user', 40, 256, $view->escape($aDatabaseParams['prod']['user'])) ?></p>

			<p class="field"><label for="prod_password"><?php _e('i_db_conf_db_password') ?></label>
			<?php echo form::text('prod_password', 40, 256, $view->escape($aDatabaseParams['prod']['password'])) ?></p>

			<p class="field"><label for="prod_prefix" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_prefix') ?></label>
			<?php echo form::text('prod_prefix', 40, 256, $view->escape($aDatabaseParams['prod']['prefix'])) ?></p>
		</fieldset>
		</div>

		<div id="dev-part" class="col">
		<fieldset>
			<legend><?php _e('i_db_conf_dev_server') ?></legend>

			<p class="field"><label for="dev_host" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_host') ?></label>
			<?php echo form::text('dev_host', 40, 256, $view->escape($aDatabaseParams['dev']['host'])) ?></p>

			<p class="field"><label for="dev_name" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_name') ?></label>
			<?php echo form::text('dev_name', 40, 256, $view->escape($aDatabaseParams['dev']['name'])) ?></p>

			<p class="field"><label for="dev_user" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_username') ?></label>
			<?php echo form::text('dev_user', 40, 256, $view->escape($aDatabaseParams['dev']['user'])) ?></p>

			<p class="field"><label for="dev_password"><?php _e('i_db_conf_db_password') ?></label>
			<?php echo form::text('dev_password', 40, 256, $view->escape($aDatabaseParams['dev']['password'])) ?></p>

			<p class="field"><label for="dev_prefix" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_prefix') ?></label>
			<?php echo form::text('dev_prefix', 40, 256, $view->escape($aDatabaseParams['dev']['prefix'])) ?></p>
		</fieldset>
		</div>
	</div>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" /></p>
</form>
<?php endif; ?>
