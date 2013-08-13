<?php
/**
 * Les fonctions
 *
 * @addtogroup Okatea
 *
 */



if (!function_exists('debug'))
{
	/**
	 * Utilitaire de debug rapide.
	 *
	 * @param mixed $mData 				La variable à déboguer
	 * @param boolean $bDie 			Kill the script after
	 * @param boolean $bDoNotUseXdebug 	Do not use Xdebug displaying
	 * @return void
	 */
	function debug($mData, $bDie=false, $bDoNotUseXdebug=false)
	{
		if (OKT_XDEBUG && !$bDoNotUseXdebug) {
			var_dump($mData);
		}
		else
		{
			echo '<pre class="debug">';
			var_export($mData);
			echo '</pre>';
		}

		if ($bDie) {
			die;
		}
	}
}

if (!function_exists('_e'))
{
	/**
	 * Translated string
	 *
	 * Display a translated string of $str. If translation is not found, display
	 * the string.
	 *
	 * @param string	$str		String to translate
	 * @return string
	 */
	function _e($str)
	{
		echo __($str);
	}
}


function oktShutdown()
{
	global $oktShutdown;

	if (is_array($oktShutdown))
	{
		foreach ($oktShutdown as $f)
		{
			if (is_callable($f)) {
				call_user_func($f);
			}
		}
	}

	if (session_id()) {
		session_write_close();
	}
}

