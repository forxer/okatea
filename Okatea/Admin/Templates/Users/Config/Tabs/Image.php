<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

?>

<h3><?php _e('c_a_users_config_tab_image_title') ?></h3>

<p class="field"><label><?php echo form::checkbox('p_users_gravatar_enabled', 1, $aPageData['config']['users']['gravatar']['enabled']) ?>
<?php printf(__('c_a_users_config_enable_gravatar_%s'), '<a href="https://'.$okt->user->language.'.gravatar.com/">Gravatar</a>') ?></label></p>

