<?php
/**
 * @ingroup okt_theme_mobile
 * @brief La classe principale du thème.
 *
 */

class oktTheme extends oktThemeBase
{
	public function prepend()
	{
		# Ajout des fichiers CSS
		$this->okt->page->css->addFile('http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css');

		# Ajout des fichiers JS
		$this->okt->page->js->addFile('http://code.jquery.com/jquery-1.10.1.min.js');
		$this->okt->page->js->addFile('http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js');
	}

} # class
