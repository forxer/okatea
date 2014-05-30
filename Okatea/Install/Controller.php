<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Okatea\Tao\Controller as BaseController;

class Controller extends BaseController
{

	/**
	 * Constructor.
	 */
	public function __construct($okt)
	{
		parent::__construct($okt);

		# URL du dossier des fichiers publics
		$this->okt->options->set('public_url', $this->request->getBasePath() . '/../oktPublic');

		# URL du dossier upload depuis la racine
		$this->okt->options->set('upload_url', $this->request->getBasePath() . '/../oktPublic/upload');

		$this->page->css->addFile($this->okt->options->public_url . '/components/jquery-ui/themes/redmond/jquery-ui.min.css');
		$this->page->css->addFile($this->okt->options->public_url . '/css/init.css');
		$this->page->css->addFile($this->okt->options->public_url . '/css/admin.css');
		$this->page->css->addFile($this->okt->options->public_url . '/css/famfamfam.css');
		$this->page->css->addCSS(file_get_contents($this->okt->options->get('root_dir') . '/install/Assets/install.css'));

		$this->page->js->addFile($this->okt->options->public_url . '/components/jquery/dist/jquery.min.js');
		$this->page->js->addFile($this->okt->options->public_url . '/components/jquery-cookie/jquery.cookie.js');
		$this->page->js->addFile($this->okt->options->public_url . '/components/jquery-ui/ui/minified/jquery-ui.min.js');
		$this->page->js->addFile($this->okt->options->public_url . '/js/common_admin.js');
		$this->page->js->addFile($this->okt->options->public_url . '/plugins/blockUI/jquery.blockUI.min.js');
	}

	/**
	 * Generates a URL from the given parameters.
	 *
	 * @param string $route
	 *        	The name of the route
	 * @param mixed $parameters
	 *        	An array of parameters
	 * @param Boolean|string $referenceType
	 *        	The type of reference (one of the constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 *
	 * @see UrlGeneratorInterface
	 */
	public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt->router->generate($route, $parameters, $referenceType);
	}

	public function serve401()
	{
		return parent::serve401();
	}

	public function serve404()
	{
		return parent::serve404();
	}

	public function serve503()
	{
		return parent::serve503();
	}
}
