<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Templating;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Okatea\Tao\Html\Escaper;

/**
 * Le système de templating étendu de Symfony\Component\Templating\PhpEngine.
 */
class Templating extends PhpEngine
{
	protected $okt;

	public function __construct($okt, $aTplDirectories)
	{
		$this->okt = $okt;

		$loader = new FilesystemLoader($aTplDirectories);

		$loader->setLogger($this->okt['logger']);

		parent::__construct(new TemplateNameParser(), $loader);

		$this->set(new SlotsHelper());

		$this->addEscapers();
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
