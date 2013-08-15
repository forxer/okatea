<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktRoute
 * @ingroup okt_classes_router
 * @brief La définition d'une route.
 *
 * Une route est constituée d'une représentation et d'un gestionnaire.
 *
 * La réprésentation peut-être une simple chaine de caractère
 * ou une expression régulière.
 *
 * Le gestionnaire doit être
 */

class oktRoute
{
	/**
	 * La représentation du chemin de la route.
	 * @var string
	 */
	protected $sPathRepresentation = null;

	/**
	 * Indique si la représentation est une expréssion régulière.
	 * @var boolean
	 */
	protected $bIsRegexp = true;

	/**
	 * Le nom de la classe du gestionnaire de la route.
	 * @var string
	 */
	protected $sClassHandler = null;

	/**
	 * Le nom de la méthode du gestionnaire de la route.
	 * @var string
	 */
	protected $sMethodHandler = null;

	/**
	 * Les arguments à passer à la méthode du gestionnaire de la route.
	 * @var array
	 */
	protected $aArgs = null;


	/**
	 * Constructor.
	 *
	 * @param string $sPathRepresentation
	 * @param string $sClassHandler
	 * @param string $sMethodHandler
	 * @param array $aArgs
	 */
	public function __construct($sPathRepresentation=null, $sClassHandler=null, $sMethodHandler=null, $aArgs=null)
	{
		$this->setPathRepresentation($sPathRepresentation);

		$this->setClassHandler($sClassHandler);

		$this->setMethodHandler($sMethodHandler);

		$this->setArgs($aArgs);
	}

	/**
	 * Définit la représentation du chemin de la route.
	 *
	 * @param string $sPathRepresentation
	 * @return oktRoute
	 */
	public function setPathRepresentation($sPathRepresentation)
	{
		$this->sPathRepresentation = $sPathRepresentation;

		$this->bIsRegexp = (boolean)($sPathRepresentation{0} === '^');

		return $this;
	}

	/**
	 * Retourne la représentation du chemin de la route.
	 *
	 * @return string
	 */
	public function getPathRepresentation()
	{
		return $this->sPathRepresentation;
	}

	/**
	 * Indique si la représentation est une expression régulière.
	 *
	 * @return boolean;
	 */
	public function isRegexp()
	{
		return $this->bIsRegexp;
	}

	/**
	 * Définit le gestionnaire de la route.
	 *
	 * @param array $mHandler
	 * @return oktRoute
	 */
	public function setHandler($sClassHandler,$sMethodHandler)
	{
		$this->setClassHandler($sClassHandler);
		$this->setMethodHandler($sMethodHandler);

		return $this;
	}

	/**
	 * Définit la classe du gestionnaire de la route.
	 *
	 * @return mixed
	 */
	public function setClassHandler($sClassHandler)
	{
		$this->sClassHandler = $sClassHandler;

		return $this;
	}

	/**
	 * Retourne la classe du gestionnaire de la route.
	 *
	 * @return mixed
	 */
	public function getClassHandler()
	{
		return $this->sClassHandler;
	}

	/**
	 * Définit la méthode du gestionnaire de la route.
	 *
	 * @return mixed
	 */
	public function setMethodHandler($sMethodHandler)
	{
		$this->sMethodHandler = $sMethodHandler;

		return $this;
	}

	/**
	 * Retourne la méthode du gestionnaire de la route.
	 *
	 * @return mixed
	 */
	public function getMethodHandler()
	{
		return $this->sMethodHandler;
	}

	/**
	 * Définit les arguments à passer à la méthode du gestionnaire de la route.
	 *
	 * @return mixed
	 */
	public function setArgs($aArgs)
	{
		$this->aArgs = $aArgs;

		return $this;
	}

	/**
	 * Retourne les arguments à passer à la méthode du gestionnaire de la route.
	 *
	 * @return string
	 */
	public function getArgs()
	{
		return $this->aArgs;
	}

	/**
	 * Test la correspondance de cette route avec un chemin donné.
	 *
	 * @param string $sPath
	 * @return boolean
	 */
	public function matchPath($sPath)
	{
		if (!$this->bIsRegexp && $sPath === $this->sPathRepresentation) {
			return true;
		}
		elseif ($this->bIsRegexp && preg_match('#'.$this->sPathRepresentation.'#',$sPath,$m))
		{
			if (empty($this->aArgs))
			{
				array_shift($m);
				$this->aArgs = $m;
			}

			return true;
		}
		else {
			return false;
		}
	}


} # class oktRoute
