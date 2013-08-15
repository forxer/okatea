<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée chemins et URL (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

# Buttons
$okt->page->js->addReady('
	$("#p_internal_router").button();
');

?>

<h3><?php _e('c_a_config_advanced_tab_path_url') ?></h3>

<div class="two-cols">
	<p class="col field"><label for="p_app_path"><?php printf(__('c_a_config_advanced_app_path'), $okt->config->app_host) ?></label>
	<?php echo form::text('p_app_path', 40, 255, html::escapeHTML($okt->config->app_path)) ?></p>

	<p class="col field"><label for="p_domain"><?php _e('c_a_config_advanced_domain') ?></label>
	http://<?php echo form::text('p_domain', 60, 255, html::escapeHTML($okt->config->domain)) ?></p>
</div>

<p><?php echo form::checkbox('p_internal_router', 1, $okt->config->internal_router) ?>
<label for="p_internal_router"><?php _e('c_a_config_advanced_internal_router_enable') ?></label>
<span class="note"><?php _e('c_a_config_advanced_internal_router_info') ?></span></p>
