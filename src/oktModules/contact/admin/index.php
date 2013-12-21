<?php
/**
 * @ingroup okt_module_contact
 * @brief Page de gestion des destinataires
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_recipients_to = !empty($_POST['p_recipients_to']) && is_array($_POST['p_recipients_to']) ? array_unique(array_filter(array_map('trim',$_POST['p_recipients_to']))) : array();
	$p_recipients_cc = !empty($_POST['p_recipients_cc']) && is_array($_POST['p_recipients_cc']) ? array_unique(array_filter(array_map('trim',$_POST['p_recipients_cc']))) : array();
	$p_recipients_bcc = !empty($_POST['p_recipients_bcc']) && is_array($_POST['p_recipients_bcc']) ? array_unique(array_filter(array_map('trim',$_POST['p_recipients_bcc']))) : array();

	foreach ($p_recipients_to as $mail)
	{
		if (!text::isEmail($mail)) {
			$okt->error->set(sprintf(__('m_contact_email_address_$s_is_invalid')), html::escapeHTML($mail));
		}
	}

	foreach ($p_recipients_cc as $mail)
	{
		if (!text::isEmail($mail)) {
			$okt->error->set(sprintf(__('m_contact_email_address_$s_is_invalid')), html::escapeHTML($mail));
		}
	}
	foreach ($p_recipients_bcc as $mail)
	{
		if (!text::isEmail($mail)) {
			$okt->error->set(sprintf(__('m_contact_email_address_$s_is_invalid')), html::escapeHTML($mail));
		}
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'recipients_to' => (array)$p_recipients_to,
			'recipients_cc' => (array)$p_recipients_cc,
			'recipients_bcc' => (array)$p_recipients_bcc,
		);

		try
		{
			$okt->contact->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=contact&action=index');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_recipients'));


$aRecipientsTo = !empty($okt->contact->config->recipients_to) ? (array)$okt->contact->config->recipients_to : array();
$aRecipientsCc = !empty($okt->contact->config->recipients_cc) ? (array)$okt->contact->config->recipients_cc : array();
$aRecipientsBcc = !empty($okt->contact->config->recipients_bcc) ? (array)$okt->contact->config->recipients_bcc : array();


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">

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


	<p><?php echo form::hidden(array('m'),'contact'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'index'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

