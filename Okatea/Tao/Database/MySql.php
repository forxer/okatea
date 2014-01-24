<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Database;

use Okatea\Tao\Misc\Utilities;

/**
 * Classe de connexion MySQL
 * @deprecated 1.0
 *
 */
class MySql
{
	/**
	 * Type de retour de la fonction connexion permettant le renvoi du dernier ID généré.
	 * @see execute()
	 */
	const LAST_ID = 1;

	/**
	 * Type de retour de la fonction connexion permettant le renvoi du nombre de ligne affecté.
	 * @see execute()
	 */
	const NUM_ROW = 2;

	/**
	 * Dernière erreur de la base de données.
	 * @var	string
	 */
	protected $error;

	/**
	 * Numéro de la dernière erreur.
	 * @var	integer
	 */
	protected $errno;

	/**
	 * Nom d'utilisateur de la base de données.
	 * @var	string
	 */
	protected $db_user;

	/**
	 * Mot de passe de la base de données.
	 * @var	string
	 */
	protected $db_pwd;

	/**
	 * Hote de la base de données.
	 * @var	string
	 */
	protected $db_host;

	/**
	 * Nom de la base de données.
	 * @var	string
	 */
	protected $db_name;

	/**
	 * Prefixe des tables.
	 * @var	string
	 */
	public $prefix;

	/**
	 * Nombre de requêtes lancées.
	 * @var	integer
	 */
	public $nb_q;

	/**
	 * Résultat de la dernière requête.
	 * @var	mixed
	 */
	public $query_result;

	/**
	 * Log des requêtes lancées
	 *
	 * @var array
	 */
	protected $log;

	/**
	 * Variable d'enregistrement du début d'une requete
	 *
	 * @var float
	 */
	protected $start_time;

	/**
	 * Temps total d'execution des requêtes
	 *
	 * @var float
	 */
	protected $total_time;

	/**
	 * Identifiant de connexion
	 */
	protected $con_id;

	/**
	 * Initialise la connexion à la base de données.
	 *
	 * @param string $user 		ID de l'utilisateur
	 * @param string $pwd 		Mot de passe
	 * @param string $alias 	Serveur auquel se connecter
	 * @param string $dbname 	Nom de la base de données
	 * @param string $dbprefix 	Préfixe de la base de donnée
	 * @return void
	 */
	public function __construct($user='', $pwd='', $alias='', $dbname='', $dbprefix='')
	{
		$this->error = '';
		$this->nb_q = 0;

		$this->log = array();

		$this->start_time = 0;
		$this->total_time = 0;

		$this->db_user = $user;
		$this->db_pwd = $pwd;
		$this->db_host = $alias;
		$this->db_name = $dbname;

		$this->prefix = $dbprefix;

		if (($this->con_id = mysql_connect($alias, $user, $pwd)) === false) {
			$this->seterror();
		}
		else {
			$this->database($dbname);
		}
	}

	/**
	 * Change de base de données. Renvoie vrai en cas de succès.
	 *
	 * @param	string	dbname		Nom de la base de données
	 * @return	boolean
	 */
	private function database($dbname)
	{
		$db = mysql_select_db($dbname);

		if (!$db)
		{
			$this->seterror();
			return false;
		}
		else {
			$this->execute('SET NAMES utf8');
			return true;
		}
	}

	/**
	 * Ferme la connection à la base de données et renvoie vrai en cas de succès.
	 *
	 * @return	boolean
	 */
	public function close()
	{
		if (is_resource($this->con_id) && get_resource_type($this->con_id) == 'mysql link')
		{
			mysql_close($this->con_id);
			return true;
		}

		return false;
	}

	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Enregistre une entrée dans le log de requêtes
	 *
	 * @param string $query
	 * @param string $time
	 * @param string $comment
	 * @return void
	 */
	private function log($query, $time)
	{
		$this->log[] = array(++$this->nb_q, $query, $time);
	}

