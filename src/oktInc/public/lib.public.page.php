<?php
/**
 * Construction des pages publiques.
 *
 * @addtogroup Okatea
 *
 */

class publicPage extends htmlPage
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

		$oController = new oktController($okt);
		$oController->serve404();
	}

	public function serve503()
	{
		global $okt;

		$oController = new oktController($okt);
		$oController->serve503();
	}

} # class
