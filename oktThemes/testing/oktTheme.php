<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Themes\Theme;

class oktTheme extends Theme
{
	public function prepend()
	{
		# Ajout de jQuery
		$this->okt->page->js->addFile($this->okt->options->public_url.'/components/jquery/dist/jquery.min.js');

		# CSS
		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/init.css');
		$this->okt->page->css->addFile($this->url.'/css/styles.css');
	}
}
