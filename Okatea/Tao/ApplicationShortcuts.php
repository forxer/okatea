<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

class ApplicationShortcuts
{
	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 *
	 * @var object
	 */
	protected $error;

	/**
	 * Constructor;
	 *
	 * @param object $okt
	 *        	Okatea application instance.
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;

		$okt->startDatabase();
		$this->db = $okt->db;

		$this->error = $okt->error;
	}
}
