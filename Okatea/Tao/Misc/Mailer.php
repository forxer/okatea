<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Misc;

use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Themes\SimpleReplacements;

/**
 * La classe pour envoyer gentillement des emails
 *
 */
class Mailer
{
	protected $okt;

	public $message;
	public $transport;
	public $mailer;
	public $failures;

	public function __construct($okt, $subject = null, $body = null, $contentType = null, $charset = null)
	{
		$this->okt = $okt;

		$this->setTransport();

		$this->mailer = \Swift_Mailer::newInstance($this->transport);

		$this->message = \Swift_Message::newInstance($subject, $body, $contentType, $charset);
	}

	/**
	 * Define the transport method
	 * @param object $okt
	 */
	protected function setTransport()
	{
		switch ($this->okt->config->email['transport'])
		{
			default:
			case 'mail':
				$this->transport = \Swift_MailTransport::newInstance();
			break;

			case 'smtp':
				$this->transport = \Swift_SmtpTransport::newInstance(
					$this->okt->config->email['smtp']['host'],
					$this->okt->config->email['smtp']['port']
				);

				if (!empty($this->okt->config->email['smtp']['username'])) {
					$this->transport->setUsername($this->okt->config->email['smtp']['username']);
				}

				if (!empty($this->okt->config->email['smtp']['password'])) {
					$this->transport->setPassword($this->okt->config->email['smtp']['password']);
				}
			break;

			case 'sendmail':
				$command = '/usr/sbin/exim -bs';

				if (!empty($this->okt->config->email['sendmail'])) {
					$command = $this->okt->config->email['sendmail'];
				}

				$this->transport = \Swift_SendmailTransport::newInstance($command);
			break;
		}
	}

	public function setFrom($mFrom = null)
	{
		if ($mFrom !== null) {
			$this->message->setFrom($mFrom);
		}
		elseif (!empty($this->okt->config->email['name'])) {
			$this->message->setFrom(array($this->okt->config->email['from'] => Escaper::html($this->okt->config->email['name'])));
		}
		else {
			$this->message->setFrom($this->okt->config->email['from']);
		}
	}

	public function setSubject($sSubject)
	{
		$this->message->setSubject($sSubject);
	}

	public function setBody($body, $contentType = null, $charset = null)
	{
		$this->message->setBody($body, $contentType, $charset);
	}

	public function addPart($body, $contentType = null, $charset = null)
	{
		$this->message->addPart($body, $contentType, $charset);
	}

	/**
	 * Parse un fichier de template pour utiliser comme mail.
	 *
	 * @param $template_file
	 * @param $variables
	 * @return void
	 * @deprecated 2.0
	 */
	public function useFile($template_file, $variables=array())
	{
		$sMailText = SimpleReplacements::parseFile($template_file,$variables);

		list($sSubject, $sBody) = explode("\n", $sMailText, 2);

		$this->message->setSubject(trim(str_replace('Subject:','',$sSubject)));
		$this->message->setBody(trim($sBody));
	}

	/**
	 * Sends a message.
	 */
	public function send()
	{
		$iNumSended = $this->mailer->send($this->message, $this->failures);

		if ($iNumSended <= 0) {
			$this->okt->error->set(__('c_c_error_sending_email'));
		}

		if (!empty($this->failures))
		{
			foreach ($this->failures as $sMail) {
				$this->okt->error->set(sprintf(__('c_c_error_sending_email_to_%s'),$sMail));
			}
		}

		return $iNumSended;
	}

	/**
	 * Sends a separate message to each recipient in the To: field.
	 *
	 * Each recipient receives a message containing only their own address in the To: field.
	 */
	public function batchSend()
	{
		return $this->mailer->batchSend($this->message);
	}
}
