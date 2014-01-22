<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Logger;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;

if (!function_exists('__'))
{
	/**
	 * Translate string.
	 *
	 */
	function __($str)
	{
		return isset($GLOBALS['okt_l10n'][$str]) ? $GLOBALS['okt_l10n'][$str] : $str;
	}
}

if (!function_exists('_e'))
{
	/**
	 * Translate and display string.
	 *
	 */
	function _e($str)
	{
		echo __($str);
	}
}

if (!function_exists('console'))
{
	/**
	 * Push data to firebug console.
	 *
	 * @param mixed $mData
	 * @return void
	 */
	function console($mData, $name = 'debug')
	{
		static $console = null;

		if (null === $console)
		{
			$console = new Logger('console',
				array(
					new FirePHPHandler()
				),
				array(
					new WebProcessor(),
					new MemoryUsageProcessor(),
					new MemoryPeakUsageProcessor()
				)
			);
		}

		$console->$name(var_export($mData, true));
	}
}

if (!function_exists('debug'))
{
	/**
	 * Utilitaire de debug rapide.
	 *
	 * @param mixed $mData 				La variable à déboguer
	 * @param boolean $bDie 			Kill the script after
	 * @return void
	 */
	function debug($mData, $bDie=false)
	{
		echo '<pre class="debug">';
		var_export($mData);
		echo '</pre>';

		if ($bDie) {
			die;
		}
	}
}