	/**
	 * Retourne le contenu du log
	 *
	 * @return array
	 */
	public function getLog($num=null)
	{
		if ($num !== null) {
			return $this->log[$num];
		}

		return $this->log;
	}

	/**
	 * Retourne le contenu du dernier log
	 *
	 * @return array
	 */
	public function getLastLog($value=null)
	{
		$logline = $this->getLog((integer)(count($this->log)-1));
		return (!empty($logline[$value]) ? $logline[$value] : $logline);
	}

	/**
	 * Cette méthode référence la dernière erreur du moteur de base de données
	 * dans les propriétés 'error' et 'errorno'.
	 *
	 * Le résultat de cette méthode privée est exploitable par la méthode 'error'.
	 */
	private function seterror()
	{
		if ($this->con_id)
		{
			$this->error = mysql_error($this->con_id);
			$this->errno = mysql_errno($this->con_id);
		}
		else {
			$this->error = (mysql_error() !== false) ? mysql_error() : 'Unknown error';
			$this->errno = (mysql_errno() !== false) ? mysql_errno() : 0;
		}
	}

	/**
	 * Indique si il y a une erreur enregistrée
	 *
	 * @return bollean
	 */
	public function hasError()
	{
		return ($this->error != '');
	}

	/**
	 * Renvoie la dernière erreur de la base de données dans le format
	 * 'numéro' - 'erreur'. Renvoie faux si aucune erreur.
	 *
	 * @return string
	 */
	public function error()
	{
		if ($this->error != '') {
			return $this->errno.' - '.$this->error;
		}

		return false;
	}

	/**
	 * Enregistre le début d'une requête
	 *
	 * @see getTime()
	 * @return void
	 */
	private function startTime()
	{
		$this->start_time = (float)microtime(true);
	}

	/**
	 * Donne le temps d'execution depuis l'enregistrement du début d'une requête
	 *
	 * @see startTime()
	 * @return float
	 */
	private function getTime()
	{
		$time = explode(' ', microtime());
		$time = sprintf('%.5f', ((float)$time[0] + (float)$time[1]) - $this->start_time);

		$this->start_time = 0;
		$this->regTime($time);

		return $time;
	}

	/**
	 * Enregistre un temps au temps total d'execution
	 *
	 * @param float $time
	 * @return void
	 */
	private function regTime($time)
	{
		$this->total_time += $time;
	}

	/**
	 * Retourne le temp total d'execution
	 *
	 * @return float
	 */
	public function getTotalTime()
	{
		return (float)$this->total_time;
	}

	/**
	 * Retourne le nombre de requêtes exécutées
	 */
	public function nbQueries()
	{
		return $this->nb_q;
	}

	/**
	 *	Retrieve list of database table
	 */
	public function getTables($db_prefix=null)
	{
		if (($tablesList = $this->select('SHOW TABLES FROM '.$this->db_name)) === false) {
			throw new \Exception('Unable to retrieve tables '.$this->db->error());
		}

		$tables = array();
		if ($db_prefix)
		{
			foreach ($tablesList->getData() as $t)
			{
				if (strpos($t['tables_in_'.$this->db_name], $db_prefix) !== false) {
					$tables[] = $t['tables_in_'.$this->db_name];
				}
			}
		}
		else {
			foreach ($tablesList->getData() as $t) {
				$tables[] = $t['tables_in_'.$this->db_name];
			}
		}

		return $tables;
	}

