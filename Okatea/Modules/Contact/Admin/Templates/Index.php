<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');


# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_recipients'));

?>

<form action="<?php $view->generateUrl('Contact_index') ?>" method="post">

	<p><?php _e('m_contact_recipients_page_description')?></p>

	<p><?php printf(__('m_contact_default_recipient_%s'), $okt->config->email['to'])?></p>

	<p><?php _e('m_contact_copy_hidden_copy')?></p>

	<p><?php _e('m_contact_howto_delete_recipent')?></p>

	<h3><?php _e('m_contact_Recipients')?></h3>

		<?php $line_count = 0;

		foreach ($aRecipientsTo as $sRecipient) :
			$line_count++; ?>

		<p class="field"><label for="p_recipients_to_<?php echo $line_count ?>"><?php _e('m_contact_Recipient')?> <?php echo $line_count ?></label>
		<?php echo form::text(array('p_recipients_to[]','p_recipients_to_'.$line_count), 60, 255, html::escapeHTML($sRecipient)) ?></p>

		<?php endforeach; ?>

		<p class="field"><label for="p_recipients_to_<?php echo ($line_count+1) ?>"><?php _e('m_contact_Add_recipient')?></label>
		<?php echo form::text(array('p_recipients_to[]','p_recipients_to_'.($line_count+1)), 60, 255) ?></p>

	<h3><?php _e('m_contact_Copy')?></h3>

		<?php $line_count = 0;

		foreach ($aRecipientsCc as $destinataire) :
			$line_count++; ?>

		<p class="field"><label for="p_recipients_cc_<?php echo $line_count ?>"><?php _e('m_contact_Copy')?> <?php echo $line_count ?></label>
		<?php echo form::text(array('p_recipients_cc[]','p_recipients_cc_'.$line_count), 60, 255, html::escapeHTML($destinataire)) ?></p>

		<?php endforeach; ?>

		<p class="field"><label for="p_recipients_cc_<?php echo ($line_count+1) ?>"><?php _e('m_contact_copy_recipient')?></label>
		<?php echo form::text(array('p_recipients_cc[]','p_recipients_cc_'.($line_count+1)), 60, 255) ?></p>

	<h3><?php _e('m_contact_hidden_copy')?></h3>

		<?php $line_count = 0;

		foreach ($aRecipientsBcc as $destinataire) :
			$line_count++; ?>

		<p class="field"><label for="p_recipients_bcc_<?php echo $line_count ?>"><?php _e('m_contact_hidden_copy')?> <?php echo $line_count ?></label>
		<?php echo form::text(array('p_recipients_bcc[]','p_recipients_bcc_'.$line_count), 60, 255, html::escapeHTML($destinataire)) ?></p>

		<?php $odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		endforeach; ?>

		<p class="field"><label for="p_recipients_bcc_<?php echo ($line_count+1) ?>"><?php _e('m_contact_hidden_copy_recipient')?></label>
		<?php echo form::text(array('p_recipients_bcc[]','p_recipients_bcc_'.($line_count+1)), 60, 255) ?></p>


	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>
