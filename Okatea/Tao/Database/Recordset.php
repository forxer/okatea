<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Orignal file from Dotclear 2.
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 */

namespace Okatea\Tao\Database;

/**
 * Cette classe permet de manipuler des données entrées dans un tableaux multilignes
 * et multicolonnes.
 *
 * Les classes @ref MySql et @ref MySqli renvoie des recordsets comme résultat
 * de requêtes.
 *
 */
class Recordset
{
	/**
	 * Tableau contenant les données.
	 *
	 * @var array
	 */
	protected $aData;

	/**
	 * Emplacement du curseur.
	 *
	 * @var integer
	 */
	protected $iIndex;

	/**
	 * Nombre d'enregistrements.
	 *
	 * @var integer
	 */
	protected $iRowCount;

	/**
	 * Nombre de colonnes.
	 *
	 * @var integer
	 */
	protected $iColCount;

	/**
	 * Indice de déplacement utilisé localement.
	 *
	 * @var integer
	 */
	private $iFetchIndex;


	/**
	 * Constructeur. Cette méthode initialise le recordset. $data est un
	 * tableau de plusieurs lignes et colones.
	 *
	 * Par exemple :
	 *
	 * #!php
	 * <?php
	 * $d = array(
	 *		array('f1' => 'v01', 'f2' => 'v02'),
	 *		array('f1' => 'v11', 'f2' => 'v12'),
	 * 		array('f1' => 'v21', 'f2' => 'v22')
	 * );
	 * $rs = new recordset($d);
	 *
	 * while ($rs->fetch()) {
	 * 		echo $rs->f('f1').' - '.$rs->f('f2').'<br />';
	 * }
	 *
	 * while ($rs->fetch()) {
	 * 		echo $rs->f1.' - '.$rs->f2.'<br />';
	 * }
	 * ?>
	 *
	 * @param	array	data			Tableau contenant les données
	 * @return void
	 */
	public function __construct($data)
	{
		$this->iIndex = 0;
		$this->iFetchIndex = NULL;

		if (is_array($data))
		{
			$this->aData = $data;
			$this->iRowCount = count($this->aData);

			if ($this->iRowCount == 0) {
				$this->iColCount = 0;
			}
			else {
				$this->iColCount = count($this->aData[0]);
			}
		}
	}

	/**
	 * Renvoie la valeur d'un champ donné, pour la ligne courante.
	 *
	 * @param	mixed	c			Nom ou numéro du champ
	 * @return	string
	 */
	public function field($c)
	{
		if (!empty($this->aData))
		{
			if (is_integer($c))
			{
				$T = array_values($this->aData[$this->iIndex]);
				return (isset($T[($c)])) ? $T[($c)] : false;
			}
			else {
				$c = strtolower($c);
				if (isset($this->aData[$this->iIndex][$c])) {
	//				if (!is_array($this->aData[$this->iIndex][$c]))
	//					return trim($this->aData[$this->iIndex][$c]);
	//				else
						return $this->aData[$this->iIndex][$c];
				}

				return false;
			}
		}
	}

	/**
	 * Renvoie la valeur d'un champ donné, pour une ligne donnée.
	 *
	 * @param	mixed	c			Nom ou numéro du champ
	 * @param	integer	l			Numéro de ligne
	 * @return	string
	 */
	public function fieldLine($c,$l)
	{
		if (!empty($this->aData))
		{
			if (is_integer($c))
			{
				$T = array_values($this->aData[$l]);
				return (isset($T[($c)])) ? $T[($c)] : false;
			}
			else {
				$c = strtolower($c);
				if (isset($this->aData[$l][$c]))
				{
					if (!is_array($this->aData[$l][$c]))
						return trim($this->aData[$l][$c]);
					else
						return $this->aData[$l][$c];
				}

				return false;
			}
		}
	}

	/**
	 * Alias de la méthode fieldLine
	 *
	 * @param	mixed	c			Nom ou numéro du champ
	 * @param	integer	l			Numéro de ligne
	 * @return	string
	 */
	public function fl($c,$l)
	{
		return $this->fieldLine($c,$l);
	}

	/**
	 * Alias de la méthode field
	 *
	 * @param	mixed	c			Nom ou numéro du champ
	 * @return	string
	 */
	public function f($c)
	{
		return $this->field($c);
	}

	/**
	 * Magic get method. Alias de la méthode field().
	 */
	public function __get($c)
	{
		return $this->field($c);
	}

	/**
	 * Change la valeur d'un champ donné à la ligne courante.
	 *
	 * @param	string	c			Nom du champ
	 * @param	string	v			Valeur du champ
	 */
	public function setField($c,$v)
	{
		$c = strtolower($c);
		$this->aData[$this->iIndex][$c] = $v;
	}

