<?php
/**
 * Configuration avancée dépôts (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# Buttons
$okt->page->js->addReady('
	$("#p_modules_repositories_enabled, #p_themes_repositories_enabled").button();
');

?>

<h3><?php _e('c_a_config_advanced_tab_repositories') ?></h3>

<p><?php _e('c_a_config_advanced_repo_replace_version') ?></p>

<h4><?php _e('c_a_config_advanced_modules_repositories') ?></h4>

<p><?php echo form::checkbox('p_modules_repositories_enabled', 1, $okt->config->modules_repositories_enabled) ?>
<label for="p_modules_repositories_enabled"><?php _e('c_a_config_advanced_enable_modules_repo') ?></label></p>

<table class="common">
	<thead><tr>
		<th scope="col"><?php _e('c_a_config_advanced_repo_name') ?></th>
		<th scope="col"><?php _e('c_a_config_advanced_repo_url') ?></th>
	</tr></thead>
	<tbody>
	<?php $line_count = 0;
	foreach ($aModulesRepositories as $repo_name=>$repo_url) :
		$odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		$line_count++;
	?>
	<tr>
		<th scope="row" class="<?php echo $odd_even ?> fake-td">
			<p><?php echo form::text(array('p_modules_repositories_names[]','p_modules_repositories_names_'.$line_count), 40, 255, html::escapeHTML($repo_name)) ?></p>
		</th>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_modules_repositories_urls[]','p_modules_repositories_urls_'.$line_count), 60, 255, html::escapeHTML($repo_url)) ?></p>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<th scope="row" class="<?php echo $odd_even ?> fake-td">
			<p><?php echo form::text(array('p_modules_repositories_names[]','p_modules_repositories_names_'.($line_count+1)), 40, 255) ?></p>
		</th>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_modules_repositories_urls[]','p_modules_repositories_urls_'.($line_count+1)), 60, 255) ?></p>
		</td>
	</tr>
	</tbody>
</table>


<h4><?php _e('c_a_config_advanced_themes_repositories') ?></h4>

<p><?php echo form::checkbox('p_themes_repositories_enabled', 1, $okt->config->themes_repositories_enabled) ?>
<label for="p_themes_repositories_enabled"><?php _e('c_a_config_advanced_enable_themes_repo') ?></label></p>

<table class="common">
	<thead><tr>
		<th scope="col"><?php _e('c_a_config_advanced_repo_name') ?></th>
		<th scope="col"><?php _e('c_a_config_advanced_repo_url') ?></th>
	</tr></thead>
	<tbody>
	<?php $line_count = 0;
	foreach ($aThemesRepositories as $repo_name=>$repo_url) :
		$odd_even = $line_count%2 == 0 ? 'even' : 'odd';
		$line_count++;
	?>
	<tr>
		<th scope="row" class="<?php echo $odd_even ?> fake-td">
			<p><?php echo form::text(array('p_themes_repositories_names[]','p_themes_repositories_names_'.$line_count), 40, 255, html::escapeHTML($repo_name)) ?></p>
		</th>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_themes_repositories_urls[]','p_themes_repositories_urls_'.$line_count), 60, 255, html::escapeHTML($repo_url)) ?></p>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<th scope="row" class="<?php echo $odd_even ?> fake-td">
			<p><?php echo form::text(array('p_themes_repositories_names[]','p_themes_repositories_names_'.($line_count+1)), 40, 255) ?></p>
		</th>
		<td class="<?php echo $odd_even ?>">
			<p><?php echo form::text(array('p_themes_repositories_urls[]','p_themes_repositories_urls_'.($line_count+1)), 60, 255) ?></p>
		</td>
	</tr>
	</tbody>
</table>
