<?php
/**
 * @ingroup okt_module_pages_example_extra_fields
 * @brief Fichier de définition du module
 *
 */

$this->register(array(
	'name' 			=> 'Pages extra fields',
	'desc' 			=> 'Example of adding extra fields in pages module',
	'version' 		=> '2.0-rc1',
	'author' 		=> 'okatea.org',
	'priority' 		=> 1010 # ce module doit être chargé après le module pages de façon a ajouter les champs supplémentaires
));
