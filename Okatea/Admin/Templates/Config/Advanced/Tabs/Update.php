<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

# Buttons
$okt->page->js->addReady('
	$("#p_updates_enabled").button();

	$("#updates_type_choice").buttonset();
');

?>

<h3><?php _e('c_a_config_advanced_tab_update') ?></h3>

<p><?php echo form::checkbox('p_updates_enabled', 1, $aPageData['values']['updates']['enabled'])?>
<label for="p_updates_enabled"><?php _e('c_a_config_advanced_enable_update') ?></label>
</p>

<p class="field">
	<label for="p_updates_url"><?php _e('c_a_config_advanced_update_url') ?></label>
<?php echo form::text('p_updates_url', 60, 255, $view->escape($aPageData['values']['updates']['url'])) ?></p>

<p id="updates_type_choice">
	<?php echo form::radio(array('p_updates_type','p_updates_type_stable'),'stable',($aPageData['values']['updates']['type'] == 'stable') )?>
	<label for="p_updates_type_stable"><strong><?php _e('c_a_config_advanced_update_stable') ?></strong></label>

	<?php echo form::radio(array('p_updates_type','p_updates_type_dev'),'dev',($aPageData['values']['updates']['type'] == 'dev') )?>
	<label for="p_updates_type_dev"><?php _e('c_a_config_advanced_update_dev') ?></label>
</p>
