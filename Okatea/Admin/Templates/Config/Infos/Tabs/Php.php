<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<h3><?php _e('c_a_infos_php_title') ?></h3>

<ul>
	<li><a
		href="<?php echo $view->generateAdminUrl('config_infos') ?>?phpinfo=1">PHP
			info</a></li>
	<li><?php _e('c_a_infos_php_version')?> <?php echo $aPhpInfos['version'] ?></li>
	<li><?php _e('c_a_infos_php_zend')?> <?php echo $aPhpInfos['zend_version'] ?></li>
	<li><?php _e('c_a_infos_php_interface')?> <?php echo $aPhpInfos['sapi_type'] ?> <?php _e('c_a_infos_php_on')?> <?php echo $aPhpInfos['apache_version'] ?></li>
	<?php if (!empty($aPhpInfos['extensions'])) : ?>
	<li><?php _e('c_a_infos_php_extensions')?> <ul class="four-cols">
			<li class="col"><?php echo implode('</li><li class="col">',$aPhpInfos['extensions']) ?></li>
		</ul></li>
	<?php endif; ?>
</ul>
