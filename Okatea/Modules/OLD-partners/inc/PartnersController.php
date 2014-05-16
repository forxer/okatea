<?php
/**
 * @ingroup okt_module_partners
 * @brief Controller public.
 *
 */
use Okatea\Website\Controller;

class PartnersController extends Controller
{

	/**
	 * Affichage de la page partenaires.
	 */
	public function partners()
	{
		# module actuel
		$this->page->module = 'partners';
		$this->page->action = 'list';
		
		# récupération des partenaires
		$rsPartners = $this->okt->partners->getPartners(array(
			'language' => $this->okt->user->language
		));
		
		# title tag du module
		$this->page->addTitleTag($this->okt->partners->getTitle());
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->partners->getName(), PartnersHelpers::getPartnersUrl());
		}
		
		# titre de la page
		$this->page->setTitle($this->okt->partners->getName());
		
		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->partners->getNameSeo());
		
		# affichage du template
		$sTemplateFile = $this->okt->partners->config->enable_categories ? 'partners_with_categories_tpl' : 'partners_tpl';
		
		return $this->render($sTemplateFile, array(
			'rsPartners' => $rsPartners
		));
	}
}
