<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Routing;

use Symfony\Component\HttpFoundation\Request;

trait ControllerResolverTrait
{
	/**
	 * Invoque le gestionnaire de la route trouvée.
	 *
	 * @return void
	 */
	public function callController()
	{
		if (false !== ($callable = $this->getController($this->app->request))) {
			call_user_func($callable);
		}
		else {
			return false;
		}
	}

	/**
	 * Returns the Controller instance associated with a Request.
	 *
	 * This method looks for a '_controller' request attribute that represents
	 * the controller name (a string like ClassName::MethodName).
	 *
	 * @param Request $request A Request instance
	 *
	 * @return mixed|Boolean A PHP callable representing the Controller,
	 *                       or false if this resolver is not able to determine the controller
	 *
	 * @throws \InvalidArgumentException|\LogicException If the controller can't be found
	 *
	 * @api
	 */
	public function getController(Request $request)
	{
		if (!$controller = $request->attributes->get('_controller')) {
			if (null !== $this->logger) {
				$this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing');
			}

			return false;
		}

		if (is_array($controller) || (is_object($controller) && method_exists($controller, '__invoke'))) {
			return $controller;
		}

		if (false === strpos($controller, ':')) {
			if (method_exists($controller, '__invoke')) {
				return new $controller;
			} elseif (function_exists($controller)) {
				return $controller;
			}
		}

		$callable = $this->createController($controller);

		if (!is_callable($callable)) {
			throw new \InvalidArgumentException(sprintf('The controller for URI "%s" is not callable.', $request->getPathInfo()));
		}

		return $callable;
	}

	/**
	 * Returns a callable for the given controller.
	 *
	 * @param string $controller A Controller string
	 *
	 * @return mixed A PHP callable
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createController($controller)
	{
		if (false === strpos($controller, '::')) {
			throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
		}

		list($class, $method) = explode('::', $controller, 2);

		if (!class_exists($class)) {
			throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
		}

		$this->app->controller = new $class($this->app);

		return array($this->app->controller, $method);
	}
}
