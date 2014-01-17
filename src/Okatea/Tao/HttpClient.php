<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao;

use Guzzle\Http\Client;

/**
 * We did this because in debug mode exceptions are caught by symfony\debug
 * same for 404 and we do not want that for the majority of request
 *
 */
class HttpClient extends Client
{
	public function __construct()
	{
		return parent::__construct('',
			array(
				'request.options' => array(
					'exceptions' => false
				)
		));
	}
}
