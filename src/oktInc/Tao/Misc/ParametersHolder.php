<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc;

/**
 * Gestion et manipulation de paramètres
 *
 */
class ParametersHolder
{
	/**
	 * La pile de paramètres
	 * @var array
	 */
	private $parameters=array();

	public function __construct($parameters=array())
	{
		$this->setParameters($parameters);
	}

	/**
	 * alimente en paramètres à partir d'un tableau
	 *
	 * @param $parameters array
	 * @return void
	 */
	public function setParameters($parameters=array())
	{
		if (!empty($parameters))
		{
			foreach ($parameters as $key=>$value) {
				$this->setParameter($key,$value);
			}
		}
	}

	/**
	 * créer un paramètre
	 * attention la clef est strtolowerisée
	 *
	 * @param $key string
	 * @param $value mixed
	 * @return void
	 */
	public function setParameter($key,$value)
	{
		$this->parameters[strtolower($key)] = $value;
	}

	/**
	 * Test l'existence d'un paramètre
	 *
	 * @param $key string
	 * @return boolean
	 */
	public function hasParameter($key)
	{
		return array_key_exists(strtolower($key), $this->parameters);
	}

	/**
	 * Récuperation d'un paramètre
	 *
	 * @param $key string
	 * @param $default string
	 * @return mixed
	 */
	public function getParameter($key, $default=null)
	{
		if ($this->hasParameter($key)) {
			return $this->parameters[$key];
		}
		elseif (!is_null($default)) {
			return $default;
		}
		else {
			return null;
		}
	}

	/**
	 * Récuperation des paramètres
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * getParameter automagic alias
	 */
	public function __get($key)
	{
		return $this->getParameter($key);
	}

	/**
	 * setParameter automagic alias
	 */
	public function __set($key,$value)
	{
		return $this->setParameter($key,$value);
	}

	/**
	 * gere automatique les get et les set
	 */
	public function __call($name, $arguments)
	{
		$prefix = substr($name,0,3);
		$key = strtolower(substr($name,3));
		$arg = isset($arguments[0]) ? $arguments[0] : null;

		if ($prefix === 'has') {
			return $this->hasParameter($key, $arg);
		}

		if ($prefix === 'get') {
			return $this->getParameter($key, $arg);
		}

		if ($prefix === 'set')
		{
			if (!$arg) {
				throw new myException(get_class($this).' un second argument est nécessaire pour une méthode set');
			}
			return $this->setParameter($key,$arg);
		}

		throw new myException('notre __call ne gère que les get ou les set ! méthode indéfinie : '.$name);
	}

}

