<?xml version="1.0" encoding="utf-8"?>

<database>

<!-- INSTALL -->

	<!-- mod_##module_id## -->
	<action id="mod_##module_id##" label="Create table %s" string="{{PREFIX}}mod_##module_id##">
		<test eq="neq" value="{{PREFIX}}mod_##module_id##" label="Table %s exists" type="wrn"
		string="{{PREFIX}}mod_##module_id##">SHOW TABLES LIKE '{{PREFIX}}mod_##module_id##'</test>

		CREATE TABLE `{{PREFIX}}mod_##module_id##` (
			`id` 					SERIAL,

			`visibility` 			TINYINT(1) 		UNSIGNED	NOT NULL DEFAULT 0,

			`title` 				VARCHAR(255) 				NULL,
			`slug` 					VARCHAR(255) 				NOT NULL,
			`title_tag` 			VARCHAR(255) 				NULL,

			`description` 			TEXT 						NULL,

			`created_at` 			DATETIME 					NULL,
			`updated_at` 			DATETIME 					NULL,

			`images` 				TEXT 						NULL,
			`files` 				TEXT 						NULL,

			`meta_description` 		VARCHAR(255) 				NULL,
			`meta_keywords` 		TEXT 						NULL,

			PRIMARY KEY (`id`),
			UNIQUE KEY `{{PREFIX}}mod_##module_id##_u_slug` (`slug`),
			KEY `{{PREFIX}}mod_##module_id##_idx_visibility` (`visibility`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci ;
	</action>


</database>
