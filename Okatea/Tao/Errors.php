<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao;

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
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
	<title>Fatal error</title>
	<style type="text/css">
	<!--
	body {MARGIN: 10% 20% auto 20%; font: 10px Verdana, Arial, Helvetica, sans-serif}
	#errorbox {BORDER: 1px solid #B84623}
	h2 {MARGIN: 0; COLOR: #FFFFFF; BACKGROUND-COLOR: #B84623; FONT-SIZE: 1.1em; PADDING: 5px 4px}
	#errorbox DIV {PADDING: 6px 5px; BACKGROUND-COLOR: #F1F1F1}
	-->
	</style>
</head>
<body>
	<div id="errorbox">
		<h2>Fatal error! Argh...</h2>
		<div>
		<?php
			if (is_array($mMessage))
			{
				echo "\t\t\t".'<ul>';
				foreach ($mMessage as $err)
					echo "\t\t\t\t".'<li>'.$err.'</li>'.PHP_EOL;
				echo "\t\t\t".'</ul>';
			}
			else {
				echo "\t\t\t".'<p>'.$mMessage.'</p>';
			}

			if (!empty($sDbError)) {
				echo "\t\t\t".'<p><strong>Database was reported:</strong><br />'.$sDbError.'</p>';
			}
		?>
		</div>
	</div>
</body>
</html><?php
		exit;
	}
}
