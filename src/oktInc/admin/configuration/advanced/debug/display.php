<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée debug (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# Buttons
$okt->page->js->addReady('
	$("#debug_choices").buttonset();
');

?>

<h3><?php _e('c_a_config_advanced_tab_debug') ?></h3>

<div id="debug_choices">
	<?php echo form::checkbox('p_debug_enabled', 1, $okt->config->debug_enabled) ?>
	<label for="p_debug_enabled"><?php _e('c_a_config_advanced_enable_debug') ?></label>

	<?php echo form::checkbox('p_stop_redirect_on_error', 1, $okt->config->stop_redirect_on_error) ?>
	<label for="p_stop_redirect_on_error"><?php _e('c_a_config_advanced_debug_not_redirect') ?></label>

	<?php echo form::checkbox('p_xdebug_enabled', 1, $okt->config->xdebug_enabled, '', '', !OKT_XDEBUG) ?>
	<label for="p_xdebug_enabled"><?php _e('c_a_config_advanced_debug_xdebug') ?></label>
</div>

<p class="note"><span class="span_sprite ss_error"></span>
<?php if ($okt->modules->moduleExists('development')) : ?>
	<?php _e('c_a_config_advanced_debug_bar_note_1') ?>
<?php else : ?>
	<?php _e('c_a_config_advanced_debug_bar_note_2') ?>
<?php endif; ?>
</p>
