<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktDebug
 * @ingroup okt_classes_core
 * @brief Le gestionnaire de déboguage.
 *
 */
class oktDebug
{
	protected $aStack;

	public function __construct()
	{
		$this->aStack = array();
	}


	/* Error handling
	----------------------------------------------------------*/

	/**
	 * Internal error handler
	 *
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$bErrorIsEnabled = (bool)($errno & ini_get('error_reporting'));

		if ($bErrorIsEnabled)
		{
			$aParams = array(
				'errno' => $errno,
				'file' => $errfile,
				'line' => $errline,
				'message' => $errstr
			);

			switch ($errno)
			{
				case E_ERROR:
				case E_USER_ERROR:
					$aParams['style'] = 'error';
					break;

				case E_WARNING:
				case E_USER_WARNING:
					$aParams['style'] = 'warning';
					break;

				case E_NOTICE:
				case E_USER_NOTICE:
					$aParams['style'] = 'warning';
					break;

				default:
					$aParams['style'] = 'error';
					break;
			}

			$this->addMessage($aParams);
		}

		# Ne pas exécuter le gestionnaire interne de PHP
		return true;
	}


	/* Gestion de la pile des messages de debug
	----------------------------------------------------------*/

	/**
	 * Ajoute un message à la pile.
	 *
	 * @param array $aParams
	 * @return void
	 */
	public function addMessage($aParams)
	{
		if (empty($aParams['style'])) {
			$aParams['style'] = 'info';
		}

		$aCompleteBacktrace = debug_backtrace();

		if (!empty($aCompleteBacktrace)) {
			$aParams['backtrace'] = $this->get_debug_print_backtrace(3);
		}

		if (OKT_XDEBUG)
		{
			if (empty($aParams['file'])) {
				$aParams['file'] = xdebug_call_file();
			}

			if (empty($aParams['line'])) {
				$aParams['line'] = xdebug_call_line();
			}

			$aParams['class'] = xdebug_call_class();
			$aParams['function'] = xdebug_call_function();
		}
		else
		{
			$aTrace = next($aCompleteBacktrace);

			if (empty($aParams['file'])) {
				$aParams['file'] = isset($aTrace['file']) ? $aTrace['file'] : '';
			}

			if (empty($aParams['line'])) {
				$aParams['line'] = isset($aTrace['line']) ? $aTrace['line'] : '';
			}

			$aParams['class'] = isset($aTrace['class']) ? $aTrace['class'] : '';
			$aParams['function'] = isset($aTrace['function']) ? $aTrace['function'] : '';
		}

		$this->aStack[] = $aParams;
	}

	/**
	 * Retourne la pile de messages.
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->aStack;
	}

	/**
	 * Retourne le nombre de messages.
	 *
	 * @return integer
	 */
	public function getNum()
	{
		return count($this->aStack);
	}

	/**
	 * Retourne vrai si il n'y a pas de message.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->aStack);
	}

	/**
	 * Retourne vrai si il y a des message.
	 *
	 * @return boolean
	 */
	public function notEmpty()
	{
		return !empty($this->aStack);
	}

	/**
	 * alias de notEmpty
	 *
	 * @see self::notEmpty()
	 * @return boolean
	 */
	public function hasMessages()
	{
		return $this->notEmpty();
	}


	/* Méthodes utilitaires
	----------------------------------------------------------*/

	public function get_debug_print_backtrace($iTracesToIgnore=1)
	{
		$traces = debug_backtrace();

		$ret = array();
		foreach ($traces as $i => $call)
		{
			if ($i < $iTracesToIgnore) {
				continue;
			}

			$object = '';
			if (isset($call['class']))
			{
				$object = $call['class'].$call['type'];
				if (is_array($call['args']))
				{
					foreach ($call['args'] as &$arg) {
						$this->get_arg($arg);
					}
				}
			}
			elseif ($call['function'] == 'require' || $call['function'] == 'require_once' || $call['function'] == 'include' || $call['function'] == 'include_once' && isset($call['args'])) {
				$call['args'] = $call['args'][0];
			}

			$ret[] =
				'<p>#'.str_pad($i - $iTracesToIgnore, 3, ' ').
				'<strong>'.$object.$call['function'].'</strong>( '.
	//			print_r($call['args'],true).' )<br />'.
				'called at ['.(isset($call['file']) ? self::formatFileTrace($call['file']) : '?').
				':'.(isset($call['line']) ? $call['line'] : '?').']</p>';
		}

		return implode('<hr />',$ret);
	}

	protected function get_arg(&$arg)
	{
		if (is_object($arg))
		{
			$arr = (array)$arg;
			$args = array();

			foreach ($arr as $key => $value)
			{
				if (strpos($key, chr(0)) !== false) {
					$key = ''; // Private variable found
				}

			//	$args[] =  '['.$key.'] => '.$this->get_arg($value);
			}

			$arg = get_class($arg) . ' Object ('.implode(',', $args).')';
		}
	}

	protected static function formatFileTrace($sFile)
	{
		return str_replace(
			array(OKT_ROOT_PATH, '\\'),
			array('', '/'),
			$sFile
		);
	}

} # class oktDebug
