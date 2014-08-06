<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Okatea\Tao\Application;

/**
 * Controller de base.
 */
class Controller
{
	protected $okt;

	protected $page;

	/**
	 * Constructor.
	 */
	public function __construct(Application $okt)
	{
		$this->okt = $okt;

		$this->page = & $okt->page;
	}

	/**
	 * Returns a RedirectResponse to the given URL.
	 *
	 * @param string $url The URL to redirect to
	 * @param integer $status The status code to use for the Response
	 * @param array $headers The headers (Location is always set to the given url)
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302, $headers = [])
	{
		return new RedirectResponse($url, $status, $headers);
	}

	public function jsonResponse($data = null, $status = 200, array $headers = [])
	{
		return new JsonResponse($data, $status, $headers);
	}

	/**
	 * Returns true if the template view exists.
	 *
	 * @param string|Symfony\Component\Templating\TemplateReferenceInterface $view
	 *        	A template name or a TemplateReferenceInterface instance
	 *
	 * @return Boolean true if the template view exists, false otherwise
	 */
	public function viewExists($view)
	{
		return $this->okt['tpl']->exists($view);
	}

	/**
	 * Returns a rendered view.
	 *
	 * @param string $view The view name
	 * @param array $parameters An array of parameters to pass to the view
	 *
	 * @return string The rendered view
	 */
	public function renderView($view, array $parameters = [])
	{
		return $this->okt['tpl']->render($view, $parameters);
	}

	/**
	 * Renders a view.
	 *
	 * @param string $view The view name
	 * @param array $parameters An array of parameters to pass to the view
	 * @param Response $response A response instance
	 *
	 * @return Response A Response instance
	 */
	public function render($view, array $parameters = [], Response $response = null)
	{
		if (null === $response) {
			$response = new Response();
		}

		return $this->okt['tpl']->renderResponse($view, $parameters, $response);
	}

	/**
	 * Streams a view.
	 *
	 * @param string $view The view name
	 * @param array $parameters An array of parameters to pass to the view
	 * @param StreamedResponse $response A response instance
	 *
	 * @return StreamedResponse A StreamedResponse instance
	 */
	public function stream($view, array $parameters = [], StreamedResponse $response = null)
	{
		$templating = $this->okt['tpl'];

		$callback = function () use($templating, $view, $parameters) {
			$templating->stream($view, $parameters);
		};

		if (null === $response) {
			return new StreamedResponse($callback);
		}

		$response->setCallback($callback);

		return $response;
	}

	/**
	 * Affichage page 401
	 */
	public function serve401()
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_UNAUTHORIZED);

		return $this->render('401', [], $response);
	}

	/**
	 * Affichage page 404
	 */
	public function serve404()
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_NOT_FOUND);

		return $this->render('404', [], $response);
	}

	/**
	 * Affichage page 503
	 */
	public function serve503()
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
		$response->headers->set('Retry-After', 3600);

		return $this->render('503', [], $response);
	}

	/**
	 * Remove trailing slash and redirect permanent
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function removeTrailingSlash()
	{
		$pathInfo = $this->okt['request']->getPathInfo();
		$requestUri = $this->okt['request']->getRequestUri();

		$url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

		return $this->redirect($url, Response::HTTP_MOVED_PERMANENTLY);
	}
}
