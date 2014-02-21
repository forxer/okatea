<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Modules;

use Okatea\Tao\Extensions\Collection as BaseCollection;

class Collection extends BaseCollection
{
	/**
	 * Constructeur.
	 *
	 * @param	object	$okt		Okatea application instance.
	 * @param	string 	$path		Le chemin du répertoire des modules à charger.
	 * @return void
	 */
	public function __construct($okt, $path)
	{
		parent::__construct($okt, $path);

		$this->sCacheId = 'modules';
		$this->sCacheRepositoryId = 'modules_repositories';

		$this->sExtensionClassPatern = 'Okatea\\Modules\\%s\\Module';
	}
}
