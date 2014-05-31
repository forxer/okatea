<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

?>

<h3><?php _e('c_a_tools_uninstall_title') ?></h3>

<p><span class="icon error"></span> <?php _e('c_a_tools_uninstall_warning') ?></p>

<p>
	<a
		href="<?php echo $view->generateUrl('config_tools') ?>?uninstall=1"
		onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_uninstall_confirm')) ?>')"
		class="icon cross"><?php _e('c_a_tools_uninstall_system') ?></a>
</p>
