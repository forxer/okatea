<?php
/**
 * @ingroup okt_module_faq
 * @brief "controller" pour l'affichage public des questions
 *
 */

use Tao\Utils as util;

# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';


# est-ce qu'on demande une langue bien précise ?
$sUserLanguage = !empty($_GET['language']) ? $_GET['language'] : $okt->user->language;

if (empty($_GET['language']) || $sUserLanguage != $okt->user->language)
{
	$okt->user->setUserLang($sUserLanguage);
	http::redirect($okt->page->getBaseUrl().$okt->faq->config->public_faq_url[$sUserLanguage]);
}


# paramètres de base de selection des articles
$aFaqParams = array(
	'visibility' => 1,
	'language' => $sUserLanguage
);


# initialisation des filtres
$okt->faq->filtersStart();


# ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->faq->filters->initFilters();
	http::redirect($okt->faq->config->url);
}


# paramètres personnalisés
if (!empty($aFaqCustomParams) && is_array($aFaqCustomParams)) {
	$aFaqParams = array_merge($aFaqParams,$aFaqCustomParams);
}


# initialisation des filtres
$okt->faq->filters->setQuestionsParams($aFaqParams);
$okt->faq->filters->getFilters();


# initialisation de la pagination
$iNumFilteredQuestions = $okt->faq->getQuestions($aFaqParams,true);

$oFaqPager = new publicPager($okt->faq->filters->params->page, $iNumFilteredQuestions, $okt->faq->filters->params->nb_per_page);

$iNumPages = $oFaqPager->getNbPages();

$okt->faq->filters->normalizePage($iNumPages);

$aFaqParams['limit'] = (($okt->faq->filters->params->page-1)*$okt->faq->filters->params->nb_per_page).','.$okt->faq->filters->params->nb_per_page;


# récupération des questions
$faqList = $okt->faq->getQuestions($aFaqParams);

$count_line = 0;
while ($faqList->fetch())
{
	$faqList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
	$count_line++;

	$faqList->url = $faqList->getQuestionUrl();

	if (!$okt->faq->config->enable_rte) {
		$faqList->content = util::nlToP($faqList->content);
	}

	if ($okt->faq->config->public_truncat_char > 0 )
	{
		$faqList->content = html::clean($faqList->content);
		$faqList->content = text::cutString($faqList->content,$okt->faq->config->public_truncat_char);
	}
}
unset($count_line);


# module actuel
$okt->page->module = 'faq';
$okt->page->action = 'list_questions';


# début du fil d'ariane
$okt->page->breadcrumb->add($okt->faq->getName(),$okt->faq->config->url);


# ajout du numéro de page au title
if ($okt->faq->filters->params->page > 1) {
	$okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$okt->faq->filters->params->page));
}


# title tag du module
$okt->page->addTitleTag($okt->faq->getTitle());

$faqList->numPages = $iNumPages;
$faqList->pager = $oFaqPager;
