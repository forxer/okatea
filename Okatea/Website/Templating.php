<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Okatea\Website;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Okatea\Tao\Templating\Templating as BaseTemplating;

class Templating extends BaseTemplating
{
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
}
