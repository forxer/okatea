<?php
/**
 * @ingroup okt_theme_testing
 * @brief La classe principale du thème.
 *
 */

use Tao\Themes\Theme;

class oktTheme extends Theme
{
	public function prepend()
	{
		# Ajout de jQuery
		$this->okt->page->js->addFile($this->okt->options->public_url.'/components/jquery/jquery.min.js');

		# CSS
		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/init.css');
		$this->okt->page->css->addFile($this->url.'/css/styles.css');
	}

} # class
