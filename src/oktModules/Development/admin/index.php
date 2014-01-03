<?php
/**
 * @ingroup okt_module_development
 * @brief Page de gestion du module dev
 *
 */

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Traitements
----------------------------------------------------------*/



/* Affichage
----------------------------------------------------------*/


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<p>Le module développement fournis plusieurs outils utiles lors du développement d’un site.</p>

<ul>
	<li>La barre de debug permet d’afficher des informations sur la page en cours d’affichage.</li>
	<li>L’amorçage de module permet de créer rapidement des bases de module.</li>
	<li>L'outil de comptage permet de compter les dossiers, fichiers et lignes de l’installation.</li>
</ul>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

