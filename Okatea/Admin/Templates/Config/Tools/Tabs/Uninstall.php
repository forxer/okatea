<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('c_a_tools_uninstall_title') ?></h3>


<form id="tool-uninstall-form" action="<?php echo $view->generateAdminUrl('config_tools') ?>" method="post">

	<p><span class="icon error"></span> <?php _e('c_a_tools_uninstall_warning') ?></p>

	<p>
		<?php echo form::hidden(array('uninstall'), 1)?>
		<?php echo $okt->page->formtoken()?>
		<input type="submit"
		onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_uninstall_confirm')) ?>')"
		value="<?php _e('c_a_tools_uninstall_system') ?>" />
	</p>
