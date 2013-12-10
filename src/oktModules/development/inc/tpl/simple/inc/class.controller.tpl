<?php
##header##


use Tao\Misc\Utilities as util;
use Tao\Core\Controller;

class ##module_camel_case_id##Controller extends Controller
{
	/**
	 * Affichage de la page.
	 *
	 */
	public function ##module_camel_case_id##Page()
	{
		# meta description
		if ($this->okt->##module_id##->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->##module_id##->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->##module_id##->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->##module_id##->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
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

} # class
