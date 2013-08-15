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
	protected $stack;

	public function __construct()
	{
		$this->stack = array();
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
		$error_is_enabled = (bool)($errno & ini_get('error_reporting'));

		if ($error_is_enabled)
		{
			$params = array(
				'errno' => $errno,
				'file' => $errfile,
				'line' => $errline,
				'message' => $errstr
			);

			switch ($errno)
			{
				case E_ERROR:
				case E_USER_ERROR:
					$params['style'] = 'error';
					break;

				case E_WARNING:
				case E_USER_WARNING:
					$params['style'] = 'warning';
					break;

				case E_NOTICE:
				case E_USER_NOTICE:
					$params['style'] = 'warning';
					break;

				default:
					$params['style'] = 'error';
					break;
			}

			$this->addMessage($params);
		}

		# Ne pas exécuter le gestionnaire interne de PHP
		return true;
	}


	/* Gestion de la pile des messages de debug
	----------------------------------------------------------*/

	/**
	 * Ajoute un message à la pile.
	 *
	 * @param array $params
	 * @return void
	 */
	public function addMessage($params)
	{
		if (empty($params['style'])) {
			$params['style'] = 'info';
		}

		$aCompleteBacktrace = debug_backtrace();

		if (!empty($aCompleteBacktrace)) {
			$params['backtrace'] = $this->get_debug_print_backtrace(3);
		}

		if (OKT_XDEBUG)
		{
			if (empty($params['file'])) {
				$params['file'] = xdebug_call_file();
			}

			if (empty($params['line'])) {
				$params['line'] = xdebug_call_line();
			}

			$params['class'] = xdebug_call_class();
			$params['function'] = xdebug_call_function();
		}
		else
		{
			$trace = next($aCompleteBacktrace);

			if (empty($params['file'])) {
				$params['file'] = isset($trace['file']) ? $trace['file'] : '';
			}

			if (empty($params['line'])) {
				$params['line'] = isset($trace['line']) ? $trace['line'] : '';
			}

			$params['class'] = isset($trace['class']) ? $trace['class'] : '';
			$params['function'] = isset($trace['function']) ? $trace['function'] : '';
		}

		$this->stack[] = $params;
	}

	/**
	 * Retourne la pile de messages.
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->stack;
	}

	/**
	 * Retourne le nombre de messages.
	 *
	 * @return integer
	 */
	public function getNum()
	{
		return (integer)count($this->stack);
	}

	/**
	 * Retourne vrai si il n'y a pas de message.
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return (boolean) empty($this->stack);
	}

	/**
	 * Retourne vrai si il y a des message.
	 *
	 * @return boolean
	 */
	public function notEmpty()
	{
		return (boolean)!empty($this->stack);
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

	public function get_debug_print_backtrace($traces_to_ignore=1)
	{
		$traces = debug_backtrace();

		$ret = array();
		foreach ($traces as $i => $call)
		{
			if ($i < $traces_to_ignore ) {
				continue;
			}

			$object = '';
			if (isset($call['class']))
			{
				$object = $call['class'].$call['type'];
				if (is_array($call['args'])) {
					foreach ($call['args'] as &$arg) {
						$this->get_arg($arg);
					}
				}
			}
			elseif ($call['function'] == 'require' || $call['function'] == 'require_once' || $call['function'] == 'include' || $call['function'] == 'include_once' && isset($call['args'])) {
				$call['args'] = $call['args'][0];
			}

			$ret[] =
				'<p>#'.str_pad($i - $traces_to_ignore, 3, ' ').
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

	protected static function formatFileTrace($file)
	{
		return str_replace(
			array(OKT_ROOT_PATH,'\\'),
			array('','/'),
			$file
		);
	}

} # class oktDebug
