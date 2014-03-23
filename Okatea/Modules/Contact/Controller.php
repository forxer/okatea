<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact;

use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Mailer;
use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Controller as BaseController;

class Controller extends BaseController
{
	/**
	 * Affichage de la page contact.
	 *
	 */
	public function contactPage()
	{
		# -- CORE TRIGGER : publicModuleContactControllerStart
		$this->okt->triggers->callTrigger('publicModuleContactControllerStart', $this->okt->module('Contact')->config->captcha);

		# liste des champs
		$this->okt->module('Contact')->rsFields = $this->okt->module('Contact')->fields->getFields(array(
			'active' => true,
			'language' => $this->okt->user->language
		));

		# -- CORE TRIGGER : publicModuleContactControllerBeforeFieldsValues
		$this->okt->triggers->callTrigger('publicModuleContactControllerBeforeInitFieldsValues');

		# intitialisation des données des champs
		while ($this->okt->module('Contact')->rsFields->fetch())
		{
			switch ($this->okt->module('Contact')->rsFields->type)
			{
				default:
				case 1 : # Champ texte
				case 2 : # Zone de texte
					$this->okt->module('Contact')->aPostedData[$this->okt->module('Contact')->rsFields->id] =
						!empty($_REQUEST[$this->okt->module('Contact')->rsFields->html_id])
						? $_REQUEST[$this->okt->module('Contact')->rsFields->html_id]
						: $this->okt->module('Contact')->rsFields->value;
				break;

				case 3 : # Menu déroulant
					$this->okt->module('Contact')->aPostedData[$this->okt->module('Contact')->rsFields->id] =
						isset($_REQUEST[$this->okt->module('Contact')->rsFields->html_id])
						? $_REQUEST[$this->okt->module('Contact')->rsFields->html_id]
						: '';
				break;

				case 4 : # Boutons radio
					$this->okt->module('Contact')->aPostedData[$this->okt->module('Contact')->rsFields->id] =
						isset($_REQUEST[$this->okt->module('Contact')->rsFields->html_id])
						? $_REQUEST[$this->okt->module('Contact')->rsFields->html_id]
						: '';
				break;

				case 5 : # Cases à cocher
					$this->okt->module('Contact')->aPostedData[$this->okt->module('Contact')->rsFields->id] =
						!empty($_REQUEST[$this->okt->module('Contact')->rsFields->html_id]) && is_array($_REQUEST[$this->okt->module('Contact')->rsFields->html_id])
						? $_REQUEST[$this->okt->module('Contact')->rsFields->html_id]
						: array();
				break;
			}
		}

		# -- CORE TRIGGER : publicModuleContactControllerAfterInitFieldsValues
		$this->okt->triggers->callTrigger('publicModuleContactControllerAfterInitFieldsValues');

		# formulaire envoyé
		if (!empty($_POST['send']))
		{
			# vérification des champs obligatoires
			while ($this->okt->module('Contact')->rsFields->fetch())
			{
				if ($this->okt->module('Contact')->rsFields->active == 2 && empty($this->okt->module('Contact')->aPostedData[$this->okt->module('Contact')->rsFields->id])) {
					$this->okt->error->set('Vous devez renseigner le champ "'.Escaper::html($this->okt->module('Contact')->rsFields->title).'".');
				}
				elseif ($this->okt->module('Contact')->rsFields->id == 4 && !Utilities::isEmail($this->okt->module('Contact')->aPostedData[4])) {
					$this->okt->error->set('Veuillez saisir une adresse email valide.');
				}
			}

			# -- CORE TRIGGER : publicModuleContactControllerFormCheckValues
			$this->okt->triggers->callTrigger('publicModuleContactControllerFormCheckValues', $this->okt->module('Contact')->config->captcha);

			# si on as pas d'erreur on se préparent à envoyer le mail
			if ($this->okt->error->isEmpty())
			{
				$oMail = new Mailer($this->okt);

				# -- CORE TRIGGER : publicModuleContactBeforeBuildMail
				$this->okt->triggers->callTrigger('publicModuleContactBeforeBuildMail', $oMail);

				# from to & reply to
				if ($this->okt->module('Contact')->config->from_to == 'website')
				{
					$oMail->setFrom();

					$oMail->message->setReplyTo($this->okt->module('Contact')->getReplyTo());
				}
				else {
					$oMail->message->setFrom($this->okt->module('Contact')->getFromTo());
				}

				# sujet
				$oMail->message->setSubject($this->okt->module('Contact')->getSubject());

				# corps du message
				$oMail->message->setBody($this->okt->module('Contact')->getBody());

				# destinataires
				$oMail->message->setTo($this->okt->module('Contact')->getRecipientsTo());

				# destinataires en copie
				$aRecipientsCc = $this->okt->module('Contact')->getRecipientsCc();
				if (!empty($aRecipientsCc)) {
					$oMail->message->setCc($aRecipientsCc);
				}

				# destinataires en copie cachée
				$aRecipientsBc = $this->okt->module('Contact')->getRecipientsBcc();
				if (!empty($aRecipientsBc)) {
					$oMail->message->setBcc($aRecipientsBc);
				}

				# -- CORE TRIGGER : publicModuleContactBeforeSendMail
				$this->okt->triggers->callTrigger('publicModuleContactBeforeSendMail', $oMail);

				if ($oMail->send())
				{
					# -- CORE TRIGGER : publicModuleContactAfterMailSent
					$this->okt->triggers->callTrigger('publicModuleContactAfterMailSent', $oMail);

					return $this->redirect($this->generateUrl('contactPage').'?sended=1');
				}
			}
		}

		# meta description
		if (!empty($this->okt->module('Contact')->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->module('Contact')->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->module('Contact')->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->module('Contact')->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->module('Contact')->getTitle());

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add($this->okt->module('Contact')->getName(), $this->generateUrl('contactPage'));
		}

		# titre de la page
		$this->page->setTitle($this->okt->module('Contact')->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->module('Contact')->getNameSeo());

		# affichage du template
		return $this->render('contact/contact/'.$this->okt->module('Contact')->config->templates['contact']['default'].'/template');
	}

