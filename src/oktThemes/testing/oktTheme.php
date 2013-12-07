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
		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');

		# CSS
		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/css/init.css');
		$this->okt->page->css->addFile(OKT_THEME.'/css/styles.css');
	}

} # class
