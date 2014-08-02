<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Okatea\Install\Controller;

use Symfony\Component\Yaml\Yaml;
use Okatea\Install\Controller;
use Okatea\Tao\Misc\Utilities;

class MergeConfig extends Controller
{

	public function page()
	{
		$bConfigMerged = false;
		
		$sConfigFile = $this->okt['config_dir'] . '/conf_site.yml';
		$sConfigFileBak = $this->okt['config_dir'] . '/conf_site.yml.bak';
		
		if (file_exists($sConfigFileBak))
		{
			$aMergedConf = array_merge(Yaml::parse($sConfigFile), Yaml::parse($sConfigFileBak));
			
			file_put_contents($sConfigFile, Yaml::dump($aMergedConf));
			
			Utilities::deleteOktCacheFiles();
			
			unlink($sConfigFileBak);
			
			$bConfigMerged = true;
		}
		
		return $this->render('MergeConfig', [
			'title' => __('i_merge_config_title'),
			'bConfigMerged' => $bConfigMerged
		]);
	}
}
