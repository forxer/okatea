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
	 * @param object $okt
	 *        	instance.
	 * @param string $path
	 *        	chemin du répertoire des modules à charger.
	 * @return void
	 */
	public function __construct($okt, $path)
	{
		parent::__construct($okt, $path);
		
		$this->type = 'module';
		
		$this->sCacheId = 'modules';
		$this->sCacheRepositoryId = 'modules_repositories';
		
		$this->sExtensionClassPatern = 'Okatea\\Modules\\%s\\Module';
		
		$this->sInstallerClass = 'Okatea\\Tao\\Extensions\\Modules\\Manage\\Installer';
	}

	/**
	 * Fonction de "pluralisation" des modules.
	 *
	 * @param integer $count        	
	 * @return string
	 */
	public static function pluralizeModuleCount($count)
	{
		if ($count == 1)
		{
			return __('c_a_modules_one_module');
		}
		elseif ($count > 1)
		{
			return sprintf(__('c_a_modules_%s_modules'), $count);
		}
		
		return __('c_a_modules_no_module');
	}
}
