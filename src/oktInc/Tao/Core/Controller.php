<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tao\Core\Application;

/**
 * Controller de base.
 *
 */
class Controller
{
	protected $okt;
	protected $request;
	protected $session;
	protected $page;

	protected $sRequestedLanguage;

	/**
	 * Constructor.
	 *
	 */
	public function __construct(Application $okt)
	{
		$this->okt = $okt;

		# shortcuts
		$this->request =& $okt->request;
		$this->session =& $okt->session;
		$this->page =& $okt->page;

		// TODO : idéalement il faudrait faire des redirections vers la page demandée dans la langue demandée
		//$this->sRequestedLanguage = $this->setUserRequestLanguage();
	//	if ($this->setUserRequestLanguage()) {
	//		\http::redirect($this->okt->page->getBaseUrl());
	//	}
	}

	public function getRequestedLanguage()
	{
		return $this->sRequestedLanguage;
	}

	/**
	 * Change la langue de l'utilisateur en fonction de la requete URL
	 * et retourne la langue définie. Retourne false si pas de changement.
	 *
	 * @return string/boolean
	 */
	protected function setUserRequestLanguage()
	{
		static $sRequestedLanguage = null;

		if ($sRequestedLanguage !== null) {
			return $sRequestedLanguage;
		}

		$sRequestLanguage = $this->okt->router->getLanguage();

		if (empty($sRequestLanguage))
		{
			$sRequestedLanguage = false;
			return $sRequestedLanguage;
		}

		if ($sRequestLanguage === $this->okt->user->language)
		{
			$sRequestedLanguage = false;
			return $sRequestedLanguage;
		}

		if (!$this->okt->user->setUserLang($sRequestLanguage)) {
			$sRequestedLanguage = false;
		}
		else {
			$sRequestedLanguage = $sRequestLanguage;
		}

		return $sRequestedLanguage;
	}

	/**
	 * Indique si les arguments représentent la route par défaut.
	 *
	 * @param string $sClass
	 * @param string $sMethod
	 * @param string $sArgs
	 * @return boolean
	 */
	public function isDefaultRoute($sClass, $sMethod, $sArgs=null)
	{
		$bClass = $this->okt->config->default_route['class'] == $sClass;
		$bMethod = $this->okt->config->default_route['method'] == $sMethod;

		$bArgs = true;

		if (!is_null($sArgs)) {
			$bArgs = $this->okt->config->default_route['args'] == $sArgs;
		}

		if ($bClass && $bMethod && $bArgs) {
			return true;
		}

		return false;
	}

	/**
	 * Returns a RedirectResponse to the given URL.
	 *
	 * @param string  $url    The URL to redirect to
	 * @param integer $status The status code to use for the Response
	 * @param array   $headers The headers (Location is always set to the given url)
	 *
	 * @return RedirectResponse
	 */
	public function redirect($url, $status = 302, $headers = array())
	{
		return new RedirectResponse($url, $status, $headers);
	}

	/**
	 * Returns a rendered view.
	 *
	 * @param string $view       The view name
	 * @param array  $parameters An array of parameters to pass to the view
	 *
	 * @return string The rendered view
	 */
	public function renderView($view, array $parameters = array())
	{
		return $this->okt->tpl->render($view, $parameters);
	}

	/**
	 * Renders a view.
	 *
	 * @param string   $view       The view name
	 * @param array    $parameters An array of parameters to pass to the view
	 * @param Response $response   A response instance
	 *
	 * @return Response A Response instance
	 */
	public function render($view, array $parameters = array(), Response $response = null)
	{
		if (null === $response) {
			$response = new Response();
		}

		return $this->okt->tpl->renderResponse($view, $parameters, $response);
	}

	/**
	 * Streams a view.
	 *
	 * @param string           $view       The view name
	 * @param array            $parameters An array of parameters to pass to the view
	 * @param StreamedResponse $response   A response instance
	 *
	 * @return StreamedResponse A StreamedResponse instance
	 */
	public function stream($view, array $parameters = array(), StreamedResponse $response = null)
	{
		$templating = $this->okt->tpl;

		$callback = function () use ($templating, $view, $parameters) {
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
	 *
	 */
	public function serve401()
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_UNAUTHORIZED);

		return $this->render('401', array(), $response);
	}

	/**
	 * Affichage page 404
	 *
	 */
	public function serve404()
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_NOT_FOUND);

		return $this->render('404', array(), $response);
	}

	/**
	 * Affichage page 503
	 *
	 */
	public function serve503()
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
		$response->headers->set('Retry-After', 3600);

		return $this->render('503', array(), $response);
	}

	/**
	 * Remove trailing slash and redirect permanent
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function removeTrailingSlash()
	{
		$pathInfo = $this->request->getPathInfo();
		$requestUri = $this->request->getRequestUri();

		$url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

		return $this->redirect($url, Response::HTTP_MOVED_PERMANENTLY);
	}
}
