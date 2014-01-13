<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc\DebugBar;

use DebugBar\DebugBar as BaseDebugBar;
use DebugBar\JavascriptRenderer;

class DebugBarRenderer extends JavascriptRenderer
{
	protected $okt;

	public function __construct($okt, DebugBar $debugBar, $baseUrl = null, $basePath = null)
	{
		$this->okt = $okt;

		parent::__construct($debugBar, $baseUrl, $basePath);

		$this->okt->triggers->registerTrigger('adminBeforeSendHeader', function(){
			$this->setIncludeVendors('css');

			list($cssFiles, $jsFiles) = $this->getAssetsFilenames();

			foreach ($cssFiles as $file) {
				$this->okt->page->css->addFile($this->okt->options->public_url.'/plugins/debugbar/'.$file);
			}

			foreach ($jsFiles as $file) {
				$this->okt->page->js->addFile($this->okt->options->public_url.'/plugins/debugbar/'.$file);
			}
		});

		$this->okt->triggers->registerTrigger('adminBeforeHtmlBodyEndTag', function(){
			echo $this->render();
		});
	}

	/**
	 * Returns needed asset files
	 *
	 * @return array
	 */
	public function getAssetsFilenames()
	{
		list($cssFiles, $jsFiles) = $this->getAssetFiles();
		return $this->filterAssetArray(array(
			$cssFiles,
			$jsFiles
		));
	}
}