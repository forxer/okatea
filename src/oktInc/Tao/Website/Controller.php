<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Website;

use Tao\Core\Controller as BaseController;

class Controller extends BaseController
{
	/**
	 * Constructor.
	 *
	 */
	public function __construct($okt)
	{
		parent::__construct($okt);

		# Title tag
		$this->okt->page->addTitleTag($this->okt->page->getSiteTitleTag(null, $this->okt->page->getSiteTitle()));
	}
}
