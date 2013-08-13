<?php
/**
 * @ingroup okt_theme_testing
 * @brief La classe principale du thème.
 *
 */

class oktTheme extends oktThemeBase
{
	public function prepend()
	{
		# définition des rubriques du site
		$this->setRubrique('Accueil', $this->okt->page->getBaseUrl());

		if ($this->okt->modules->moduleExists('contact')) {
			$this->setRubrique($this->okt->contact->getName(),$this->okt->contact->config->url);
		}
		if ($this->okt->modules->moduleExists('disclaimer')) {
			$this->setRubrique(__('disclaimer'),$this->okt->disclaimer->config->url);
		}
		if ($this->okt->modules->moduleExists('guestbook')) {
			$this->setRubrique($this->okt->guestbook->getName(),$this->okt->guestbook->config->url);
		}
		if ($this->okt->modules->moduleExists('news')) {
			$this->setRubrique($this->okt->news->getName(),$this->okt->news->config->url);
		}
		if ($this->okt->modules->moduleExists('diary')) {
			$this->setRubrique($this->okt->diary->getName(),$this->okt->diary->config->url);
		}
		if ($this->okt->modules->moduleExists('galleries')) {
			$this->setRubrique($this->okt->galleries->getName(),$this->okt->galleries->config->url);
		}
		if ($this->okt->modules->moduleExists('pages')) {
			$this->setRubrique($this->okt->pages->getName(),$this->okt->pages->config->url);
		}
		if ($this->okt->modules->moduleExists('partners')) {
			$this->setRubrique($this->okt->partners->getName(),$this->okt->partners->config->url);
		}
		if ($this->okt->modules->moduleExists('estate_i18n')) {
			$this->setRubrique($this->okt->estate_i18n->getName(),$this->okt->estate_i18n->config->url);
		}
		if ($this->okt->modules->moduleExists('faq')) {
			$this->setRubrique($this->okt->faq->getName(),$this->okt->faq->config->url);
		}
		if ($this->okt->modules->moduleExists('restaurant')) {
			$this->setRubrique($this->okt->restaurant->getName(),$this->okt->restaurant->config->url);
		}
		if ($this->okt->modules->moduleExists('vehicles')) {
			$this->setRubrique($this->okt->vehicles->getName(),$this->okt->vehicles->config->url);
		}

		# si module véhicule installé
		if ($this->okt->modules->moduleExists('vehicles'))
		{
			# initialisation de la boite de recherche de véhicules
			vehiclesHelpers::initSearch();

			# initialisation des filtres véhicules
			vehiclesHelpers::initFilters();
		}

		# Ajout de jQuery
		$this->okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');

		# CSS
		$this->okt->page->css->addFile(OKT_COMMON_URL.'/css/init.css');
		$this->okt->page->css->addFile(OKT_THEME.'/css/styles.css');

	}

	/**
	 * Retourne les rubriques du menu haut.
	 *
	 */
	public function getRubriquesMenuHaut()
	{
		return $this->getRubriquesRange(0,3);
	}

	/**
	 * Retourne le menu haut.
	 *
	 * @param string $sBlockFormat
	 * @param string $sItemFormat
	 * @return string
	 */
	public function getMenuHaut($sBlockFormat='<ul id="menu_top">%s</ul><!-- #menu_top -->',$sItemFormat='<li><a id="menu%3$s" href="%2$s">%1$s</a></li>')
	{
		$aMenuHautItems = array();

		$iCount = 1;
		foreach ($this->getRubriquesMenuHaut() as $rubTitle=>$rubUrl) {
			$aMenuHautItems[] = sprintf($sItemFormat,$rubTitle,$rubUrl,$iCount++);
		}

		return sprintf($sBlockFormat,implode("\n",$aMenuHautItems));
	}

	/**
	 * Retourne les rubriques du menu milieu.
	 *
	 */
	public function getRubriquesMenuMilieu()
	{
		return $this->getRubriquesRange(3,7);
	}

	/**
	 * Retourne le menu milieu.
	 *
	 * @param string $sBlockFormat
	 * @param string $sItemFormat
	 * @return string
	 */
	public function getMenuMilieu($sBlockFormat='<ul id="menu_middle">%s</ul><!-- #menu_middle -->',$sItemFormat='<li><a id="menu%3$s" href="%2$s">%1$s</a></li>')
	{
		$aMenuMilieuItems = array();

		$iCount = 3;
		foreach ($this->getRubriquesMenuMilieu() as $rubTitle=>$rubUrl) {
			$aMenuMilieuItems[] = sprintf($sItemFormat,$rubTitle,$rubUrl,$iCount++);
		}

		return sprintf($sBlockFormat,implode("\n",$aMenuMilieuItems));
	}

	/**
	 * Retourne les rubriques du menu bas.
	 *
	 */
	public function getRubriquesMenuBas()
	{
		return $this->getRubriques();
	}

	/**
	 * Retourne le menu bas.
	 *
	 * @param string $sBlockFormat
	 * @param string $sItemFormat
	 * @return string
	 */
	public function getMenuBas($sBlockFormat='<ul class="block_various_links">%s</ul><!-- .block_various_links -->',$sItemFormat='<li><a href="%2$s">%1$s</a></li>')
	{
		$aMenuMilieuItems = array();

		$iCount = 1;
		foreach ($this->getRubriquesMenuBas() as $rubTitle=>$rubUrl) {
			$aMenuMilieuItems[] = sprintf($sItemFormat,$rubTitle,$rubUrl,$iCount++);
		}

		return sprintf($sBlockFormat,implode("\n",$aMenuMilieuItems));
	}

} # class
