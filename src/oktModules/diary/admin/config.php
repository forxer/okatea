<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Forms\Statics\FormElements as form;
use Tao\Images\ImageUploadConfig;

# Accès direct interdit
if (!defined('ON_DIARY_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$oImageUploadConfig = new ImageUploadConfig($okt,$okt->diary->getImageUpload());
$oImageUploadConfig->setBaseUrl('module.php?m=diary&amp;action=config&amp;');


/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (!empty($_GET['minregen']))
{
	$okt->diary->regenMinImages();
	http::redirect('module.php?m=diary&action=config&minregenerated=1');
}

# suppression filigrane
if (!empty($_GET['delete_watermark']))
{
	$okt->diary->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));
	http::redirect('module.php?m=diary&action=config&watermarkdeleted=1');
}

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_enable_metas = !empty($_POST['p_enable_metas']) ? true : false;
	$p_enable_rte = !empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';
	$p_enable_filters = !empty($_POST['p_enable_filters']) ? true : false;

	$p_chp_color = !empty($_POST['p_chp_color']) ? intval($_POST['p_chp_color']) : 0;
	$p_chp_disponibility = !empty($_POST['p_chp_disponibility']) ? intval($_POST['p_chp_disponibility']) : 0;

	$aImagesConfig = $oImageUploadConfig->getPostConfig();

	$p_enable_files = !empty($_POST['p_enable_files']) ? true : false;
	$p_number_files = !empty($_POST['p_number_files']) ? intval($_POST['p_number_files']) : 0;
	$p_allowed_exts = !empty($_POST['p_allowed_exts']) ? $_POST['p_allowed_exts'] : '';

	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name'])  ? $_POST['p_name'] : array();
	$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo'])  ? $_POST['p_name_seo'] : array();
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'enable_metas' => (boolean)$p_enable_metas,
			'enable_rte' => $p_enable_rte,
			'enable_filters' => (boolean)$p_enable_filters,

			'fields' => array(
				'color' => (integer)$p_chp_color,
				'disponibility' => (integer)$p_chp_disponibility,
			),

			'images' => $aImagesConfig,

			'files' => array(
				'enable' => (boolean)$p_enable_files,
				'number' => (integer)$p_number_files,
				'allowed_exts' => $p_allowed_exts
			),

			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords
		);

		try
		{
			$okt->diary->config->write($new_conf);
			http::redirect('module.php?m=diary&action=config&updated=1');
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


$aFieldChoices = util::getStatusFieldChoices();


# Titre de la page
$okt->page->addGlobalTitle(__('Configuration'));

# javascript
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));
$okt->page->messages->success('minregenerated',__('c_c_confirm_thumb_regenerated'));
$okt->page->messages->success('watermarkdeleted',__('c_c_confirm_watermark_deleted'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span>Général</span></a></li>
			<li><a href="#tab_fields"><span>Champs</span></a></li>
			<li><a href="#tab_files"><span>Fichiers joints</span></a></li>
			<li><a href="#tab_seo"><span>Référencement</span></a></li>
		</ul>

		<div id="tab_general">
			<h3>Général</h3>

			<fieldset>
				<legend>Fonctionnalités</legend>

				<p class="field"><label><?php echo form::checkbox('p_enable_metas',1,$okt->diary->config->enable_metas) ?>
				<?php _e('c_c_enable_seo_help') ?></label></p>

				<p class="field"><label><?php echo form::checkbox('p_enable_filters',1,$okt->diary->config->enable_filters) ?>
				Afficher les filtres sur la partie publique</label></p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_enable_rte">Éditeur de texte riche</label>
				<?php echo form::select('p_enable_rte',array_merge(array('Désactivé'=>0),$okt->page->getRteList(true)),$okt->diary->config->enable_rte) ?></p>
			<?php else : ?>
				<p>Il n’y a aucun éditeur de texte riche de disponible.
				<?php echo form::hidden('p_enable_rte',0); ?></p>
			<?php endif;?>
			</fieldset>

		</div><!-- #tab_general -->

		<div id="tab_fields">
			<h3>Champs</h3>

			<p class="field col"><label for="p_chp_color"><?php _e('m_diary_tab_options_color') ?></label>
			<?php echo form::select('p_chp_color', $aFieldChoices, $okt->diary->config->fields['color']) ?></p>

			<p class="field col"><label for="p_chp_disponibility"><?php _e('m_diary_tab_options_disponibility') ?></label>
			<?php echo form::select('p_chp_disponibility', $aFieldChoices, $okt->diary->config->fields['disponibility']) ?></p>


		</div><!-- #tab_general -->

		<div id="tab_files">
			<h3>Fichiers joints</h3>

			<h4>Images</h4>

			<?php echo $oImageUploadConfig->getForm(); ?>

			<h4>Autres fichiers</h4>

			<fieldset>
				<legend>Autres fichiers</legend>

				<p class="field"><label><?php echo form::checkbox('p_enable_files',1,$okt->diary->config->files['enable']) ?>
				Activer les fichiers joints</label></p>

				<p class="field"><label for="p_number_files">Nombre de fichiers joints</label>
				<?php echo form::text('p_number_files', 10, 255, $okt->diary->config->files['number']) ?></p>

				<p class="field"><label for="p_allowed_exts">Liste des extensions autorisées séparées par des virgules</label>
				<?php echo form::text('p_allowed_exts', 60, 255, $okt->diary->config->files['allowed_exts']) ?></p>
			</fieldset>
		</div><!-- #tab_files -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->diary->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->diary->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->diary->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->diary->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->diary->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->diary->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->diary->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->diary->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->diary->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->diary->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>
			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','diary'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
