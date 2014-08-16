<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

?>

<p><?php printf(__('i_start_about_'.$okt['session']->get('okt_install_process_type')), $okt->getVersion()) ?></p>

<p><?php _e('i_start_choose_lang') ?></p>
<ul id="languageChoice">
	<li><a href="<?php echo $view->generateInstallUrl('start') ?>?switch_language=fr"
		<?php if ($okt['session']->get('okt_install_language') == 'fr') echo ' class="current"'; ?>><img
		src="<?php echo $okt['public_url'] ?>/img/flags/fr.png" alt="" />
		français</a>
	</li>
	<li><a href="<?php echo $view->generateInstallUrl('start') ?>?switch_language=en"
		<?php if ($okt['session']->get('okt_install_language') == 'en') echo ' class="current"'; ?>><img
		src="<?php echo $okt['public_url'] ?>/img/flags/en.png" alt="" />
		english</a>
	</li>
</ul>

<form action="<?php echo $view->generateInstallUrl($okt->stepper->getCurrentStep()) ?>" method="post">
	<p class="note"><?php _e('i_start_click_next') ?></p>

	<p><?php echo $okt->page->formtoken()?>
	<input type="hidden" name="sended" value="1" />
	<input type="submit" value="<?php _e('c_c_next') ?>" /></p>
</form>
