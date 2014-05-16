<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

# Checkboxes helper
$okt->page->checkboxHelper($sFormId, 'checkboxHelper');

?>

<div id="form-list-batches">
	<p id="checkboxHelper"></p>
	<p id="actionsChoices">
		<label for="action"><?php echo $sActionsLabel?>
	<?php echo form::select('action', $aActionsChoices) ?></label>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_ok') ?>" />
	</p>
</div>
