<?php
/**
 * @brief Le module d'exemple d'ajout de champs supplémentaires au module Pages.
 *
 * Ceci est un module d'exemple. Un exemple d'ajout de champs supplémentaires au module Pages.
 *
 * Il n'est pas fait pour etre utilisé directement, mais pour que l'on s'en inspire.
 *
 * Il ajoute au module Pages les champs suivants :
 *
 *	- checkbox
 *	- date
 *	- required
 *	- multilangue
 *	- editor
 *
 *  ---------------------
 *  1. checkbox
 *  ---------------------
 *
 *  Ajout d'un champs type "case à cocher" dans l'onglet "Options".
 *
 *  Exemple d'utilisation dans les templates :
 *
 *  	<?php if ($rsPage->checkbox) : ?>
 *  		checked
 *  	<?php else : ?>
 *  		not checked
 *  	<?php endif; ?>
 *
 *
 *  ---------------------
 *  2. date
 *  ---------------------
 *
 *  Ajout d'un champs type "input text" avec datepicker dans l'onglet "Options".
 *
 *  Exemple d'utilisation dans les templates :
 *
 *  	<?php echo dt::dt2str(__('%A, %B %d, %Y'), $rsPage->date) ?>
 *
 *  	<?php echo dt::dt2str(__('%Y-%m-%d'), $rsPage->date) ?>
 *
 *
 *
 *  ---------------------
 *  3. required
 *  ---------------------
 *
 *  Ajout d'un champs de type "input text" dans l'onglet "Contenu" qui a la particularité d'etre requis.
 *
 *  Exemple d'utilisation dans les templates :
 *
 *  	<?php echo html::escapeHTML($rsPage->required) ?>
 *
 *
 *  ---------------------
 *  4. multilangue
 *  ---------------------
 *
 *  Ajout d'un champs de type "input text" multilangue dans l'onglet "Contenu".
 *
 *  Exemple d'utilisation dans les templates :
 *
 *  	<?php echo html::escapeHTML($rsPage->multilangue) ?>
 *
 *
 *  ---------------------
 *  5. editor
 *  ---------------------
 *
 *  Ajout d'un champs de type "textarea" multilangue avec un editeur dans l'onglet "Contenu".
 *
 *  Exemple d'utilisation dans les templates :
 *
 *  	<?php echo $rsPage->editor ?>
 *
 */
