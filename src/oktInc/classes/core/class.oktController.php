<?php
/**
 * @class oktController
 * @ingroup okt_classes_core
 * @brief Controller de base
 *
 */

class oktController
{
	protected $okt;

	protected $sRequestedLanguage;

	/**
	 * Constructor.
	 *
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;

		// TODO : patch de bourin de merde ; normalement il faudrait faire des redirections vers la page demandée
		// dans la langue demandée ; mais bon, pas l'temps...
		//$this->sRequestedLanguage = $this->setUserRequestLanguage();
		if ($this->setUserRequestLanguage()) {
			http::redirect($this->okt->page->getBaseUrl());
		}
	}

	public function getRequestedLanguage()
	{
		return $this->sRequestedLanguage;
	}

	/**
	 * Change la langue de l'utilisateur en fonction de la requete URL
	 * et retourne la langue définie. Retourne false si pas de changement.
	 *
	 * @return string/boolean
	 */
	protected function setUserRequestLanguage()
	{
		static $sRequestedLanguage = null;

		if ($sRequestedLanguage !== null) {
			return $sRequestedLanguage;
		}

		$sRequestLanguage = $this->okt->router->getLanguage();

		if (empty($sRequestLanguage))
		{
			$sRequestedLanguage = false;
			return $sRequestedLanguage;
		}

		if ($sRequestLanguage === $this->okt->user->language)
		{
			$sRequestedLanguage = false;
			return $sRequestedLanguage;
		}

		if (!$this->okt->user->setUserLang($sRequestLanguage)) {
			$sRequestedLanguage = false;
		}
		else {
			$sRequestedLanguage = $sRequestLanguage;
		}

		return $sRequestedLanguage;
	}

	public function switchLanguage($urlPage, $params_after=null, $params_before=null)
	{
		/*if ($this->sRequestedLanguage !== false)
		{
			if (!empty($urlPage)) {
				$urlPage = $urlPage[$this->sRequestedLanguage];
			}

			http::redirect($this->okt->page->getBaseUrl().$params_before.$urlPage.$params_after);
		}*/
	}

	/**
	 * Affichage page 404
	 *
	 */
	public function serve404()
	{
		$this->okt->page->module = '404';
		$this->okt->page->action = '404';

		http::head(404);

		echo $this->okt->tpl->render('404');

		exit;
	}

	/**
	 * Affichage page 503
	 *
	 */
	public function serve503()
	{
		$this->okt->page->module = '503';
		$this->okt->page->action = '503';

		http::head(503);

		header('Retry-After: 3600');

		echo $this->okt->tpl->render('503');

		exit;
	}


} # class oktController
