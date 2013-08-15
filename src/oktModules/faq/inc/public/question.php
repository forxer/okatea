<?php
/**
 * @ingroup okt_module_faq
 * @brief "controller" pour l'affichage public d'une question
 *
 */

# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';


# récupération de la question en fonction du slug
$slug = !empty($_GET['slug']) ? $_GET['slug'] : null;


$faqQuestion = $okt->faq->getQuestions(array(
	'slug' => $slug,
	'visibility' => 1
//	'language' => $okt->user->language
));


if ($faqQuestion->isEmpty()) {
	$okt->page->serve404();
}


# récupération de l'internationalisation
$aLocalizedUrl = array();
$faqQuestionLocales = $okt->faq->getQuestionI18n($faqQuestion->id);


while ($faqQuestionLocales->fetch())
{
	$faqQuestionLocales->url = $faqQuestionLocales->getQuestionUrl($faqQuestionLocales->language);
	$aLocalizedUrl[$faqQuestionLocales->language] = $faqQuestionLocales->url;
}


# est-ce qu'on demande une langue bien précise ?
$sUserLanguage = !empty($_GET['language']) ? $_GET['language'] : $okt->user->language;

if (empty($_GET['language']) || $sUserLanguage != $okt->user->language)
{
	$okt->user->setUserLang($sUserLanguage);
	http::redirect($aLocalizedUrl[$sUserLanguage]);
}


# formatage des données
if ($faqQuestion->title_tag == '') {
	$faqQuestion->title_tag = $faqQuestion->title;
}


# module actuel
$okt->page->module = 'faq';
$okt->page->action = 'question';


$faqQuestion->url = $faqQuestion->getQuestionUrl();

if (!$okt->faq->config->enable_rte) {
	$faqQuestion->content = util::nlToP($faqQuestion->content);
}


# meta description
if ($faqQuestion->metadescription != '') {
	$okt->page->meta_description = $faqQuestion->metadescription;
}
else if ($okt->faq->config->meta_description[$okt->user->language] != '') {
	$okt->page->meta_description = $okt->faq->config->meta_description[$okt->user->language];
}
else {
	$okt->page->meta_description = util::getSiteMetaDesc();
}


# meta keywords
if ($faqQuestion->meta_keywords != '') {
	$okt->page->meta_keywords = $faqQuestion->meta_keywords;
}
else if ($okt->faq->config->meta_keywords[$okt->user->language] != '') {
	$okt->page->meta_keywords = $okt->faq->config->meta_keywords[$okt->user->language];
}
else {
	$okt->page->meta_keywords = util::getSiteMetaKeywords();
}


# récupération des images
$faqQuestion->images = $faqQuestion->getImagesInfo();


# récupération des fichiers
$faqQuestion->files = $faqQuestion->getFilesInfo();


# début du fil d'ariane
$okt->page->breadcrumb->add($okt->faq->getName(),$okt->faq->config->url);


# title tag du module
$okt->page->addTitleTag($okt->faq->getTitle());


# title tag du post
$okt->page->addTitleTag(($faqQuestion->title_tag != '' ? $faqQuestion->title_tag : $faqQuestion->title));


# fil d'ariane du post
$okt->page->breadcrumb->add($faqQuestion->title,$faqQuestion->url);

