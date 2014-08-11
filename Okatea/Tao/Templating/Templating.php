<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Templating;

use Okatea\Tao\Html\Escaper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Helper\SlotsHelper;

/**
 * Le système de templating étendu de Symfony\Component\Templating\PhpEngine.
 */
class Templating extends PhpEngine
{
	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;

		$loader = new FilesystemLoader($this->okt['tpl_directories']);

		$loader->setLogger($this->okt['logger']);

		parent::__construct(new TemplateNameParser(), $loader);

		$this->set(new SlotsHelper());

		$this->addEscapers();

		$this->addGlobal('okt', $this->okt);
	}

	/**
	 * Generates a URL from the given parameters.
	 *
	 * @param string $route The name of the route
	 * @param mixed $parameters An array of parameters
	 * @param Boolean|string $referenceType sThe type of reference (one of the constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 *
	 * @see UrlGeneratorInterface
	 */
	public function generateUrl($route, $parameters = [], $language = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt['router']->generate($route, $parameters, $language, $referenceType);
	}

	/**
	 * Generates a admin URL from the given parameters.
	 *
	 * @param string $route The name of the route
	 * @param mixed $parameters An array of parameters
	 * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 *
	 * @see UrlGeneratorInterface
	 */
	public function generateAdminUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt['adminRouter']->generate($route, $parameters, $referenceType);
	}

	/**
	 * Renders a view and returns a Response.
	 *
	 * @param string $view The view name
	 * @param array $parameters An array of parameters to pass to the view
	 * @param Response $response A Response instance
	 *
	 * @return Response A Response instance
	 */
	public function renderResponse($view, array $parameters = [], Response $response = null)
	{
		if (null === $response) {
			$response = new Response();
		}

		$response->setContent($this->render($view, $parameters));

		return $response;
	}

	public function escapeJs($string)
	{
		return $this->escape($string, 'js');
	}

	public function escapeHtmlAttr($string)
	{
		return $this->escape($string, 'html_attr');
	}

	public function addEscapers()
	{
		$this->setEscaper('html', [
			'Okatea\Tao\Html\Escaper',
			'html'
		]);

		$this->setEscaper('html_attr', [
			'Okatea\Tao\Html\Escaper',
			'attribute'
		]);

		$this->setEscaper('js', [
			'Okatea\Tao\Html\Escaper',
			'js'
		]);
	}
}
