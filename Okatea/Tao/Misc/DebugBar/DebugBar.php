<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Misc\DebugBar;

use DebugBar\DebugBar as BaseDebugBar;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DataCollector\RequestDataCollector;
use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\ExceptionsCollector;
use Symfony\Component\HttpFoundation\Response;

class DebugBar extends BaseDebugBar
{

	protected $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;
		
		$this->setHttpDriver(new SymfonyHttpDriver($this->okt->session, $this->okt->response));
		
		$this->addCollector(new PhpInfoCollector());
		$this->addCollector(new MessagesCollector());
		$this->addCollector(new RequestDataCollector());
		$this->addCollector(new TimeDataCollector());
		$this->addCollector(new MemoryCollector());
		$this->addCollector(new ExceptionsCollector());
		
		$this->getRenderer();
	}

	public function getRenderer()
	{
		if ($this->jsRenderer === null)
		{
			$this->jsRenderer = new DebugBarRenderer($this->okt, $this);
		}
		
		return $this->jsRenderer;
	}

	public function shouldCollect($name, $default = false)
	{
		return true;
		//	return $this->app['config']->get('laravel-debugbar::config.collectors.'.$name, $default);
	}

	/**
	 * Starts a measure
	 *
	 * @param string $name
	 *        	Internal name, used to stop the measure
	 * @param string $label
	 *        	Public name
	 */
	public function startMeasure($name, $label = null)
	{
		if ($this->hasCollector('time'))
		{
			$this->getCollector('time')->startMeasure($name, $label);
		}
	}

	/**
	 * Stops a measure
	 *
	 * @param string $name        	
	 */
	public function stopMeasure($name)
	{
		if ($this->hasCollector('time'))
		{
			$this->getCollector('time')->stopMeasure($name);
		}
	}

	public function getStartedMeasures()
	{
		return $this->getCollector('time')->getStartedMeasures();
	}

	/**
	 * Adds a measure
	 *
	 * @param string $label        	
	 * @param float $start        	
	 * @param float $end        	
	 */
	public function addMeasure($label, $start, $end)
	{
		if ($this->hasCollector('time'))
		{
			$this->getCollector('time')->addMeasure($label, $start, $end);
		}
	}

	/**
	 * Utility function to measure the execution of a Closure
	 *
	 * @param string $label        	
	 * @param \Closure|callable $closure        	
	 */
	public function measure($label, \Closure $closure)
	{
		if ($this->hasCollector('time'))
		{
			$this->getCollector('time')->measure($label, $closure);
		}
	}

	/**
	 * Adds an exception to be profiled in the debug bar
	 *
	 * @param Exception $e        	
	 */
	public function addException(\Exception $e)
	{
		if ($this->hasCollector('exceptions'))
		{
			$this->getCollector('exceptions')->addException($e);
		}
	}

	/**
	 * Adds a message to the MessagesCollector
	 *
	 * A message can be anything from an object to a string
	 *
	 * @param mixed $message        	
	 * @param string $label        	
	 */
	public function addMessage($message, $label = 'info')
	{
		if ($this->hasCollector('messages'))
		{
			$this->getCollector('messages')->addMessage($message, $label);
		}
	}

	/**
	 * Injects the web debug toolbar into the given Response.
	 *
	 * @param \Symfony\Component\HttpFoundation\Response $response
	 *        	A Response instance
	 *        	Based on https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
	 */
	public function injectDebugbar(Response $response)
	{
		if (function_exists('mb_stripos'))
		{
			$posrFunction = 'mb_strripos';
			$substrFunction = 'mb_substr';
		}
		else
		{
			$posrFunction = 'strripos';
			$substrFunction = 'substr';
		}
		
		$content = $response->getContent();
		$pos = $posrFunction($content, '</body>');
		
		$renderer = $this->getJavascriptRenderer();
		$renderer->setOpenHandlerUrl('_debugbar/open');
		
		$debugbar = $renderer->renderHead() . $renderer->render();
		
		if (false !== $pos)
		{
			$content = $substrFunction($content, 0, $pos) . $debugbar . $substrFunction($content, $pos);
		}
		else
		{
			$content = $content . $debugbar;
		}
		
		$response->setContent($content);
	}

	/**
	 * Magic calls for adding messages
	 *
	 * @param string $method        	
	 * @param array $args        	
	 * @return mixed|void
	 */
	public function __call($method, $args)
	{
		$messageLevels = array(
			'emergency',
			'alert',
			'critical',
			'error',
			'warning',
			'notice',
			'info',
			'debug',
			'log'
		);
		
		if (in_array($method, $messageLevels))
		{
			$this->addMessage($args[0], $method);
		}
	}
}