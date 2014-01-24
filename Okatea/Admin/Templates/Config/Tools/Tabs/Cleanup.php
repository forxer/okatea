<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$okt->page->loader('.lazy-load');

?>

<h3><?php _e('c_a_tools_cleanup_title') ?></h3>

<p><?php _e('c_a_tools_cleanup_desc') ?></p>

<form action="<?php echo $view->generateUrl('config_tools') ?>" method="post">

	<ul class="checklist">
	<?php foreach ($aCleanableFiles as $fileId=>$fileName) : ?>
		<li><label for="cleanup_file_<?php echo $fileId ?>"><?php echo form::checkbox(array('cleanup[]', 'cleanup_file_'.$fileId), $fileId) ?> <?php echo $fileName ?></label></li>
	<?php endforeach; ?>
	</ul>

	<p><?php echo $okt->page->formtoken() ?>
	<input type="submit" class="lazy-load" value="<?php _e('c_c_action_delete') ?>" /></p>
</form>
