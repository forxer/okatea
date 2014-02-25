<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Themes\Okatea;

use Okatea\Tao\Extensions\Themes\Theme as baseTheme;

class Theme extends baseTheme
{
	public function prepend_public()
	{
		# JS
		$this->okt->page->js->addFile($this->okt->options->get('public_url').'/components/jquery/dist/jquery.min.js');
		$this->okt->page->js->addCCFile($this->okt->options->get('public_url').'/components/html5shiv/dist/html5shiv.js', 'lt IE 9');

		# CSS
		$this->okt->page->css->addFile($this->okt->options->get('public_url').'/components/normalize-css/normalize.css');
		$this->okt->page->css->addLessFile($this->public_path.'/css/styles.less');
	}
}
