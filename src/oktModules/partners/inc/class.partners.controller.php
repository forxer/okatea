<?php
/**
 * @ingroup okt_module_partners
 * @brief Controller public.
 *
 */

use Tao\Core\Controller;

class partnersController extends Controller
{
	/**
	 * Affichage de la page partenaires.
	 *
	 */
	public function partnersPage()
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
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__)) {
			$this->page->breadcrumb->add($this->okt->partners->getName(), $this->okt->partners->config->url);
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

} # class