	/**
	 * Affichage de la page du plan d'accès.
	 *
	 */
	public function contactMapPage()
	{
		# si la page n'est pas active -> 404
		if (!$this->okt->module('Contact')->config->google_map['enable']) {
			return $this->serve404();
		}

		# module actuel
		$this->page->module = 'contact';
		$this->page->action = 'map';

		# meta description
		if (!empty($this->okt->module('Contact')->config->meta_description_map[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->module('Contact')->config->meta_description_map[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->module('Contact')->config->meta_keywords_map[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->module('Contact')->config->meta_keywords_map[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# title tag de la page
		$sTitle = null;
		if (isset($this->okt->module('Contact')->config->title_map[$this->okt->user->language])) {
			$sTitle = $this->okt->module('Contact')->config->title_map[$this->okt->user->language];
		}
		elseif ($this->okt->module('Contact')->config->title_map[$this->okt->config->language]) {
			$sTitle = $this->okt->module('Contact')->config->title_map[$this->okt->config->language];
		}
		$this->page->addTitleTag($sTitle);

		# titre de la page
		$sName = null;
		if (isset($this->okt->module('Contact')->config->name_map[$this->okt->user->language])) {
			$sName = $this->okt->module('Contact')->config->name_map[$this->okt->user->language];
		}
		elseif ($this->okt->module('Contact')->config->name_map[$this->okt->config->language]) {
			$sName = $this->okt->module('Contact')->config->name_map[$this->okt->config->language];
		}
		$this->page->setTitle($sName);

		# titre SEO de la page
		$sNameSeo = null;
		if (isset($this->okt->module('Contact')->config->name_seo_map[$this->okt->user->language])) {
			$sNameSeo = $this->okt->module('Contact')->config->name_seo_map[$this->okt->user->language];
		}
		elseif ($this->okt->module('Contact')->config->name_seo_map[$this->okt->config->language]) {
			$sNameSeo = $this->okt->module('Contact')->config->name_seo_map[$this->okt->config->language];
		}
		$this->page->setTitleSeo($sNameSeo);

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add($sName, $this->generateUrl('contactMapPage'));
		}

		# affichage du template
		return $this->render('contact/map/'.$this->okt->module('Contact')->config->templates['map']['default'].'/template');
	}

}
