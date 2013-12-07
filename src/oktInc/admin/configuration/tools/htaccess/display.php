<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion du .htacess (partie affichage)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Forms\StaticFormElements as form;


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


$okt->page->css->addCss('
	#tool-htaccess-form textarea {
		width: 100%;
	}
');

?>

<h3><?php _e('c_a_tools_htaccess_title') ?></h3>

<?php if (!$bHtaccessExists && $bHtaccessDistExists) : ?>
	<p><span class="icon error"></span> <?php printf(__('c_a_tools_htaccess_not_exists_can_create'), 'configuration.php?action=tools&amp;create_htaccess=1')?></p>
<?php else : ?>

<form id="tool-htaccess-form" action="configuration.php" method="post">
	<p class="field"><label for="p_htaccess_content"><?php _e('c_a_tools_htaccess_content') ?></label>
	<?php echo form::textarea('p_htaccess_content',80,20,$sHtaccessContent)?></p>

	<p><?php echo form::hidden(array('htaccess_form_sent'), 1) ?>
	<?php echo form::hidden(array('action'), 'tools') ?>
	<?php echo adminPage::formtoken() ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<p><span class="icon delete"></span><a href="configuration.php?action=tools&amp;delete_htaccess=1" onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_tools_htaccess_confirm_delete')) ?>')"><?php _e('c_a_tools_htaccess_delete') ?></a></p>
<?php endif; ?>
