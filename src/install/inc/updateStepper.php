<?php

if (!defined('OKT_INSTAL_PROCESS')) die;

$stepper = new oktStepper(array(
	array(
		'step' 		=> 'start',
		'title' 	=> __('i_step_start')
	),
	array(
		'step' 		=> 'merge_config',
		'title' 	=> __('i_step_merge_config')
	),
	array(
		'step' 		=> 'checks',
		'title' 	=> __('i_step_checks')
	),
	array(
		'step' 		=> 'db',
		'title' 	=> __('i_step_db')
	),
	array(
		'step' 		=> 'end',
		'title' 	=> __('i_step_end')
	)
));
