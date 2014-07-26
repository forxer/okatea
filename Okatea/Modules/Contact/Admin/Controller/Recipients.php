<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Contact\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Utilities;

class Recipients extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('contact_usage') || ! $this->okt->checkPerm('contact_recipients'))
		{
			return $this->serve401();
		}
		
		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.recipients');
		
		$aRecipientsTo = ! empty($this->okt->module('Contact')->config->recipients_to) ? $this->okt->module('Contact')->config->recipients_to : array();
		$aRecipientsCc = ! empty($this->okt->module('Contact')->config->recipients_cc) ? $this->okt->module('Contact')->config->recipients_cc : array();
		$aRecipientsBcc = ! empty($this->okt->module('Contact')->config->recipients_bcc) ? $this->okt->module('Contact')->config->recipients_bcc : array();
		
		if ($this->request->request->has('form_sent'))
		{
			$aRecipientsTo = array_unique(array_filter(array_map('trim', $this->request->request->get('p_recipients_to', array()))));
			$aRecipientsCc = array_unique(array_filter(array_map('trim', $this->request->request->get('p_recipients_cc', array()))));
			$aRecipientsBcc = array_unique(array_filter(array_map('trim', $this->request->request->get('p_recipients_bcc', array()))));
			
			foreach ($aRecipientsTo as $mail)
			{
				if (! Utilities::isEmail($mail))
				{
					$this->okt->error->set(sprintf(__('m_contact_recipients_email_address_$s_is_invalid'), Escaper::html($mail)));
				}
			}
			
			foreach ($aRecipientsCc as $mail)
			{
				if (! Utilities::isEmail($mail))
				{
					$this->okt->error->set(sprintf(__('m_contact_recipients_email_address_$s_is_invalid'), Escaper::html($mail)));
				}
			}
			
			foreach ($aRecipientsBcc as $mail)
			{
				if (! Utilities::isEmail($mail))
				{
					$this->okt->error->set(sprintf(__('m_contact_recipients_email_address_$s_is_invalid'), Escaper::html($mail)));
				}
			}
			
			if ($this->okt->error->isEmpty())
			{
				$aNewConf = array(
					'recipients_to' => $aRecipientsTo,
					'recipients_cc' => $aRecipientsCc,
					'recipients_bcc' => $aRecipientsBcc
				);
				
				$this->okt->module('Contact')->config->write($aNewConf);
				
				$this->okt->flash->success(__('c_c_confirm_configuration_updated'));
				
				return $this->redirect($this->generateUrl('Contact_index'));
			}
		}
		
		return $this->render('Contact/Admin/Templates/Index', array(
			'aRecipientsTo' => $aRecipientsTo,
			'aRecipientsCc' => $aRecipientsCc,
			'aRecipientsBcc' => $aRecipientsBcc
		));
	}
}
