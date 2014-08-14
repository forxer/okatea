<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Okatea\Install;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Okatea\Tao\Templating as BaseTemplating;

class Templating extends BaseTemplating
{

	protected $okt;

	public function __construct($okt, $aTplDirectories)
	{
		$this->okt = $okt;
		
		parent::__construct($okt, $aTplDirectories);
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
	public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt['installRouter']->generate($route, $parameters, $referenceType);
	}
}
