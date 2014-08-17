<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

$okt->page->css->addCss('
	.selectedEnvironment {
		border: 1px solid #F09100;
		background-color: inherit;
		opacity: 1;
	}
	.unselectedEnvironment {
		border: 1px solid #f1f1f1;
		background-color: #f1f1f1;
		opacity: 0.7;
	}
');

$okt->page->js->addReady('
	function focusEnvironment() {
		if ($("#connect_prod").is(":checked")) {
			$("#dev-part").addClass("unselectedEnvironment").removeClass("selectedEnvironment");
			$("#prod-part").addClass("selectedEnvironment").removeClass("unselectedEnvironment");
		}
		else if ($("#connect_dev").is(":checked")) {
			$("#dev-part").addClass("selectedEnvironment").removeClass("unselectedEnvironment");
			$("#prod-part").addClass("unselectedEnvironment").removeClass("selectedEnvironment");
		}
	}

	focusEnvironment();
	$(\'input[name="connect"]\').click(focusEnvironment);

	$("#drivers .disabled").hide();

	$("#show_unsupported_drivers")
		.html(\'<a href="#" class="icon database_add">'.__('i_db_conf_driver_show_unsupported').'</a>\')
		.click(function(e) {
			$("#drivers .disabled").fadeIn();
			$(this).hide();
			e.preventDefault();
		});

');

?>

<?php if (!is_null($aPageData['checklist']) && !$okt['messages']->hasError()) : ?>

<form action="<?php echo $view->generateInstallUrl($okt->stepper->getNextStep()) ?>" method="post">

	<?php echo $aPageData['checklist']->getHTML(); ?>

	<?php if ($aPageData['checklist']->checkAll()) : ?>
	<p><?php _e('i_db_conf_next') ?></p>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" /></p>
	<?php endif; ?>
</form>

<?php else : ?>
<form action="<?php echo $view->generateInstallUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<p class="fake-label"><?php _e('i_db_conf_driver') ?></p>
	<ul class="checklist" id="drivers">
	<?php $iUnsupportedDriverCount = 0;
	foreach ($aPageData['drivers']->getDrivers() as $sDrivers => $driver) : ?>
		<li<?php if (!$driver->isSupported()) { echo ' class="disabled"'; $iUnsupportedDriverCount++; }?>><label for="driver_<?php echo $sDrivers ?>"><?php
		echo form::radio(['driver','driver_'.$sDrivers], $sDrivers, $aPageData['values']['driver'] == $sDrivers, '', null, !$driver->isSupported()) ?>
		<?php echo $sDrivers ?></label>
		<span class="note"><?php _e('i_db_conf_driver_'.$sDrivers) ?></span></li>
	<?php endforeach ?>
	</ul>
	<?php if ($iUnsupportedDriverCount > 0) : ?>
	<p id="show_unsupported_drivers"></p>
	<?php endif; ?>

	<div class="two-cols">
		<div class="col">
		<p><?php _e('i_db_conf_environement_choice') ?></p>
		<ul class="checklist">
			<li>
				<label for="connect_prod"><input type="radio" name="connect"
				id="connect_prod" value="prod"
				<?php if ($aPageData['values']['env'] == 'prod') echo ' checked="checked"'; ?> />
				<strong><?php _e('i_db_conf_environement_prod') ?></strong></label>
			</li>
			<li>
				<label for="connect_dev"><input type="radio" name="connect"
				id="connect_dev" value="dev"
				<?php if ($aPageData['values']['env'] == 'dev') echo ' checked="checked"'; ?> />
				<?php _e('i_db_conf_environement_dev') ?></label>
			</li>
		</ul>
		<p class="note"><?php _e('i_db_conf_environement_note') ?></p>
		</div>
		<div class="col">
			<p class="field"><label for="prod_prefix" title="<?php _e('c_c_required_field') ?>"
			class="required"><?php _e('i_db_conf_db_prefix') ?></label>
			<?php echo form::text('prefix', 40, 256, $view->escape($aPageData['values']['prefix'])) ?></p>
		</div>
	</div>

	<div class="two-cols">
		<div id="prod-part" class="col">
			<fieldset>
				<legend><?php _e('i_db_conf_prod_server') ?></legend>

	<?php foreach ($aPageData['drivers']->getDrivers() as $sDrivers => $driver) : ?>
		<?php if (!$driver->isSupported()) continue; ?>

		<div id="driver_prod_<?php echo $sDrivers ?>">
			<?php foreach ($driver->getConfigFields() as $aFields) : ?>
			<p class="field">
				<label for="prod_<?php echo $aFields['id'] ?>"<?php if ($aFields['required']) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif ?>><?php
				echo $aFields['label'] ?></label>
				<?php echo form::text('prod_'.$aFields['id'], 40, 256, '') ?>
			</p>
			<?php endforeach ?>
		</div>

	<?php endforeach ?>

				<p class="field">
					<label for="prod_host" title="<?php _e('c_c_required_field') ?>"
						class="required"><?php _e('i_db_conf_db_host') ?></label>
				<?php echo form::text('prod_host', 40, 256, $view->escape($aPageData['values']['prod']['host'])) ?></p>

				<p class="field">
					<label for="prod_name" title="<?php _e('c_c_required_field') ?>"
						class="required"><?php _e('i_db_conf_db_name') ?></label>
				<?php echo form::text('prod_name', 40, 256, $view->escape($aPageData['values']['prod']['name'])) ?></p>

				<p class="field">
					<label for="prod_user" title="<?php _e('c_c_required_field') ?>"
						class="required"><?php _e('i_db_conf_db_username') ?></label>
				<?php echo form::text('prod_user', 40, 256, $view->escape($aPageData['values']['prod']['user'])) ?></p>

				<p class="field">
					<label for="prod_password"><?php _e('i_db_conf_db_password') ?></label>
				<?php echo form::text('prod_password', 40, 256, $view->escape($aPageData['values']['prod']['password'])) ?></p>

			</fieldset>
		</div>

		<div id="dev-part" class="col">
			<fieldset>
				<legend><?php _e('i_db_conf_dev_server') ?></legend>

	<?php foreach ($aPageData['drivers']->getDrivers() as $sDrivers => $driver) : ?>
		<?php if (!$driver->isSupported()) continue; ?>

		<div id="driver_dev_<?php echo $sDrivers ?>">
			<?php foreach ($driver->getConfigFields() as $aFields) : ?>
			<p class="field">
				<label for="dev_<?php echo $aFields['id'] ?>"<?php if ($aFields['required']) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif ?>><?php
				echo $aFields['label'] ?></label>
				<?php echo form::text('dev_'.$aFields['id'], 40, 256, '') ?>
			</p>
			<?php endforeach ?>
		</div>

	<?php endforeach ?>

				<p class="field">
					<label for="dev_host" title="<?php _e('c_c_required_field') ?>"
						class="required"><?php _e('i_db_conf_db_host') ?></label>
				<?php echo form::text('dev_host', 40, 256, $view->escape($aPageData['values']['dev']['host'])) ?></p>

				<p class="field">
					<label for="dev_name" title="<?php _e('c_c_required_field') ?>"
						class="required"><?php _e('i_db_conf_db_name') ?></label>
				<?php echo form::text('dev_name', 40, 256, $view->escape($aPageData['values']['dev']['name'])) ?></p>

				<p class="field">
					<label for="dev_user" title="<?php _e('c_c_required_field') ?>"
						class="required"><?php _e('i_db_conf_db_username') ?></label>
				<?php echo form::text('dev_user', 40, 256, $view->escape($aPageData['values']['dev']['user'])) ?></p>

				<p class="field">
					<label for="dev_password"><?php _e('i_db_conf_db_password') ?></label>
				<?php echo form::text('dev_password', 40, 256, $view->escape($aPageData['values']['dev']['password'])) ?></p>

			</fieldset>
		</div>
	</div>

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
	</p>
</form>
<?php endif; ?>
