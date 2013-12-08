<?php
/**
 * @ingroup okt_module_faq
 * @brief Controller public.
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Core\Controller;
use Tao\Website\Pager;

class faqController extends Controller
{
	/**
	 * Affichage de la liste des questions.
	 *
	 */
	public function faqList()
	{
		# module actuel
		$this->okt->page->module = 'faq';
		$this->okt->page->action = 'list_questions';

		# paramètres de base de selection des articles
		$aFaqParams = array(
			'visibility' => 1,
			'language' => $this->okt->user->language
		);

		# initialisation des filtres
		$this->okt->faq->filtersStart();

		# ré-initialisation filtres
		if (!empty($_GET['init_filters']))
		{
			$this->okt->faq->filters->initFilters();
			http::redirect($this->okt->faq->config->url);
		}

		# initialisation des filtres
		$this->okt->faq->filters->setQuestionsParams($aFaqParams);
		$this->okt->faq->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredQuestions = $this->okt->faq->getQuestions($aFaqParams,true);

		$oFaqPager = new Pager($this->okt->faq->filters->params->page, $iNumFilteredQuestions, $this->okt->faq->filters->params->nb_per_page);

		$iNumPages = $oFaqPager->getNbPages();

		$this->okt->faq->filters->normalizePage($iNumPages);

		$aFaqParams['limit'] = (($this->okt->faq->filters->params->page-1)*$this->okt->faq->filters->params->nb_per_page).','.$this->okt->faq->filters->params->nb_per_page;

		# récupération des questions
		$faqList = $this->okt->faq->getQuestions($aFaqParams);

		$count_line = 0;
		while ($faqList->fetch())
		{
			$faqList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
			$count_line++;

			$faqList->url = $faqList->getQuestionUrl();

			if (!$this->okt->faq->config->enable_rte) {
				$faqList->content = util::nlToP($faqList->content);
			}

			if ($this->okt->faq->config->public_truncat_char > 0 )
			{
				$faqList->content = html::clean($faqList->content);
				$faqList->content = text::cutString($faqList->content,$this->okt->faq->config->public_truncat_char);
			}
		}
		unset($count_line);

		# fil d'ariane
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__)) {
			$this->okt->page->breadcrumb->add($this->okt->faq->getName(),$this->okt->faq->config->url);
		}

		# ajout du numéro de page au title
		if ($this->okt->faq->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->faq->filters->params->page));
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->faq->getTitle());

		# titre de la page
		$this->okt->page->setTitle($this->okt->faq->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->faq->getNameSeo());

		# raccourcis
		$faqList->numPages = $iNumPages;
		$faqList->pager = $oFaqPager;

		# affichage du template
		if ($this->okt->faq->config->enable_categories) {
			$sTemplatename = 'faq_list_questions_with_categories_tpl';
		} else {
			$sTemplatename = 'faq_list_questions_tpl';
		}

		echo $this->okt->tpl->render($sTemplatename, array(
			'faqList' => $faqList
		));
	}

	/**
	 * Affichage d'une question.
	 *
	 */
	public function faqQuestion($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'faq';
		$this->okt->page->action = 'question';

		# récupération de la question en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		$faqQuestion = $this->okt->faq->getQuestions(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($faqQuestion->isEmpty()) {
			$this->serve404();
		}

		# formatage des données
		if ($faqQuestion->title_tag == '') {
			$faqQuestion->title_tag = $faqQuestion->title;
		}

		$faqQuestion->url = $faqQuestion->getQuestionUrl();

		if (!$this->okt->faq->config->enable_rte) {
			$faqQuestion->content = util::nlToP($faqQuestion->content);
		}

		# meta description
		if ($faqQuestion->metadescription != '') {
			$this->okt->page->meta_description = $faqQuestion->metadescription;
		}
		else if ($this->okt->faq->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->faq->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($faqQuestion->meta_keywords != '') {
			$this->okt->page->meta_keywords = $faqQuestion->meta_keywords;
		}
		else if ($this->okt->faq->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->faq->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# récupération des images
		$faqQuestion->images = $faqQuestion->getImagesInfo();

		# récupération des fichiers
		$faqQuestion->files = $faqQuestion->getFilesInfo();

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->faq->getTitle());

		# title tag du post
		$this->okt->page->addTitleTag(($faqQuestion->title_tag != '' ? $faqQuestion->title_tag : $faqQuestion->title));

		# titre de la page
		$this->okt->page->setTitle($faqQuestion->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo(!empty($faqQuestion->title_seo) ? $faqQuestion->title_seo : $faqQuestion->title);

		# fil d'ariane du post
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__, $slug))
		{
			$this->okt->page->breadcrumb->add($this->okt->faq->getName(), $this->okt->faq->config->url);

			$this->okt->page->breadcrumb->add($faqQuestion->title, $faqQuestion->url);
		}

		# affichage du template
		echo $this->okt->tpl->render('faq_question_tpl', array(
			'faqQuestion' => $faqQuestion,
			'faqQuestionLocales' => $faqQuestionLocales
		));
	}

} # class
