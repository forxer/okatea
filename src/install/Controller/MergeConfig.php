<?php
/*
 * This file is part of Okatea.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Okatea\Install\Controller;

use Okatea\Install\Controller;

class MergeConfig extends Controller
{
	public function page()
	{
		$bConfigMerged = false;

		/*
		if (file_exists(OKT_CONFIG_PATH.'/conf_site.yaml.bak'))
		{
			$aMergedConf = array_merge(
					(array)Yaml::parse(OKT_CONFIG_PATH.'/conf_site.yaml'),
					(array)Yaml::parse(OKT_CONFIG_PATH.'/conf_site.yaml.bak')
			);

			$okt->config->write($aMergedConf);

			util::deleteOktCacheFiles();

			unlink(OKT_CONFIG_PATH.'/conf_site.yaml.bak');

			$bConfigMerged = true;
		}
		*/

		return $this->render('MergeConfig', array(
			'bConfigMerged' => $bConfigMerged
		));
	}
}
