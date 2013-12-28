<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil infos Okatea (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

# affichage changelog Okatea
if (!empty($_GET['show_changelog']) && file_exists($okt->options->getRootPath().'/CHANGELOG'))
{
	echo '<pre class="changelog">'.html::escapeHTML(file_get_contents($okt->options->getRootPath().'/CHANGELOG')).'</pre>';
	die;
}
