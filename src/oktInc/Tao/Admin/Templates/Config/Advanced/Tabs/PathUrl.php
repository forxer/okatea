<?php
use Tao\Forms\Statics\FormElements as form;
?>

<h3><?php _e('c_a_config_advanced_tab_path_url') ?></h3>

<div class="two-cols">
	<p class="col field"><label for="p_app_path"><?php printf(__('c_a_config_advanced_app_path'), $okt->request->getSchemeAndHttpHost()) ?></label>
	<?php echo form::text('p_app_path', 40, 255, $view->escape($okt->config->app_path)) ?></p>

	<p class="col field"><label for="p_domain"><?php _e('c_a_config_advanced_domain') ?></label>
	http://<?php echo form::text('p_domain', 60, 255, $view->escape($okt->config->domain)) ?></p>
</div>
