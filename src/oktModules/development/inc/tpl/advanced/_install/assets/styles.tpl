/**
 * Feuille de style pour le module éléments coté public
 *
 * 	- Filtres d'affichages
 * 	- Pagination
 *
 */


/* Filtres d'affichages
----------------------------------------------------------*/

###module_id##-filter-control a {
	background: transparent url(preview.png) no-repeat 0 40%;
	padding-left: 20px;
}

.##module_id##-filters-form {}

.##module_id##-filters-form input {
	color: #000;
	padding: 1px;
}

.##module_id##-filters-form fieldset {
	padding: 0.5em;
	margin-bottom: 2em;
	border: 1px solid #5D0708;
	border-left: none;
	border-right: none;
	background-color: #fdf2f2;
}
.##module_id##-filters-form legend {
	padding: 0 0.3em;
	color: #000000;
}

.##module_id##-filters-form p.field {
	margin: 0.5em 0;
	padding: 0;
}

.##module_id##-filters-form p.field label,
.##module_id##-filters-form p.field .fake-label {
	display: block;
}


/* Images et contenu
----------------------------------------------------------*/

###module_id##-images {
	float: left;
	margin: 0 1em 1em 0;
	width: 206px;
}
	###module_id##-images img {
		border: 2px solid #5d0708;
		margin: 0 2px 2px 0;
	}

###module_id##-content {
	line-height: 160%;
	text-align: justify;
}


/* Colonnes
----------------------------------------------------------*/

.two-cols, .three-cols, .four-cols {
	position: static;
	zoom: 1;
}
.two-cols:after, .three-cols:after, .four-cols:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}
.two-cols .col, .three-cols .col, .four-cols .col {
	float: left;
	margin-left: 1%;
	padding: 1px 0;
}

.two-cols .col {
	width: 48%;
}
.three-cols .col {
	width: 32%;
}
.four-cols .col {
	width: 24%;
}

.clearer {
	clear: both;
}

.floatLeft {
	float: left;
}

.floatLeftEspace {
	float: left;
	padding: 0 1em 1em 0;
}


/* Pagination
----------------------------------------------------------*/

.pagination { display: inline-block; zoom: 1; }
.pagination:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
/* required comment for clearfix to work in Opera \*/
* html .pagination { height:1%; }
.pagination { display:block; }
/* end clearfix */

.pagination {
	border: 0;
	margin: 1em 0;
	padding: 0;
	clear: both;
}
.pagination li {
	border: 0;
	margin: 0;
	padding: 0;
	list-style: none;
	margin-right: 2px;
	display: block;
	float: left;
}

.pagination .active,
.pagination a {
	border: 1px solid #5D0708;
	background-color: #fdf2f2;
	color: #5D0708;
	display: block;
	padding: 3px 6px;
	text-decoration: none;
}
.pagination .active {
	border: 1px solid #9E0C0C;
	background-color: #fdf2f2;
	color: #9E0C0C;
}
.pagination a:hover {
	border: 1px solid #fdf2f2;
	background-color: #5D0708;
	color: #fdf2f2;
}