	/**
	 * Execute une requête SQL et renvoie le resultat dans une instance de l'objet
	 * dont le type est défini par $class. Le type d'objet par défaut est un
	 * recordset.
	 *
	 * N'importe quel objet peut-être utilisé à la place du recordset du moment
	 * qu'il prend un tableau multidimmensionel comme premier argument de son
	 * constructeur.
	 *
	 * Cette méthode renvoie false en cas d'erreur.
	 *
	 * @param	string	query		Requête SQL
	 * @param	string	class		Type d'objet à renvoyer ('recordset')
	 * @return	recordset
	 */
	public function select($query,$class='Recordset')
	{
		if (!$this->con_id) {
			return false;
		}

		if ($class == '' || !class_exists($class)) {
			$class = 'Recordset';
		}

		$this->startTime();
		$cur = mysql_unbuffered_query($query, $this->con_id);
		$exec_time = $this->getTime();

		$this->log($query,$exec_time);

		if ($cur)
		{
			# Insertion dans le reccordset
			$i = 0;
			$arryRes = array();
			while ($res = mysql_fetch_row($cur))
			{
				$nRes = count($res);

				for ($j=0; $j<$nRes; $j++) {
					$arryRes[$i][strtolower(mysql_field_name($cur, $j))] = $res[$j];
				}

				$i++;
			}

			return new $class($arryRes);
		}
		else {
			$this->seterror();
			return false;
		}
	}

	/**
	 * Cette méthode exécute la requête $query et renvoi vrai si aucune erreur
	 * ne s'est produite, faux dans le cas contraire.
	 *
	 * @param	string	query		Requête SQL
	 * @return	boolean
	 */
	public function execute($query,$type=null)
	{
		if (!$this->con_id) {
			return false;
		}

		$this->startTime();
		$cur = mysql_query($query, $this->con_id);
		$exec_time = $this->getTime();

		$this->log($query,$exec_time);

		if (!$cur)
		{
			$this->seterror();
			return false;
		}

		if ($type === self::NUM_ROW) {
			return $this->affectedRows();
		}
		elseif ($type === self::LAST_ID) {
			return $this->getLastID();
		}
		else {
			return true;
		}
	}

	/**
	 * Execute une requête SQL.
	 *
	 * Cette méthode renvoie false en cas d'erreur.
	 *
	 * @param	string	query		Requête SQL
	 * @return	mixed
	 */
	public function query($query, $unbuffered=false)
	{
		if (!$this->con_id) {
			return false;
		}

		$this->startTime();

		if ($unbuffered) {
			$this->query_result = mysql_unbuffered_query($query, $this->con_id);
		}
		else {
			$this->query_result = mysql_query($query, $this->con_id);
		}

		$exec_time = $this->getTime();

		$this->log($query,$exec_time);

		if ($this->query_result) {
			return $this->query_result;
		}
		else {
			$this->seterror();
			return false;
		}
	}

	/**
	 * Execute une requête SQL à partir d'un fichier.
	 *
	 * Cette méthode renvoie false en cas d'erreur.
	 *
	 * @param	string	query		Requête SQL
	 * @return	recordset
	 */
	public function queryFile($file)
	{
		if (file_exists($file))
		{
			$query = file_get_contents($file);
			$query = trim($query);
			$query = str_replace('{{DB_PREFIX}}',$this->prefix,$query);

			if (!empty($query)) {
				return $this->execute($query);
			}
		}
	}

	/**
	 * Retourne un champ d'un résultat MySQL.
	 *
	 * @param integer $row
	 * @param mixed $col
	 * @see mysql_result
	 * @return mixed
	 */
	public function result($row, $col=0)
	{
		return ($this->query_result) ? mysql_result($this->query_result, $row, $col) : false;
	}

