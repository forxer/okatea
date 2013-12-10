<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Website;

use Tao\Html\Page as BasePage;
use Tao\Core\Controller;

/**
 * Construction des pages publiques.
 *
 * @addtogroup Okatea
 *
 */
class Page extends BasePage
{
	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($okt)
	{
		parent::__construct($okt,'public');
	}

	public function serve404()
	{
		$this->okt->request->attributes->set('_controller', 'Tao\Core\Controller::serve404');
	}

	public function serve503()
	{
		$this->okt->request->attributes->set('_controller', 'Tao\Core\Controller::serve503');
	}

} # class
