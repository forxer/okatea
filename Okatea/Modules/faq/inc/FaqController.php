<?php
/**
 * @ingroup okt_module_faq
 * @brief Controller public.
 *
 */

use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Controller;
use Okatea\Website\Pager;

class FaqController extends Controller
{
	/**
	 * Affichage de la liste des questions.
	 *
	 */
	public function faqList()
	{
		# module actuel
		$this->page->module = 'faq';
		$this->page->action = 'list';

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
			return $this->redirect(FaqHelpers::getFaqUrl());
		}

		# initialisation des filtres
		$this->okt->faq->filters->setQuestionsParams($aFaqParams);
		$this->okt->faq->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredQuestions = $this->okt->faq->getQuestions($aFaqParams,true);

		$oFaqPager = new Pager($this->okt, $this->okt->faq->filters->params->page, $iNumFilteredQuestions, $this->okt->faq->filters->params->nb_per_page);

		$iNumPages = $oFaqPager->getNbPages();

		$this->okt->faq->filters->normalizePage($iNumPages);

		$aFaqParams['limit'] = (($this->okt->faq->filters->params->page-1)*$this->okt->faq->filters->params->nb_per_page).','.$this->okt->faq->filters->params->nb_per_page;

		# récupération des questions
		$this->rsQuestionsList = $this->okt->faq->getQuestions($aFaqParams);

		$count_line = 0;
		while ($this->rsQuestionsList->fetch())
		{
			$this->rsQuestionsList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
			$count_line++;

			$this->rsQuestionsList->url = $this->rsQuestionsList->getQuestionUrl();

			if (!$this->okt->faq->config->enable_rte) {
				$this->rsQuestionsList->content = Utilities::nlToP($this->rsQuestionsList->content);
			}

			if ($this->okt->faq->config->public_truncat_char > 0 )
			{
				$this->rsQuestionsList->content = strip_tags($this->rsQuestionsList->content);
				$this->rsQuestionsList->content = text::cutString($this->rsQuestionsList->content,$this->okt->faq->config->public_truncat_char);
			}
		}
		unset($count_line);

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add($this->okt->faq->getName(), FaqHelpers::getFaqUrl());
		}

		# ajout du numéro de page au title
		if ($this->okt->faq->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->faq->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->faq->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->faq->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->faq->getNameSeo());

		# raccourcis
		$this->rsQuestionsList->numPages = $iNumPages;
		$this->rsQuestionsList->pager = $oFaqPager;

		# affichage du template
		if ($this->okt->faq->config->enable_categories) {
			$sTemplatename = 'faq_list_questions_with_categories_tpl';
		} else {
			$sTemplatename = 'faq_list_questions_tpl';
		}

		return $this->render($sTemplatename, array(
			'faqList' => $this->rsQuestionsList
		));
	}

	/**
	 * Affichage d'une question.
	 *
	 */
	public function faqQuestion()
	{
		# module actuel
		$this->page->module = 'faq';
		$this->page->action = 'question';

		# récupération de la question en fonction du slug
		if (!$slug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		$this->rsQuestion = $this->okt->faq->getQuestions(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($this->rsQuestion->isEmpty()) {
			return $this->serve404();
		}

		# formatage des données
		if ($this->rsQuestion->title_tag == '') {
			$this->rsQuestion->title_tag = $this->rsQuestion->title;
		}

		$this->rsQuestion->url = $this->rsQuestion->getQuestionUrl();

		if (!$this->okt->faq->config->enable_rte) {
			$this->rsQuestion->content = Utilities::nlToP($this->rsQuestion->content);
		}

		# meta description
		if (!empty($this->rsQuestion->metadescription)) {
			$this->page->meta_description = $this->rsQuestion->metadescription;
		}
		elseif (!empty($this->okt->faq->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->faq->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsQuestion->meta_keywords)) {
			$this->page->meta_keywords = $this->rsQuestion->meta_keywords;
		}
		elseif (!empty($this->okt->faq->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->faq->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# récupération des images
		$this->rsQuestion->images = $this->rsQuestion->getImagesInfo();

		# récupération des fichiers
		$this->rsQuestion->files = $this->rsQuestion->getFilesInfo();

		# title tag du module
		$this->page->addTitleTag($this->okt->faq->getTitle());

		# title tag du post
		$this->page->addTitleTag((!empty($this->rsQuestion->title_tag) ? $this->rsQuestion->title_tag : $this->rsQuestion->title));

		# titre de la page
		$this->page->setTitle($this->rsQuestion->title);

		# titre SEO de la page
		$this->page->setTitleSeo(!empty($this->rsQuestion->title_seo) ? $this->rsQuestion->title_seo : $this->rsQuestion->title);

		# fil d'ariane du post
		if (!$this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->faq->getName(), FaqHelpers::getFaqUrl());

			$this->page->breadcrumb->add($this->rsQuestion->title, $this->rsQuestion->url);
		}

		# affichage du template
		return $this->render('faq_question_tpl', array(
			'faqQuestion' => $this->rsQuestion
		));
	}

}
