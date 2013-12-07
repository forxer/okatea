<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Html\Page;
use Tao\Core\Controller;

/**
 * Construction des pages publiques.
 *
 * @addtogroup Okatea
 *
 */
class publicPage extends Page
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
		global $okt;

		$oController = new Controller($okt);
		$oController->serve404();
	}

	public function serve503()
	{
		global $okt;

		$oController = new Controller($okt);
		$oController->serve503();
	}

} # class
