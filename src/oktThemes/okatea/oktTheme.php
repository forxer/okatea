<?php
/**
 * @ingroup okt_theme_okatea
 * @brief La classe principale du thème Okatea.
 *
 */

class oktTheme extends oktThemeBase
{
	public function prepend()
	{
		# Add jQuery
		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');

		# CSS
		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/css/normalize.css');
		$this->okt->page->css->addLessFile(__DIR__.'/css/styles.less');
	}

} # class
