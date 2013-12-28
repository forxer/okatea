<?php
/**
 * @ingroup okt_theme_okatea
 * @brief La classe principale du thème Okatea.
 *
 */

use Tao\Themes\Theme;

class oktTheme extends Theme
{
	public function prepend()
	{
		# JS
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/jquery.min.js');
		$this->okt->page->js->addCCFile($this->okt->options->public_url.'/plugins/html5shiv/dist/html5shiv.js', 'lt IE 9');

		# CSS
		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/normalize.css');
		$this->okt->page->css->addLessFile(__DIR__.'/css/styles.less');
	}

} # class
