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


	function handleDrivers() {

		var current_driver = $(\'input[name="driver"]:checked\').val();

		$("div.driver").each(function() {
			var driver = $(this);

			if (driver.data("driver-id") == current_driver) {
				driver.show();
			}
			else {
				driver.hide();
			}
		});
	}

	handleDrivers();
	$(\'input[name="driver"]\').click(handleDrivers);
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
	foreach ($aPageData['drivers']->getDrivers() as $sDriver => $driver) : ?>
		<li<?php if (!$driver->isSupported()) { echo ' class="disabled"'; $iUnsupportedDriverCount++; }?>><label for="driver_<?php echo $sDriver ?>"><?php
		echo form::radio(['driver','driver_'.$sDriver], $sDriver, $aPageData['values']['driver'] == $sDriver, '', null, !$driver->isSupported()) ?>
		<?php echo $sDriver ?></label>
		<span class="note"><?php _e('i_db_conf_driver_'.$sDriver) ?></span></li>
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
			<p class="field"><label for="prefix" title="<?php _e('c_c_required_field') ?>"
			class="required"><?php _e('i_db_conf_db_prefix') ?></label>
			<?php echo form::text('prefix', 40, 256, $view->escape($aPageData['values']['prefix'])) ?></p>
		</div>
	</div>

	<div class="two-cols">
		<div id="prod-part" class="col">
			<fieldset>
				<legend><?php _e('i_db_conf_prod_server') ?></legend>

				<?php foreach ($aPageData['drivers']->getDrivers() as $sDriver => $driver) : ?>
					<?php if (!$driver->isSupported()) continue; ?>

					<div data-driver-id="<?php echo $sDriver ?>" class="driver">
						<?php foreach ($driver->getConfigFields() as $aFields) : ?>
						<p class="field">
						<?php if ($aFields['type'] == 'integer') : ?>
							<label for="prod_<?php echo $sDriver ?>_<?php echo $aFields['id'] ?>"<?php
							if ($aFields['required']) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif ?>><?php
							echo $aFields['label'] ?></label>
							<?php echo form::text(['config[prod]['.$aFields['id'].']', 'prod_'.$sDriver.'_'.$aFields['id']], 10, 16, '') ?>

						<?php elseif ($aFields['type'] == 'boolean') : ?>
							<label for="prod_<?php echo $sDriver ?>_<?php echo $aFields['id'] ?>"><?php
							echo form::checkbox(['config[prod]['.$aFields['id'].']', 'prod_'.$sDriver.'_'.$aFields['id']], 1, '') ?>
							<?php echo $aFields['label'] ?></label>

						<?php else : ?>
							<label for="prod_<?php echo $sDriver ?>_<?php echo $aFields['id'] ?>"<?php
							if ($aFields['required']) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif ?>><?php
							echo $aFields['label'] ?></label>
							<?php echo form::text(['config[prod]['.$aFields['id'].']', 'prod_'.$sDriver.'_'.$aFields['id']], 40, 256, '') ?>

						<?php endif; ?>
						</p>
						<?php endforeach ?>
					</div>
				<?php endforeach ?>

			</fieldset>
		</div>

		<div id="dev-part" class="col">
			<fieldset>
				<legend><?php _e('i_db_conf_dev_server') ?></legend>

				<?php foreach ($aPageData['drivers']->getDrivers() as $sDriver => $driver) : ?>
					<?php if (!$driver->isSupported()) continue; ?>

					<div data-driver-id="<?php echo $sDriver ?>" class="driver">
						<?php foreach ($driver->getConfigFields() as $aFields) : ?>
						<p class="field">
						<?php if ($aFields['type'] == 'integer') : ?>
							<label for="dev_<?php echo $sDriver ?>_<?php echo $aFields['id'] ?>"<?php
							if ($aFields['required']) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif ?>><?php
							echo $aFields['label'] ?></label>
							<?php echo form::text(['config[dev]['.$aFields['id'].']', 'dev_'.$sDriver.'_'.$aFields['id']], 10, 16, '') ?>

						<?php elseif ($aFields['type'] == 'boolean') : ?>
							<label for="dev_<?php echo $sDriver ?>_<?php echo $aFields['id'] ?>"><?php
							echo form::checkbox(['config[dev]['.$aFields['id'].']', 'dev_'.$sDriver.'_'.$aFields['id']], 1, '') ?>
							<?php echo $aFields['label'] ?></label>

						<?php else : ?>
							<label for="dev_<?php echo $sDriver ?>_<?php echo $aFields['id'] ?>"<?php
							if ($aFields['required']) : ?> title="<?php _e('c_c_required_field') ?>" class="required"<?php endif ?>><?php
							echo $aFields['label'] ?></label>
							<?php echo form::text(['config[dev]['.$aFields['id'].']', 'dev_'.$sDriver.'_'.$aFields['id']], 40, 256, '') ?>

						<?php endif; ?>
						</p>
						<?php endforeach ?>
					</div>
				<?php endforeach ?>

			</fieldset>
		</div>
	</div>

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
	</p>
</form>
<?php endif; ?>
