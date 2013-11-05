<?php
/**
 * Configuration du site générale (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

?>

<h3><?php _e('c_a_config_tab_general') ?></h3>

<?php foreach ($okt->languages->list as $aLanguage) : ?>

<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt->languages->unique ? _e('c_a_config_website_title') : printf(__('c_a_config_website_title_in_%s'), html::escapeHTML($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->config->title[$aLanguage['code']]) : '')) ?></p>

<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_desc_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_website_desc') : printf(__('c_a_config_website_desc_in_%s'), html::escapeHTML($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
<?php echo form::text(array('p_desc['.$aLanguage['code'].']','p_desc_'.$aLanguage['code']), 60, 255, (isset($okt->config->desc[$aLanguage['code']]) ? html::escapeHTML($okt->config->desc[$aLanguage['code']]) : '')) ?></p>

<?php endforeach; ?>
