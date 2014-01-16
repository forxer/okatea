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
	$("#p_update_enabled").button();

	$("#update_type_choice").buttonset();
');

?>

<h3><?php _e('c_a_config_advanced_tab_update') ?></h3>

<p><?php echo form::checkbox('p_update_enabled', 1, $aPageData['values']['update_enabled']) ?>
<label for="p_update_enabled"><?php _e('c_a_config_advanced_enable_update') ?></label></p>

<p class="field"><label for="p_update_url"><?php _e('c_a_config_advanced_update_url') ?></label>
<?php echo form::text('p_update_url', 60, 255, $view->escape($aPageData['values']['update_url'])) ?></p>

<p id="update_type_choice">
	<?php echo form::radio(array('p_update_type','p_update_type_stable'),'stable',($aPageData['values']['update_type'] == 'stable') ) ?>
	<label for="p_update_type_stable"><strong><?php _e('c_a_config_advanced_update_stable') ?></strong></label>

	<?php echo form::radio(array('p_update_type','p_update_type_dev'),'dev',($aPageData['values']['update_type'] == 'dev') ) ?>
	<label for="p_update_type_dev"><?php _e('c_a_config_advanced_update_dev') ?></label>
</p>
