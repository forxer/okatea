<?php
/**
 * @ingroup okt_module_guestbook
 * @brief Controller public.
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Core\Controller;
use Tao\Misc\Mailer;
use Tao\Website\Pager;

class guestbookController extends Controller
{
	/**
	 * Affichage de la page guestbook.
	 *
	 */
	public function guestbookPage()
	{
		# module actuel
		$this->page->module = 'guestbook';
		$this->page->action = 'list';

		# -- CORE TRIGGER : publicModuleGuestbookControllerStart
		$this->okt->triggers->callTrigger('publicModuleGuestbookControllerStart', $this->okt, $this->okt->guestbook->config->captcha);

		$aSigData = array(
			'language' 	=> $this->okt->user->language,
			'message' 	=> '',
			'nom' 		=> '',
			'email' 	=> '',
			'url' 		=> 'http://',
			'note' 		=> 'nc'
		);

		# formulaire envoyé
		if (!empty($_POST['sign']))
		{
			$aSigData = array(
				'language' 	=> isset($_POST['language']) ? $_POST['language'] : $this->okt->user->language,
				'message' 	=> isset($_POST['msg']) ? $_POST['msg'] : null,
				'nom' 		=> isset($_POST['nom']) ? $_POST['nom'] : null,
				'email' 	=> isset($_POST['email']) ? $_POST['email'] : null,
				'url' 		=> isset($_POST['url']) ? $_POST['url'] : 'http://',
				'note' 		=> isset($_POST['note']) ? $_POST['note'] : null,
				'ip' 		=> http::realIP(),
				'visible'   => $this->okt->guestbook->config->validation ? 0 : 1
			);

			$aSigData = $this->okt->guestbook->handleUserData($aSigData);

			# -- CORE TRIGGER : publicModuleGuestbookControllerFormCheckValues
			$this->okt->triggers->callTrigger('publicModuleGuestbookControllerFormCheckValues', $this->okt, $this->okt->guestbook->config->captcha);

			if (!$this->okt->error->hasError())
			{
				if ($this->okt->guestbook->addSig($aSigData))
				{
					if ($this->okt->guestbook->config->emails_list != '')
					{
						$oMail = new Mailer($this->okt);

						$oMail->setFrom();

						$oMail->message->setSubject('Nouveau message sur le livre d’or de '.$this->page->getSiteTitle());

						$mail_body =
							'Bonjour,'."\n\n".
							'Un utilisateur a laissé un nouveau message '.
							'sur le livre d’or de "'.$this->page->getSiteTitle().'".'."\n\n";

						if ($this->okt->guestbook->config->validation)
						{
							$mail_body .=
								'Ce nouveau message peut être validé '.
								'en vous rendant sur l’administration.'."\n\n";
						}

						$mail_body .=
							'Cordialement'.PHP_EOL.PHP_EOL.
							'--'.PHP_EOL.
							'Email automatique,'.PHP_EOL.
							'ne repondez pas à ce message';

						$oMail->message->setBody($mail_body);

						$dests = array_map('trim',explode(',',$this->okt->guestbook->config->emails_list));
						$oMail->message->setTo($dests);

						$oMail->send();
					}

					return $this->redirect($this->okt->guestbook->config->url.'?added=1');
				}
			}
		}

		# signatures à afficher
		$aGuestbookParams = array(
			'is_not_spam' => true,
			'is_visible' => true,
			'language' => $this->okt->user->language
		);

		# initialisation de la pagination
		$iPage = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$oGuestbookPager = new Pager($iPage, $this->okt->guestbook->getSig($aGuestbookParams,true), $this->okt->guestbook->config->nbparpage_public);

		$iNumPages = $oGuestbookPager->getNbPages();

		# récupération des signatures
		$aGuestbookParams['limit'] = (($iPage-1)*$this->okt->guestbook->config->nbparpage_public).','.$this->okt->guestbook->config->nbparpage_public;
		$signaturesList = $this->okt->guestbook->getSig($aGuestbookParams);

		$aLanguages = array();
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (isset($this->okt->guestbook->config->public_url[$aLanguage['code']])) {
				$aLanguages[$aLanguage['title']] = $aLanguage['code'];
			}
		}

		# formatage des données
		$num_sig = 0;
		while ($signaturesList->fetch())
		{
			$signaturesList->number = ++$num_sig;

			# note
			if ($this->okt->guestbook->config->chp_note)
			{
				if (!is_numeric($signaturesList->note)) {
					$signaturesList->note = 'nc';
				}
				else {
					$signaturesList->note = ceil($signaturesList->note).'/20';
				}
			}
			else {
				$signaturesList->note = null;
			}
		}

		# meta description
		if ($this->okt->guestbook->config->meta_description[$this->okt->user->language] != '') {
			$this->page->meta_description = $this->okt->guestbook->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->guestbook->config->meta_keywords[$this->okt->user->language] != '') {
			$this->page->meta_keywords = $this->okt->guestbook->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($iPage > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'),$iPage));
		}

		# title tag
		$this->page->addTitleTag($this->okt->guestbook->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->guestbook->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->guestbook->getNameSeo());

		# fil d'ariane de la page
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__)) {
			$this->page->breadcrumb->add($this->okt->guestbook->getName(), $this->okt->guestbook->config->url);
		}

		# raccourcis
		$signaturesList->numPages = $iNumPages;
		$signaturesList->pager = $oGuestbookPager;

		# affichage du template
		return $this->render('guestbook_tpl', array(
			'aSigData' => $aSigData,
			'signaturesList' => $signaturesList,
			'aLanguages' => $aLanguages
		));
	}

}
