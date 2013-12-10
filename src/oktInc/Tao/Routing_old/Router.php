<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Routing_old;

/**
 * Le routeur interne d'Okatea.
 *
 */
class Router
{
	/**
	 * Stocke la liste des objets Tao\Routing\Route.
	 * @var array
	 */
	protected $aRoutes = array();

	/**
	 * Le code ISO de la langue si trouvé.
	 * @var string
	 */
	protected $sLanguage = null;

	/**
	 * Le chemin si trouvé.
	 * @var string
	 */
	protected $sPath = null;

	/**
	 * Le nom de la route si trouvée.
	 * @var string
	 */
	protected $sFindedRoute = null;


	/**
	 * Constructor. Possibilité de passer des routes à ajouter dans un tableau.
	 *
	 * @param array $routes
	 */
	public function __construct($aRoutes=array())
	{
		$this->addRoutes($aRoutes);
	}

	/**
	 * Ajout d'une route nommée à la liste des routes possibles.
	 *
	 * @param string $sName
	 * @param Tao\Routing\Route $oRoute
	 * @return Tao\Routing\Router
	 */
	public function addRoute($sName, Route $oRoute)
	{
		$this->aRoutes[$sName] = $oRoute;

		return $this;
	}

	/**
	 * Ajout d'un tableau de routes nommées à la liste des routes possibles.
	 *
	 * @param array $aRoutes
	 * @return Tao\Routing\Router
	 */
	public function addRoutes($aRoutes)
	{
		foreach ($aRoutes as $sName=>$oRoute) {
			$this->addRoute($sName, $oRoute);
		}

		return $this;
	}

	/**
	 * Retourne la liste des routes possibles.
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->aRoutes;
	}

	/**
	 * Définit le code ISO de la langue.
	 *
	 * @param mixed $sLanguage
	 * @return Tao\Routing\Router
	 */
	public function setLanguage($sLanguage)
	{
		$this->sLanguage = $sLanguage;

		return $this;
	}

	/**
	 * Retourne le code ISO de la langue.
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->sLanguage;
	}

	/**
	 * Retourne le chemin.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->sPath;
	}

	/**
	 * Retourne la route trouvée.
	 *
	 * @return string
	 */
	public function getFindedRoute()
	{
		if ($this->sFindedRoute !== null && isset($this->aRoutes[$this->sFindedRoute])) {
			return $this->aRoutes[$this->sFindedRoute];
		}

		return null;
	}

	/**
	 * Retourne l'identifiant de la route trouvée.
	 *
	 * @return string
	 */
	public function getFindedRouteId()
	{
		return $this->sFindedRoute;
	}

	/**
	 * Cherche dans la liste une route correspondant au $sPath spécifié.
	 *
	 * @param string $sPath
	 * @return boolean
	 */
	public function findRoute($sPath=null)
	{
		# définition du chemin à utiliser
		if ($sPath !== null) {
			$sPath = $this->formatGivenPath($sPath);
		}
		else {
			$sPath = $this->getPathFromQueryString();
		}

		# recherche d'un code ISO de langue
		if (preg_match('#^(?:([a-zA-Z]{2}(?:-[a-z]{2})?)/)(.*)#',$sPath,$m))
		{
			$this->sLanguage = $m[1];
			$sPath = $m[2];
		}

		# boucle sur les routes
		foreach ($this->aRoutes as $sRouteName=>$oRoute)
		{
			if ($oRoute->matchPath($sPath) === true)
			{
				$this->sPath = ($sPath != '/' ? $sPath : '');
				$this->sFindedRoute = $sRouteName;

				return true;
			}
		}

		return false;
	}

	/**
	 * Invoque le gestionnaire de la route trouvée.
	 *
	 * @return void
	 */
	public function callRouteHanlder()
	{
		$oFindedRoute = $this->getFindedRoute();

		if ($oFindedRoute === null || !($oFindedRoute instanceof Route)) {
			return false;
		}

		$sClass = $oFindedRoute->getClassHandler();
		$sMethod = $oFindedRoute->getMethodHandler();

		if ($sClass === null || $sMethod === null) {
			return false;
		}

		if (!class_exists($sClass)) {
			return false;
		}

		$obj = new $sClass($GLOBALS['okt']);

		if (!is_callable(array($obj,$sMethod))) {
			return false;
		}

		call_user_func(array($obj,$sMethod),$oFindedRoute->getArgs());
	}

	/**
	 * Formate un chemin donnée pour trouver une route.
	 *
	 * @param string $sPath
	 * @return string
	 */
	protected function formatGivenPath($sPath)
	{
		$aParsed = parse_url($sPath);
		$sPath = $aParsed['path'];

		$sPath = preg_replace('/^\//', '', $sPath);

		return $sPath;
	}

	/**
	 * Détermine le chemin pour trouver une route en fonction de la query string.
	 *
	 * @return string
	 */
	protected function getPathFromQueryString()
	{
		$sPath = '/';

		if (!empty($_SERVER['QUERY_STRING']))
		{
			if (($iPos = strpos($_SERVER['QUERY_STRING'], '&')) !== false) {
				$sPath = substr($_SERVER['QUERY_STRING'], 0, $iPos);
			}
			else {
				$sPath = $_SERVER['QUERY_STRING'];
			}
		}

		return $sPath;
	}


}
