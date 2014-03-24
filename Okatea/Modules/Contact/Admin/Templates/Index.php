<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Module title tag
$okt->page->addTitleTag($okt->module('Contact')->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->module('Contact')->getName(), $view->generateUrl('Contact_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_recipients'));

?>

<form action="<?php $view->generateUrl('Contact_index') ?>" method="post">

	<p><?php _e('m_contact_recipients_page_description') ?></p>

	<p><?php printf(__('m_contact_recipients_default_recipient_%s'), $okt->config->email['to'])?></p>

	<p><?php _e('m_contact_recipients_copy_hidden_copy') ?></p>

	<p><?php _e('m_contact_recipients_howto_delete_recipent') ?></p>

	<h3><?php _e('m_contact_recipients') ?></h3>

		<?php $iLineCount = 0;

		foreach ($aRecipientsTo as $sRecipient) :
			$iLineCount++; ?>

		<p class="field"><label for="p_recipients_to_<?php echo $iLineCount ?>"><?php printf(__('m_contact_recipients_recipient_%s'), $iLineCount) ?></label>
		<?php echo form::text(array('p_recipients_to[]','p_recipients_to_'.$iLineCount), 60, 255, $view->escape($sRecipient)) ?></p>

		<?php endforeach; ?>

		<p class="field"><label for="p_recipients_to_<?php echo ($iLineCount+1) ?>"><?php _e('m_contact_recipients_add_recipient') ?></label>
		<?php echo form::text(array('p_recipients_to[]','p_recipients_to_'.($iLineCount+1)), 60, 255) ?></p>

	<h3><?php _e('m_contact_recipients_copy') ?></h3>

		<?php $iLineCount = 0;

		foreach ($aRecipientsCc as $sRecipient) :
			$iLineCount++; ?>

		<p class="field"><label for="p_recipients_cc_<?php echo $iLineCount ?>"><?php printf(__('m_contact_recipients_copy_%s'), $iLineCount) ?></label>
		<?php echo form::text(array('p_recipients_cc[]', 'p_recipients_cc_'.$iLineCount), 60, 255, $view->escape($sRecipient)) ?></p>

		<?php endforeach; ?>

		<p class="field"><label for="p_recipients_cc_<?php echo ($iLineCount+1) ?>"><?php _e('m_contact_recipients_add_copy') ?></label>
		<?php echo form::text(array('p_recipients_cc[]', 'p_recipients_cc_'.($iLineCount+1)), 60, 255) ?></p>

	<h3><?php _e('m_contact_recipients_hidden_copy')?></h3>

		<?php $iLineCount = 0;

		foreach ($aRecipientsBcc as $sRecipient) :
			$iLineCount++; ?>

		<p class="field"><label for="p_recipients_bcc_<?php echo $iLineCount ?>"><?php printf(__('m_contact_recipients_hidden_copy_%s'), $iLineCount) ?></label>
		<?php echo form::text(array('p_recipients_bcc[]', 'p_recipients_bcc_'.$iLineCount), 60, 255, $view->escape($sRecipient)) ?></p>

		<?php endforeach; ?>

		<p class="field"><label for="p_recipients_bcc_<?php echo ($iLineCount+1) ?>"><?php _e('m_contact_recipients_add_hidden_copy') ?></label>
		<?php echo form::text(array('p_recipients_bcc[]', 'p_recipients_bcc_'.($iLineCount+1)), 60, 255) ?></p>


	<p><?php echo form::hidden('form_sent', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>