	/**
	 * Optimise une table donnée
	 *
	 * @param string	table	Le nom de la table à optimiser.
	 * @return boolean
	 */
	public function optimize($table)
	{
		$strReq = 'OPTIMIZE TABLE '.$table.' ';

		if ($this->execute($strReq) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne toutes les lignes de résultat MySQL dans un tableau associatif.
	 *
	 * @return array
	 */
	public function fetchAll()
	{
		$aResult = array();

		while ($aItem = $this->fetchAssoc()) {
			$aResult[] = $aItem;
		}

		return $aResult;
	}

	/**
	 * Lit une ligne de résultat MySQL dans un tableau associatif.
	 *
	 * @return array
	 */
	public function fetchAssoc()
	{
		return ($this->query_result) ? mysql_fetch_assoc($this->query_result) : false;
	}

	/**
	 * Retourne une ligne de résultat MySQL sous la forme d'un objet.
	 *
	 * @return object
	 */
	public function fetchObject()
	{
		return ($this->query_result) ? mysql_fetch_object($this->query_result) : false;
	}

	/**
	 * Retourne une ligne de résultat MySQL sous la forme d'un tableau.
	 *
	 * @return array
	 */
	public function fetchRow()
	{
		return ($this->query_result) ? mysql_fetch_row($this->query_result) : false;
	}

	/**
	 * Retourne le nombre de lignes d'un résultat MySQL.
	 *
	 * @return integer
	 */
	public function numRows()
	{
		return ($this->query_result) ? mysql_num_rows($this->query_result) : false;
	}

	/**
	 * Retourne le nombre de champs d'un résultat MySQL.
	 *
	 * @return integer
	 */
	public function numFields()
	{
		return ($this->query_result) ? mysql_num_fields($this->query_result) : false;
	}

	/**
	 * Retourne le nombre de lignes affectées lors de la dernière opération.
	 *
	 * @return integer
	 */
	public function affectedRows()
	{
		return mysql_affected_rows($this->con_id);
	}

	/**
	 * Cette méthode renvoie le dernier ID inséré et créé par auto incrémentation.
	 *
	 * @return integer
	 */
	public function getLastID()
	{
		if ($this->con_id) {
			return mysql_insert_id($this->con_id);
		}

		return false;
	}

	/**
	 * Formate une chaîne de caractères pour la protéger lors de
	 * son insertion dans une requête SQL.
	 *
	 * @param	string	str			Chaîne à protéger
	 * @return	string
	 */
	public function escapeStr($str)
	{
		return mysql_real_escape_string($str, $this->con_id);
	}

	public function escapeSystem($str)
	{
		return '`'.$str.'`';
	}

	/**
	 * Echap les caractères spéciaux sauf si la chaine est une fonction MySQL.
	 *
	 * @param string Chaine à traiter
	 * @return string Retourne la chaine traitée
	 */
	public function escape($str)
	{
		return $this->checkFunction($str) ? $str : "'".$this->escapeStr($str)."'";
	}

	/**
	 * Vérifie si on utilise une fonction mysql ou non
	 *
	 * @param string Chaine à vérifier
	 * @return bool Retourne une fonction est utilisée ou non
	 */
	public function checkFunction($value)
	{
		$aMYSQL_FONCTION = array("NOW","CURDATE","CURTIME","DATE_ADD","DATE_SUB","STR_TO_DATE",
			"DATE_FORMAT","CONCAT","LOWER","UPPER","REPLACE","LEFT","MID","RIGHT","LTRIM","RTRIM",
			"TRIM","SUBSTRING","MD5","SHA1","PASSWORD","STR_TO_DATE");

		foreach ($aMYSQL_FONCTION as $fonc)
		{
			if (strpos($value, $fonc.'(') !== false) {
					return true;
			}
		}

		return false;
	}

	/**
	 * Protect string
	 *
	 * @param string $str
	 * @return string
	 */
	public function quote($str)
	{
		return '"'.$this->escapeStr($str).'"';
	}

	/**
	 * Query builder
	 *
	 * @param array $query
	 * @param string $rsClass
	 * @return
	 */
	public function builder($query, $rsClass='recordset')
	{
		$sql = '';

		if (isset($query['SELECT']))
		{
			$sql .= 'SELECT '.$query['SELECT'].' FROM '.$query['FROM'];

			if (isset($query['JOINS']))
			{
				foreach ($query['JOINS'] as $cur_join) {
					$aJoins[] = ' '.key($cur_join).' '.current($cur_join).' ON '.$cur_join['ON'];
				}
				$sql .= implode('',array_unique($aJoins));
			}

			if (!empty($query['WHERE'])) {
				$sql .= ' WHERE '.$query['WHERE'];
			}

			if (!empty($query['GROUP BY'])) {
				$sql .= ' GROUP BY '.$query['GROUP BY'];
			}

			if (!empty($query['HAVING'])) {
				$sql .= ' HAVING '.$query['HAVING'];
			}

			if (!empty($query['ORDER BY'])) {
				$sql .= ' ORDER BY '.$query['ORDER BY'];
			}

			if (!empty($query['LIMIT'])) {
				$sql .= ' LIMIT '.$query['LIMIT'];
			}
			return $this->select($sql,$comment,$rsClass);
		}
		elseif (isset($query['INSERT']))
		{
			$sql .= 'INSERT INTO '.$query['INTO'];

			if (!empty($query['INSERT'])) {
				$sql .= ' ('.$query['INSERT'].')';
			}

			$sql .= ' VALUES('.$query['VALUES'].')';
		}
		elseif (isset($query['UPDATE']))
		{
			$sql .= 'UPDATE '.$query['UPDATE'].' SET '.$query['SET'];

			if (!empty($query['WHERE'])) {
				$sql .= ' WHERE '.$query['WHERE'];
			}
		}
		elseif (isset($query['DELETE']))
		{
			$sql .= 'DELETE FROM '.$query['DELETE'];

			if (!empty($query['WHERE'])) {
				$sql .= ' WHERE '.$query['WHERE'];
			}
		}

		return $this->execute($sql);
	}

	/**
	 * @deprecated
	 * @param $words
	 * @return unknown_type
	 */
	public static function formatSQLSearchWords($words)
	{
		$strip = array('+',')','(',',','\'', '-', '_', '*', '.');
		$words = mb_strtolower($words);
		$words = str_replace($strip,' ',$words);
	//	$words = preg_replace('/( )+/',' ',$words);

	//	$lettre = preg_replace('/[\D]/', ' ', $words);
	//	$chiffre = preg_replace('/[0-9]/', ' ', $words);

	//	$words = trim($lettre).' '.trim($chiffre);
		$words = preg_replace('/\s+/s', ' ', $words);

		return trim($words);
	}

	public static function formatDateTime($sDate=null, $sOrder='dmyhis')
	{
		$sDate = trim($sDate);

		if (empty($sDate)) {
			return null;
		}

		return date('Y-m-d H:i:s', strtotime($sDate));
	}

	/**
	 * Returns a new instance of cursor class on <var>$table</var>
	 * for the current connection.
	 *
	 * @param string table 	Cursor table
	 * @return object cursor
	 */
	public function openCursor($table)
	{
		return new Cursor($this,$table);
	}

	/**
	 * Get the MySQL version
	 *
	 * @return array
	 */
	public function getVersion()
	{
		$result = $this->query('SELECT VERSION()');

		return array(
			'name'		=> 'MySQL Standard',
			'version'	=> preg_replace('/^([^-]+).*$/', '\\1', $this->result($result))
		);
	}

	public function tableExists($table_name)
	{
		$result = $this->query('SHOW TABLES LIKE \''.$this->escapeStr($table_name).'\'');
		return $this->numRows($result) > 0;
	}


	public function fieldExists($table_name, $field_name)
	{
		$result = $this->query('SHOW COLUMNS FROM '.$table_name.' LIKE \''.$this->escapeStr($field_name).'\'');
		return $this->numRows($result) > 0;
	}


	public function indexExists($table_name, $index_name)
	{
		$exists = false;

		$result = $this->query('SHOW INDEX FROM '.$table_name);
		while ($cur_index = $this->fetchAssoc($result))
		{
			if ($cur_index['Key_name'] == $table_name.'_'.$index_name)
			{
				$exists = true;
				break;
			}
		}

		return $exists;
	}


	public function safeString($str)
	{
		$str = $this->escapeStr($str);
		$str = addcslashes($str, '%_');
		$str = trim($str);
		$str = Utilities::escapeHTML($str);

		return $str;
	}
}
