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

if (! function_exists('__'))
{

	/**
	 * Translate string.
	 */
	function __($str)
	{
		return isset($GLOBALS['okt_l10n'][$str]) ? $GLOBALS['okt_l10n'][$str] : $str;
	}
}

if (! function_exists('_e'))
{

	/**
	 * Translate and display string.
	 */
	function _e($str)
	{
		echo __($str);
	}
}

if (! function_exists('console'))
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
			$console = new Logger('console', array(
				new FirePHPHandler()
			), array(
				new WebProcessor(),
				new MemoryUsageProcessor(),
				new MemoryPeakUsageProcessor()
			));
		}
		
		$console->$name(var_export($mData, true));
	}
}

if (! function_exists('debug'))
{

	/**
	 * Utilitaire de debug rapide.
	 *
	 * @param mixed $mData
	 *        	La variable à déboguer
	 * @param boolean $bDie
	 *        	Kill the script after
	 * @return void
	 */
	function debug($mData, $bDie = false)
	{
		echo '<pre class="debug">';
		var_export($mData);
		echo '</pre>';
		
		if ($bDie)
		{
			die();
		}
	}
}

/**
 * Display a fatal error screen.
 *
 * @param mixed $mMessage
 *        	The fatal error message
 */
function oktFatalScreen($mMessage)
{
	header('Content-Type: text/html; charset=utf-8');
	
	?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
<title>Fatal error</title>
<style type="text/css">
<!--
body {
	margin: 10% 20% auto 20%;
	font: 14px Verdana, Arial, Helvetica, sans-serif;
}

#errorbox {
	background-color: #f1f1f1;
	border: 1px solid #c94c26;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}

h2 {
	margin: 0;
	color: #fff;
	background-color: #c94c26;
	font-size: 1.1em;
	padding: 4px 5px;
}

#errorbox div {
	padding: 0 5px;
}
-->
</style>
</head>
<body>
	<div id="errorbox">
		<h2>Fatal error! Aaaargh...</h2>
		<div>
		<?php
	if (is_array($mMessage))
	{
		echo '<ul>';
		
		foreach ($mMessage as $err)
		{
			echo '<li>' . $err . '</li>';
		}
		
		echo '</ul>';
	}
	else
	{
		echo '<p>' . $mMessage . '</p>';
	}
	?>
		</div>
	</div>
</body>
</html><?php
	
	die();
}

