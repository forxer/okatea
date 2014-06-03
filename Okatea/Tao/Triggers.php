<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

/**
 * Le système de gestion des déclencheurs.
 */
class Triggers
{
	/**
	 * La pile qui contient les déclencheurs.
	 *
	 * @var array
	 */
	protected $aStack = array();

	/**
	 * Construtor.
	 *
	 * Initialise la pile de déclencheurs. Et c'est tout...
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->aStack = array();
	}

	/**
	 * Ajout de plusieurs nouveaux callables à la pile de déclencheurs.
	 *
	 * @param array $aTriggers
	 *        	Tableaux des déclencheurs à ajouter
	 * @return void
	 */
	public function registerTriggers($aTriggers)
	{
		foreach ($aTriggers as $sTrigger => $mCallable)
		{
			$this->registerTrigger($sTrigger, $mCallable);
		}
	}

	/**
	 * Ajoute un nouveau callable à la pile de déclencheurs.
	 *
	 * @param string $sTrigger
	 *        	Nom du déclencheur
	 * @param callable $mCallable
	 *        	Le "callable" (callback) à appeller
	 * @return void
	 */
	public function registerTrigger($sTrigger, $mCallable)
	{
		if (is_callable($mCallable))
		{
			$this->aStack[$sTrigger][] = $mCallable;
		}
	}

	/**
	 * Magic __set method.
	 * Alias de la méthode registerTrigger().
	 *
	 * @see self::registerTrigger()
	 */
	public function __set($sTrigger, $mCallable)
	{
		$this->registerTrigger($sTrigger, $mCallable);
	}

	/**
	 * Test si un déclencheur particulier existe dans la pile de déclencheurs.
	 *
	 * @param string $sTrigger
	 *        	Nom du déclencheur
	 * @return boolean
	 */
	public function hasTrigger($sTrigger)
	{
		return isset($this->aStack[$sTrigger]);
	}

	/**
	 * Magic __isset method.
	 * Alias de la méthode hasTrigger().
	 *
	 * @see self::hasTrigger()
	 */
	public function __isset($sTrigger)
	{
		return $this->hasTrigger($sTrigger);
	}

	/**
	 * Permet de récupérer la pile des déclencheurs
	 * (ou un déclencheur si le paramètre est précisé).
	 *
	 * @param string $sTrigger
	 *        	Nom du déclencheur
	 * @return array
	 */
	public function getTriggers($sTrigger = null)
	{
		if (empty($this->aStack))
		{
			return null;
		}

		if (empty($sTrigger))
		{
			return $this->aStack;
		}
		elseif ($this->hasTrigger($sTrigger))
		{
			return $this->aStack[$sTrigger];
		}

		return null;
	}

	/**
	 * Permet de récupérer un déclencheur.
	 *
	 * @param string $sTrigger
	 *        	Nom du déclencheur
	 * @return array
	 */
	public function getTrigger($sTrigger)
	{
		return $this->getTriggers($sTrigger);
	}

	/**
	 * Magic __get method.
	 * Alias de la méthode getTrigger().
	 *
	 * @see self::getTrigger()
	 */
	public function __get($sTrigger)
	{
		return $this->getTrigger($sTrigger);
	}

	/**
	 * Appelle chaque callable dans la pile de déclencheurs pour
	 * un déclencheur donné et retourne les résultats concaténés
	 * de chaques callables.
	 *
	 * @param string $sTrigger
	 *        	Nom du déclencheur
	 * @return string
	 */
	public function callTrigger($sTrigger)
	{
		if (! $this->hasTrigger($sTrigger))
		{
			return null;
		}

		$args = func_get_args();
		array_shift($args);

		$sReturn = '';
		foreach ($this->aStack[$sTrigger] as $f)
		{
			$sReturn .= call_user_func_array($f, $args);
		}

		return $sReturn;
	}
}
