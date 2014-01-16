<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Themes\Theme;

class oktTheme extends Theme
{
	public function prepend()
	{
		# JS
		$this->okt->page->js->addFile($this->okt->options->public_url.'/components/jquery/jquery.min.js');
		$this->okt->page->js->addCCFile($this->okt->options->public_url.'/components/html5shiv/dist/html5shiv.js', 'lt IE 9');

		# CSS
		$this->okt->page->css->addFile($this->okt->options->public_url.'/components/normalize-css/normalize.css');
		$this->okt->page->css->addLessFile(__DIR__.'/css/styles.less');
	}
}
