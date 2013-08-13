<?php
/**
 * @ingroup okt_module_menus
 * @brief
 *
 */



# Accès direct interdit
if (!defined('ON_MENUS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/


/* Traitements
----------------------------------------------------------*/

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name'])  ? $_POST['p_name'] : array();

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'name' => $p_name
		);

		try
		{
			$okt->menus->config->write($new_conf);
			$okt->redirect('module.php?m=menus&action=config&updated=1');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_menus_configuration'));

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">

		<h3><?php _e('c_c_seo_help') ?></h3>

		<fieldset>
			<legend><?php _e('c_c_seo_identity_meta') ?></legend>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_intitle') : printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->menus->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->menus->config->name[$aLanguage['code']]) : '')) ?></p>
			<?php endforeach; ?>

		</fieldset>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','menus'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
