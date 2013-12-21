<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil infos PHP (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


# PHP infos
$php_infos = array();
$php_infos['version'] =  function_exists('phpversion') ? phpversion() : 'n/a';
$php_infos['zend_version'] = function_exists('zend_version') ? zend_version() : 'n/a';
$php_infos['sapi_type'] = function_exists('php_sapi_name') ? php_sapi_name() : 'n/a';
$php_infos['apache_version'] = function_exists('apache_get_version') ? apache_get_version() : 'n/a';
$php_infos['extensions'] = (function_exists('get_loaded_extensions') ? (array)get_loaded_extensions() : array());

foreach ($php_infos['extensions'] as $k=>$e) {
	$php_infos['extensions'][$k] .= ' '.phpversion($e);
}

?>

<h3><?php _e('c_a_infos_php_title') ?></h3>

<ul>
	<li><a href="configuration.php?action=infos&amp;phpinfo=1">PHP info</a></li>
	<li><?php _e('c_a_infos_php_version')?> <?php echo $php_infos['version'] ?></li>
	<li><?php _e('c_a_infos_php_zend')?> <?php echo $php_infos['zend_version'] ?></li>
	<li><?php _e('c_a_infos_php_interface')?> <?php echo $php_infos['sapi_type'] ?> <?php _e('c_a_infos_php_on')?> <?php echo $php_infos['apache_version'] ?></li>
	<?php if (!empty($php_infos['extensions'])) : ?>
	<li><?php _e('c_a_infos_php_extensions')?> <ul class="four-cols">
		<li class="col"><?php echo implode('</li><li class="col">',$php_infos['extensions']) ?></li>
	</ul></li>
	<?php endif; ?>
</ul>
