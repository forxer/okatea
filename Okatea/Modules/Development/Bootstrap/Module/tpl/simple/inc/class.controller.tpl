<?php
##header##


use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Controller;

class ##module_camel_case_id##Controller extends Controller
{
	/**
	 * Affichage de la page.
	 *
	 */
	public function ##module_camel_case_id##Page()
	{
		# meta description
		if ($this->okt->##module_id##->config->meta_description[$this->okt['visitor']->language] != '') {
			$this->okt->page->meta_description = $this->okt->##module_id##->config->meta_description[$this->okt['visitor']->language];
		}
		else {
			$this->okt->page->meta_description = Utilities::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->##module_id##->config->meta_keywords[$this->okt['visitor']->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->##module_id##->config->meta_keywords[$this->okt['visitor']->language];
		}
		else {
			$this->okt->page->meta_keywords = Utilities::getSiteMetaKeywords();
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->##module_id##->getTitle());

		# titre de la page
		$this->okt->page->setTitle($this->okt->##module_id##->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->##module_id##->getNameSeo());

		# affichage du template
		return $this->render('##module_id##_tpl');
	}

}
