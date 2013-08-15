<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil de nettoyage (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

?>

<h3><?php _e('c_a_tools_cleanup_title') ?></h3>

<p><?php _e('c_a_tools_cleanup_desc') ?></p>

<form action="configuration.php" method="post">

	<ul class="checklist">
	<?php foreach ($aCleanableFiles as $fileId=>$fileName) : ?>
		<li><label for="<?php echo $fileId ?>"><?php echo form::checkbox(array('cleanup[]'),$fileId)?> <?php echo $fileName ?></label></li>
	<?php endforeach; ?>
	</ul>

	<p><?php echo form::hidden(array('action'), 'tools') ?>
	<?php echo adminPage::formtoken() ?>
	<input type="submit" class="lazy-load" value="<?php _e('c_c_action_delete') ?>" /></p>
</form>
