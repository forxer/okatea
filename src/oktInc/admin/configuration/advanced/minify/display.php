<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée minify (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# Toggle JS
$okt->page->toggleWithLegend('min_css_admin_title', 'min_css_admin_table');
$okt->page->toggleWithLegend('min_js_admin_title', 'min_js_admin_table');
$okt->page->toggleWithLegend('min_css_public_title', 'min_css_public_table');
$okt->page->toggleWithLegend('min_js_public_title', 'min_js_public_table');

?>

<h3><?php _e('c_a_config_advanced_tab_minify') ?></h3>

<p><?php printf(__('c_a_config_advanced_minify_instructions'),$okt->config->app_host) ?></p>

<p><?php _e('c_a_config_advanced_minify_replace') ?></p>
<ul>
	<li><?php printf(__('c_a_config_advanced_minify_replace_app_url'), $okt->config->app_host) ?></li>
	<li><?php printf(__('c_a_config_advanced_minify_replace_public_url'), OKT_PUBLIC_URL) ?></li>
	<li><?php printf(__('c_a_config_advanced_minify_replace_theme'), $okt->config->app_path.OKT_THEMES_DIR.'/'.$okt->config->theme) ?></li>
	<li><?php printf(__('c_a_config_advanced_minify_replace_mobile_theme'), $okt->config->app_path.OKT_THEMES_DIR.'/'.$okt->config->theme_mobile) ?></li>
	<li><?php printf(__('c_a_config_advanced_minify_replace_admin_theme'), $okt->config->admin_theme) ?></li>
	<li><?php printf(__('c_a_config_advanced_minify_replace_public_theme'), $okt->config->public_theme) ?></li>
</ul>

<h4 id="min_css_admin_title"><?php _e('c_a_config_advanced_minify_admin_css_files') ?></h4>
<table id="min_css_admin_table" class="common">
	<thead><tr>
		<th scope="col" class="left"><?php _e('c_a_config_advanced_minify_url_file') ?></th>
	</tr></thead>
	<tbody>
	<?php $line_count = 0;
	foreach ($aMinifyCssAdmin as $filename) :
		$odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		$line_count++;
	?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_css_admin[]','p_minify_css_admin_'.$line_count), 60, 255, html::escapeHTML($filename)) ?></p>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_css_admin[]','p_minify_css_admin_'.($line_count+1)), 60, 255) ?></p>
		</td>
	</tr>
	</tbody>
</table>

<h4 id="min_js_admin_title"><?php _e('c_a_config_advanced_minify_admin_js_files') ?></h4>
<table id="min_js_admin_table" class="common">
	<thead><tr>
		<th scope="col" class="left"><?php _e('c_a_config_advanced_minify_url_file') ?></th>
	</tr></thead>
	<tbody>
	<?php $line_count = 0;
	foreach ($aMinifyJsAdmin as $filename) :
		$odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		$line_count++;
	?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_js_admin[]','p_minify_js_admin_'.$line_count), 60, 255, html::escapeHTML($filename)) ?></p>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_js_admin[]','p_minify_js_admin_'.($line_count+1)), 60, 255) ?></p>
		</td>
	</tr>
	</tbody>
</table>

<h4 id="min_css_public_title"><?php _e('c_a_config_advanced_minify_public_css_files') ?></h4>
<table id="min_css_public_table" class="common">
	<thead><tr>
		<th scope="col" class="left"><?php _e('c_a_config_advanced_minify_url_file') ?></th>
	</tr></thead>
	<tbody>
	<?php $line_count = 0;
	foreach ($aMinifyCssPublic as $filename) :
		$odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		$line_count++;
	?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_css_public[]','p_minify_css_public_'.$line_count), 60, 255, html::escapeHTML($filename)) ?></p>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_css_public[]','p_minify_css_public_'.($line_count+1)), 60, 255) ?></p>
		</td>
	</tr>
	</tbody>
</table>

<h4 id="min_js_public_title"><?php _e('c_a_config_advanced_minify_public_js_files') ?></h4>
<table id="min_js_public_table" class="common">
	<thead><tr>
		<th scope="col" class="left"><?php _e('c_a_config_advanced_minify_url_file') ?></th>
	</tr></thead>
	<tbody>
	<?php $line_count = 0;
	foreach ($aMinifyJsPublic as $filename) :
		$odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		$line_count++;
	?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_js_public[]','p_minify_js_public_'.$line_count), 60, 255, html::escapeHTML($filename)) ?></p>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_minify_js_public[]','p_minify_js_public_'.($line_count+1)), 60, 255) ?></p>
		</td>
	</tr>
	</tbody>
</table>
