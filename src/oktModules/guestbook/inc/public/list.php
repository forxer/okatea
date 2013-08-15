<?php
/**
 * @ingroup okt_module_guestbook
 * @brief "controller" pour l'affichage public du livre d'or
 *
 */



# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';

# est-ce qu'on demande une langue bien précise ?
$sUserLanguage = !empty($_GET['language']) ? $_GET['language'] : $okt->user->language;

if (empty($_GET['language']) || $sUserLanguage != $okt->user->language)
{
	$okt->user->setUserLang($sUserLanguage);
	http::redirect($okt->page->getBaseUrl($sUserLanguage).$okt->guestbook->config->public_url[$sUserLanguage]);
}


# module actuel
$okt->page->module = 'guestbook';
$okt->page->action = 'list';


# -- CORE TRIGGER : publicModuleGuestbookControllerStart
$okt->triggers->callTrigger('publicModuleGuestbookControllerStart', $okt, $okt->guestbook->config->captcha);

$aSigData = array(
	'language' 	=> $okt->user->language,
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
		'language' 	=> isset($_POST['language']) ? $_POST['language'] : $okt->user->language,
		'message' 	=> isset($_POST['msg']) ? $_POST['msg'] : null,
		'nom' 		=> isset($_POST['nom']) ? $_POST['nom'] : null,
		'email' 	=> isset($_POST['email']) ? $_POST['email'] : null,
		'url' 		=> isset($_POST['url']) ? $_POST['url'] : 'http://',
		'note' 		=> isset($_POST['note']) ? $_POST['note'] : null,
		'ip' 		=> http::realIP(),
		'visible'   => $okt->guestbook->config->validation ? 0 : 1
	);

	$aSigData = $okt->guestbook->handleUserData($aSigData);

	# -- CORE TRIGGER : publicModuleGuestbookControllerFormCheckValues
	$okt->triggers->callTrigger('publicModuleGuestbookControllerFormCheckValues', $okt);

	if (!$okt->error->hasError())
	{
		if ($okt->guestbook->addSig($aSigData))
		{
			if ($okt->guestbook->config->emails_list != '')
			{
				$oMail = new oktMail($okt);

				$oMail->setFrom();

				$oMail->message->setSubject('Nouveau message sur le livre d’or de '.util::getSiteTitle());

				$mail_body =
					'Bonjour,'."\n\n".
					'Un utilisateur a laissé un nouveau message '.
					'sur le livre d’or de "'.util::getSiteTitle().'".'."\n\n";

				if ($okt->guestbook->config->validation)
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

				$dests = array_map('trim',explode(',',$okt->guestbook->config->emails_list));
				$oMail->message->setTo($dests);

				$oMail->send();
			}

			http::redirect($okt->guestbook->config->url.'?added=1');
		}
	}
}


# signatures à afficher
$aGuestbookParams = array(
	'is_not_spam' => true,
	'is_visible' => true,
	'language' => $okt->user->language
);


# initialisation de la pagination
$iPage = !empty($_GET['page']) ? intval($_GET['page']) : 1;
$oGuestbookPager = new publicPager($iPage, $okt->guestbook->getSig($aGuestbookParams,true), $okt->guestbook->config->nbparpage_public);

$iNumPages = $oGuestbookPager->getNbPages();


# récupération des signatures
$aGuestbookParams['limit'] = (($iPage-1)*$okt->guestbook->config->nbparpage_public).','.$okt->guestbook->config->nbparpage_public;
$signaturesList = $okt->guestbook->getSig($aGuestbookParams);

$aLanguages = array();
foreach ($okt->languages->list as $aLanguage)
{
	if (isset($okt->guestbook->config->public_url[$aLanguage['title']])) {
		$aLanguages[$aLanguage['title']] = $aLanguage['title'];
	}
}

# formatage des données
$num_sig = 0;
while ($signaturesList->fetch())
{
	$signaturesList->number = ++$num_sig;

	# note
	if ($okt->guestbook->config->chp_note)
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
if ($okt->guestbook->config->meta_description[$okt->user->language] != '') {
	$okt->page->meta_description = $okt->guestbook->config->meta_description[$okt->user->language];
}
else {
	$okt->page->meta_description = util::getSiteMetaDesc();
}

# meta keywords
if ($okt->guestbook->config->meta_keywords[$okt->user->language] != '') {
	$okt->page->meta_keywords = $okt->guestbook->config->meta_keywords[$okt->user->language];
}
else {
	$okt->page->meta_keywords = util::getSiteMetaKeywords();
}


# ajout du numéro de page au title
if ($iPage > 1) {
	$okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$iPage));
}


# title tag
$okt->page->addTitleTag($okt->guestbook->getTitle());

# raccourcis
$signaturesList->numPages = $iNumPages;
$signaturesList->pager = $oGuestbookPager;