	public function set($c,$v)
	{
		return $this->setField($c,$v);
	}

	/**
	 * Magic set method. Alias de la méthode setField().
	 */
	public function __set($c,$v)
	{
		return $this->setField($c,$v);
	}

	/**
	* Field exists
	*
	* Returns true if a field exists.
	*
	* @param string		$c		Field name
	* @return string
	*/
	public function exists($c)
	{
		return isset($this->aData[$this->iIndex][$c]);
	}

	/**
	* Field isset
	*
	* Returns true if a field exists (magic method from PHP 5.1).
	*
	* @param string		$c		Field name
	* @return boolean
	*/
	public function __isset($c)
	{
		return isset($this->aData[$this->iIndex][$c]);
	}

	/**
	* Field unset
	*
	* @param string		$c		Field name
	* @return void
	*/
	public function __unset($c)
	{
		unset($this->aData[$this->iIndex][$c]);
	}

	/*
	 * Cette méthode pose trop de problèmes....
	 *
	 */
	/*
	public function unsetLine($l=null)
	{
		if (is_null($l)) {
			$l = $this->iIndex;
		}

		unset($this->aData[$l]);
		$this->iRowCount = count($this->aData);
		$this->aData = array_values($this->aData);
	}
	*/

	/**
	 * Remet le curseur à la première ligne des données et renvoie vrai.
	 *
	 * @return	boolean
	 */
	public function moveStart()
	{
		$this->iIndex = 0;
		return true;
	}

	/**
	 * Positionne le curseur à la dernière ligne des données et renvoie vrai.
	 *
	 * @return	boolean
	 */
	public function moveEnd()
	{
		$this->iIndex = ($this->iRowCount-1);
		return true;
	}

	/**
	 * Déplace le curseur d'un cran si possible et renvoie vrai. Si le curseur
	 * est à la fin du tableau, renvoie false.
	 *
	 * @return	boolean
	 */
	public function moveNext()
	{
		if (!empty($this->aData) && !$this->EOF())
		{
			$this->iIndex++;
			return true;
		}
		return false;
	}

	/**
	 * Déplace le curseur d'un cran dans le sens inverse si possible et renvoie
	 * vrai. Si le curseur	est au début du tableau, renvoie false.
	 *
	 * @return	boolean
	 */
	public function movePrev()
	{
		if (!empty($this->aData) && $this->iIndex > 0)
		{
			$this->iIndex--;
			return true;
		}
		return false;
	}

	/**
	 * Positionne le curseur à l'indice donné par $index. Si l'indice n'existe
	 * pas, renvoie false.
	 *
	 * @param	integer	index		Indice
	 * @return	boolean
	 */
	public function move($index)
	{
		if (!empty($this->aData) && $this->iIndex >= 0 && $index < $this->iRowCount)
		{
			$this->iIndex = $index;
			return true;
		}
		return false;
	}

	public function index()
	{
		return $this->iIndex;
	}

	/**
	 * Déplace le cuseur d'un cran et renvoie vrai tant que celui ci n'est pas
	 * positionné à la fin du tableau. La fonction démarre toujours du premier
	 * élément du tableau. Elle a pour vocation à être utilisée dans une boucle
	 * de type while (voir le premier exemple).
	 *
	 * @return	boolean
	 */
	public function fetch()
	{
		if ($this->iFetchIndex === null)
		{
			$this->iFetchIndex = 0;
			$this->iIndex = -1;
		}

		if ($this->iFetchIndex+1 > $this->iRowCount)
		{
			$this->iFetchIndex = null;
			$this->iIndex = 0;
			return false;
		}

		$this->iFetchIndex++;
		$this->iIndex++;

		return true;
	}

	/**
	 * Indique si le curseur est au début du tableau.
	 *
	 * @return	boolean
	 */
	public function BOF()
	{
		return ($this->iIndex == -1 || $this->iRowCount == 0);
	}

	/**
	 * Indique si le curseur est à la fin du tableau.
	 *
	 * @return	boolean
	 */
	public function EOF()
	{
		return ($this->iIndex == $this->iRowCount);
	}

	/**
	 * Indique si le tableau de données est vide.
	 *
	 * @return	boolean
	 */
	public function isEmpty()
	{
		return ($this->iRowCount == 0);
	}

	/**
	 * Renvoie le tableau de données.
	 *
	 * @return	array
	 */
	public function getData($id=null)
	{
		if ($id === null) {
			return $this->aData;
		}
		else {
			return $this->aData[$id];
		}
	}

	public function getJson($id=null)
	{
		return json_encode($this->getData($id));
	}

	/**
	 * Renvoie le nombre de lignes du tableau.
	 *
	 * @return	integer
	 */
	public function nbRow()
	{
		return $this->iRowCount;
	}

}
