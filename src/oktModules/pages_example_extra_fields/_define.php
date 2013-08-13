<?php
/**
 * @ingroup okt_module_pages_example_extra_fields
 * @brief Fichier de définition du module
 *
 */


$this->registerModule(
	/* Name */				"Pages extra fields",
	/* Description*/		"Example of adding extra fields in pages module",
	/* Version */			'1.0',
	/* Author */			"okatea.org",
	/* Priority */ 			1010 # ce module doit être chargé après le module pages de façon a ajouter les champs supplémentaires
);
