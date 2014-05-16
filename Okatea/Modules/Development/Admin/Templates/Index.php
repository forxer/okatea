<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('layout');

# Module title tag
$okt->page->addTitleTag(__('Development'));

# Start breadcrumb
$okt->page->addAriane(__('Development'), $view->generateUrl('Development_index'));

?>

<p>Le module développement fournis plusieurs outils utiles lors du
	développement d’un site.</p>

<ul>
	<li>La barre de debug permet d’afficher des informations sur la page en
		cours d’affichage.</li>
	<li>L’amorçage de module permet de créer rapidement des bases de
		module.</li>
	<li>L'outil de comptage permet de compter les dossiers, fichiers et
		lignes de l’installation.</li>
</ul>

