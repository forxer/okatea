<?php

if (!defined('OKT_INSTAL_PROCESS')) die;


$stepper = new oktStepper(array(
	array(
		'step' 		=> 'start',
		'title' 	=> __('i_step_start')
	),
	array(
		'step' 		=> 'checks',
		'title' 	=> __('i_step_checks')
	),
	array(
		'step' 		=> 'db_conf',
		'title' 	=> __('i_step_db_conf')
	),
	array(
		'step' 		=> 'db',
		'title' 	=> __('i_step_db')
	),
	array(
		'step' 		=> 'supa',
		'title' 	=> __('i_step_supa')
	),
	array(
		'step' 		=> 'config',
		'title' 	=> __('i_step_config')
	),
	array(
		'step' 		=> 'theme',
		'title' 	=> __('i_step_theme')
	),
	array(
		'step' 		=> 'colors',
		'title' 	=> __('i_step_colors')
	),
	array(
		'step' 		=> 'modules',
		'title' 	=> __('i_step_modules')
	),
	array(
		'step' 		=> 'pages',
		'title' 	=> __('i_step_pages')
	),
	array(
		'step' 		=> 'end',
		'title' 	=> __('i_step_end')
	)
));
