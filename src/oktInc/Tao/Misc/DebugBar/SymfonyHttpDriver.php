<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc\DebugBar;

use DebugBar\HttpDriverInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
/**
 * HTTP driver for Symfony Request/Session
 */
class SymfonyHttpDriver implements HttpDriverInterface
{
	/** @var \Symfony\Component\HttpFoundation\Session\Session  */
	protected $session;
	/** @var \Symfony\Component\HttpFoundation\Response  */
	protected $response;

	public function __construct($session, $response = null){
		$this->session = $session;
		$this->response = $response;
	}

	/**
	 * {@inheritDoc}
	 */
	function setHeaders(array $headers)
	{
		if(!is_null($this->response)){
			$this->response->headers->add($headers);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	function isSessionStarted()
	{
		$this->session->start();
		return $this->session->isStarted();
	}

	/**
	 * {@inheritDoc}
	 */
	function setSessionValue($name, $value)
	{
		$this->session->set($name, $value);
	}

	/**
	 * {@inheritDoc}
	 */
	function hasSessionValue($name)
	{
		return $this->session->has($name);
	}

	/**
	 * {@inheritDoc}
	 */
	function getSessionValue($name)
	{
		return $this->session->get($name);
	}

	/**
	 * {@inheritDoc}
	 */
	function deleteSessionValue($name)
	{
		$this->session->remove($name);
	}
}