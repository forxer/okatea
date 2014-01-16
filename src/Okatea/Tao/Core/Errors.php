<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Core;

/**
 * Permet de gérer des listes d'erreurs.
 *
 */
class Errors
{
	/**
	 * La pile d'erreurs
	 * @var array
	 */
	protected $aErrors;


	/**
	 * Constructeur.
	 *
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
		$this->aErrors = array();
	}

	/**
	 * Retourne vrai si il y a des erreurs.
	 *
	 * @return boolean
	 */
	public function notEmpty()
	{
		return !empty($this->aErrors);
	}

	/**
	 * alias de notEmpty
	 *
	 * @ref self::notEmpty()
	 * @return boolean
	 */
	public function hasError()
	{
		return $this->notEmpty();
	}

	/**
	 * Retourne vrai si il n'y a pas d'erreurs.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->aErrors);
	}

	/**
	 * Ajoute une erreur dans la pile des erreurs.
	 *
	 * @param mixed $mMessage
	 * @param string $sDbError
	 * @return void
	 */
	public function set($mMessage, $sDbError='')
	{
		if (is_array($mMessage))
		{
			foreach ($mMessage as $m) {
				$this->aErrors[] = array('message'=>$m, 'db'=>$sDbError);
			}
		}
		else {
			$this->aErrors[] = array('message'=>$mMessage, 'db'=>$sDbError);
		}
	}

	/**
	 * Récupère les erreurs et renvoie une chane ou FALSE si aucune erreur.
	 *
	 * Le paramètre $bHtml indique si l'on souhaite obtenir les erreurs
	 * au format HTML.
	 *
	 * @param boolean $bHtml Au format HTML (true)
	 * @return multitype:array|string|NULL
	 */
	public function get($bHtml=true)
	{
		$nb_err = count($this->aErrors);
		if ($nb_err > 0)
		{
			if (!$bHtml) {
				return $this->aErrors;
			}
			else
			{
				if ($nb_err > 1)
				{
					$res = '<ul>'.PHP_EOL;
					foreach($this->aErrors as $v)
					{
						$res .= "\t".'<li><span class="errmsg">'.$v['message'].'</span>'.
								($v['db'] != '' ? '<br /><span class="errsql">'.$v['db'].'</span>' : '').
								'</li>'.PHP_EOL;
					}
					$res .= "</ul>\n";

					return $res;
				}
				else {
					return '<p class="errmsg">'.$this->aErrors[0]['message'].'</p>'.PHP_EOL.
							($this->aErrors[0]['db'] != '' ? '<p class="errsql">'.$this->aErrors[0]['db'].'</p>'.PHP_EOL : '');
				}
			}
		}
		else {
			return null;
		}
	}

	public function fatal($mMessage, $sDbError='')
	{
		self::fatalScreen($mMessage, $sDbError='');
	}

	/**
	 * Affiche une erreur fatale.
	 *
	 * @param mixed $mMessage 	Le message d'erreur fatale
	 * @param string $sDbError 	Le message d'erreur de la base de données
	 */
	public static function fatalScreen($mMessage, $sDbError='')
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
				if (is_array($mMessage))
				{
					echo "\t\t".'<ul>';
					foreach ($mMessage as $err)
						echo "\t\t\t".'<li>'.$err.'</li>'.PHP_EOL;
					echo "\t\t".'</ul>';
				}
				else {
					echo '<p>'.$mMessage.'</p>';
				}

				if (!empty($sDbError)) {
					echo '<p><strong>Database was reported:</strong><br />'.$sDbError.'</p>';
				}
			?>
				</div>
			</div>

			</body>
			</html>
			<?php
		exit;
	}
}
