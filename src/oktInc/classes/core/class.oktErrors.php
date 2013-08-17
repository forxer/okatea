<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktErrors
 * @ingroup okt_classes_core
 * @brief Permet de gérer des listes d'erreurs.
 *
 */
class oktErrors
{
	/**
	 * La pile d'erreurs
	 * @var array
	 */
	private $error;

	/**
	 * Constructeur. Cette méthode initialise l'objet error.
	 */
	public function __construct()
	{
		$this->reset();
	}

	/**
	 * Remet les erreurs à zéro.
	 */
	public function reset()
	{
		$this->error = array();
	}

	/**
	 * Retourne vrai si il y a des erreurs.
	 */
	public function notEmpty()
	{
		return (boolean) !empty($this->error);
	}

	/**
	 * alias de notEmpty
	 *
	 * @ref self::notEmpty()
	 */
	public function hasError()
	{
		return $this->notEmpty();
	}

	/**
	 * Retourne vrai si il n'y a pas d'erreurs.
	 */
	public function isEmpty()
	{
		return (boolean) empty($this->error);
	}

	/**
	 * Ajoute une erreur dans la pile des erreurs.
	 *
	 * @param	string	msg			Message
	 */
	public function set($message, $db_error='')
	{
		if (is_array($message))
		{
			foreach ($message as $m) {
				$this->error[] = array('message'=>$m, 'db'=>$db_error);
			}
		}
		else {
			$this->error[] = array('message'=>$message, 'db'=>$db_error);
		}
	}

	/**
	 * Récupère les erreurs et renvoie une chane ou FALSE si aucune erreur.
	 *
	 * Le paramètre $html indique si l'on souhaite obtenir les erreurs
	 * au format HTML
	 *
	 * @param	boolean	html	Au format HTML (true)
	 * @return	string/array
	 */
	public function get($html=true)
	{
		$nb_err = count($this->error);
		if ($nb_err > 0)
		{
			if (!$html) {
				return $this->error;
			}
			else
			{
				if ($nb_err > 1)
				{
					$res = '<ul>'.PHP_EOL;
					foreach($this->error as $v)
					{
						$res .= "\t".'<li><span class="errmsg">'.$v['message'].'</span>'.
								($v['db'] != '' ? '<br /><span class="errsql">'.$v['db'].'</span>' : '').
								'</li>'.PHP_EOL;
					}
					$res .= "</ul>\n";

					return $res;
				}
				else {
					return '<p class="errmsg">'.$this->error[0]['message'].'</p>'.PHP_EOL.
							($this->error[0]['db'] != '' ? '<p class="errsql">'.$this->error[0]['db'].'</p>'.PHP_EOL : '');
				}
			}
		}
		else {
			return null;
		}
	}

	public function fatal($msg, $db_error='')
	{
		self::affichefatal($msg, $db_error='');
	}

	/**
	 * Affiche une erreur fatale
	 *
	 * @param $msg Le message d'erreur fatale
	 * @param $db_error Le numero d'erreur
	 */
	public static function affichefatal($msg, $db_error='')
	{
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Erreur fatale</title>
	<style type="text/css">
	<!--
	BODY {MARGIN: 10% 20% auto 20%; font: 10px Verdana, Arial, Helvetica, sans-serif}
	#errorbox {BORDER: 1px solid #B84623}
	H2 {MARGIN: 0; COLOR: #FFFFFF; BACKGROUND-COLOR: #B84623; FONT-SIZE: 1.1em; PADDING: 5px 4px}
	#errorbox DIV {PADDING: 6px 5px; BACKGROUND-COLOR: #F1F1F1}
	-->
	</style>
	</head>
	<body>

	<div id="errorbox">
		<h2>Erreur fatale ! Argh...</h2>
		<div>
	<?php
		if (is_array($msg))
		{
			echo "\t\t".'<ul>';
			foreach ($msg as $err)
				echo "\t\t\t".'<li>'.$err.'</li>'.PHP_EOL;
			echo "\t\t".'</ul>';
		}
		else {
			echo '<p>'.$msg.'</p>';
		}

		if (!empty($db_error)) {
			echo '<p><strong>Database was reported:</strong><br />'.$db_error.'</p>';
		}
	?>
		</div>
	</div>

	</body>
	</html>
	<?php
		exit;
	}

} # class oktErrors
