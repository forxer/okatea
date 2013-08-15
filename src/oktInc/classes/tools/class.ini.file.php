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


/**
 * @class iniFile
 * @ingroup okt_classes_tools
 * @brief Permet de manipuler un fichier de configuration de type .ini
 *
 * Pour charger un fichier ini et transformer ses données en constantes,
 * utiliser simplement la méthode statique iniFile::read($fichier_ini)
 *
 * Pour manipuler un fichier il faut créer une instance de la classe.
 *
 * $objIni = new iniFile($fichier_ini);
 *
 * $objIni->editVar('nom_1', $valeur_1);
 * $objIni->editVar('nom_2', $valeur_2);
 *
 * $objIni->createVar('nom_3', $valeur_3, 'Un nouvelle variable de configuration');
 *
 * $objIni->saveFile();
 *
 */

class iniFile
{
	/**
	 * Le contenu du fichier de configuration
	 * @var string
	 * @access private
	 */
	private $content;

	/**
	 * Le modèle des lignes de configuration
	 * @var string
	 * @access private
	 */
	private $var_reg = '/^[\s]*(%s)[\s]*?=[\s*](.*)$/m';

	/**
	 * Constructeur. Charge le contenu d'un fichier de configuration.
	 *
	 * @access public
	 * @param	string	file	Le chemin du fichier de configuration à charger.
	 * @return void
	 */
	public function __construct($file)
	{
		if (file_exists($file))
		{
			$this->file = $file;
			$this->content = implode('',file($file));
		}
		else {
			$this->file = false;
		}
	}

	/**
	 * Modification d'une variable de configuration
	 *
	 * @access public
	 * @param	string	name		Le nom de la variable
	 * @param	mixed	value		La valeur de la variable
	 * @return void
	 */
	public function editVar($name,$value)
	{
		if ($this->file !== false)
		{
			$match = sprintf($this->var_reg,preg_quote($name));

			if (preg_match($match,$this->content))
			{
				$replace = '$1 = '.$value;
				$this->content = preg_replace($match,$replace,$this->content);
			}
			else {
				$this->createVar($name,$value);
			}
		}
	}

	/**
	 * Création d'une variable de configuration.
	 *
	 * @access public
	 * @param	string	name		Le nom de la variable
	 * @param	mixed	value		La valeur de la variable
	 * @param	string	comment		Un commentaire
	 * @return void
	 */
	public function createVar($name,$value,$comment='')
	{
		$match = sprintf($this->var_reg,preg_quote($name));

		if ($comment != '') {
			$comment = '; '.str_replace("\n","\n; ",$comment)."\n";
		}

		if (!preg_match($match,$this->content)) {
			$this->content .= "\n\n".$comment.$name.' = '.$value;
		}
	}

	/**
	 * Sauvegarde du fichier de configuration
	 *
	 * @access public
	 * @return boolean
	 */
	public function saveFile()
	{
		if (($fp = @fopen($this->file,'w')) !== false)
		{
			if (@fwrite($fp,$this->content,strlen($this->content)) !== false) {
				$res = true;
			}
			else {
				$res = false;
			}

			fclose($fp);
			return $res;
		}
		else {
			return false;
		}
	}

	public static function generateCacheFile($filesource,$filecache)
	{
		if (!file_exists($filesource)) {
			trigger_error('No config file',E_USER_WARNING);
			return false;
		}

		$ini_array = parse_ini_file($filesource);

		$res = '<?php'."\n\n";
		foreach ($ini_array as $k=>$v)
		{
			if (is_bool($v) || util::isInt($v)) {
				$res .= 'define(\''.$k.'\', '.$v.');'."\n\n";
			}
			else {
				$res .= 'define(\''.$k.'\', \''.addslashes($v).'\');'."\n\n";
			}
		}

		return file_put_contents($filecache,$res);
	}

	/**
	 * Méthode statique qui permet de créer des constantes des variables de configuration
	 * ou de retourner un tableau indexé
	 *
	 * @static
	 * @access public
	 * @param	string	file	Le chemin du fichier de configuration à lire
	 * @param	boolean	return	Si mis à TRUE, la fonction retourne les valeurs dans un tableau indexé
	 * @return void/array
	 */
	public static function read($file,$return=false)
	{
		if (!file_exists($file)) {
			trigger_error('No config file',E_USER_ERROR);
			exit;
		}

		if ($return) {
			$res = array();
		}

		$ini_array = parse_ini_file($file);

		foreach ($ini_array as $k=>$v)
		{
			if ($return) {
				$res[$k] = $v;
			}
			elseif (!defined($k)) {
				define($k,$v);
			}
		}

		if ($return) {
			return $res;
		}
	}

	public static function readCache($filesource,$filecache)
	{
		if (file_exists($filecache)) {
			require $filecache;
		}
		else {
			self::generateCacheFile($filesource,$filecache);
			require $filecache;
		}
	}

}
