<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact\Admin\Controller;

use Okatea\Admin\Controller;

class Recipients extends Controller
{
	public function page()
	{
		$aRecipientsTo = !empty($okt->contact->config->recipients_to) ? $okt->contact->config->recipients_to : array();
		$aRecipientsCc = !empty($okt->contact->config->recipients_cc) ? $okt->contact->config->recipients_cc : array();
		$aRecipientsBcc = !empty($okt->contact->config->recipients_bcc) ? $okt->contact->config->recipients_bcc : array();

		if (!empty($_POST['form_sent']))
		{
			$p_recipients_to = !empty($_POST['p_recipients_to']) && is_array($_POST['p_recipients_to']) ? array_unique(array_filter(array_map('trim',$_POST['p_recipients_to']))) : array();
			$p_recipients_cc = !empty($_POST['p_recipients_cc']) && is_array($_POST['p_recipients_cc']) ? array_unique(array_filter(array_map('trim',$_POST['p_recipients_cc']))) : array();
			$p_recipients_bcc = !empty($_POST['p_recipients_bcc']) && is_array($_POST['p_recipients_bcc']) ? array_unique(array_filter(array_map('trim',$_POST['p_recipients_bcc']))) : array();

			foreach ($p_recipients_to as $mail)
			{
				if (!Utilities::isEmail($mail)) {
					$okt->error->set(sprintf(__('m_contact_email_address_$s_is_invalid')), html::escapeHTML($mail));
				}
			}

			foreach ($p_recipients_cc as $mail)
			{
				if (!Utilities::isEmail($mail)) {
					$okt->error->set(sprintf(__('m_contact_email_address_$s_is_invalid')), html::escapeHTML($mail));
				}
			}

			foreach ($p_recipients_bcc as $mail)
			{
				if (!Utilities::isEmail($mail)) {
					$okt->error->set(sprintf(__('m_contact_email_address_$s_is_invalid')), html::escapeHTML($mail));
				}
			}

			if ($okt->error->isEmpty())
			{
				$aNewConf = array(
					'recipients_to' => (array)$p_recipients_to,
					'recipients_cc' => (array)$p_recipients_cc,
					'recipients_bcc' => (array)$p_recipients_bcc,
				);

				try
				{
					$okt->contact->config->write($aNewConf);

					$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					http::redirect('module.php?m=contact&action=index');
				}
				catch (InvalidArgumentException $e)
				{
					$okt->error->set(__('c_c_error_writing_configuration'));
					$okt->error->set($e->getMessage());
				}
			}
		}

	}
}
