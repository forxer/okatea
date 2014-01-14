<?php
/**
 * @ingroup okt_module_catalog
 * @brief La page de configuration du module.
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Images\ImageUploadConfig;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$oImageUploadConfig = new ImageUploadConfig($okt,$okt->catalog->getImageUpload());
$oImageUploadConfig->setBaseUrl('module.php?m=catalog&amp;action=config&amp;');

$field_choice = array(
	'désactivé' => 0,
	'activé' => 1,
	'activé et obligatoire' => 2
);

$field_choice_simple = array(
	'désactivé' => 0,
	'activé' => 1
);

$field_choice_pnf = array(
	'désactivé' => 0,
	'boite à cocher' => 1,
	'dates' => 2
);

$p_chp_subtitle = $okt->catalog->config->fields['subtitle'];
$p_chp_content_short = $okt->catalog->config->fields['content_short'];
$p_chp_price = $okt->catalog->config->fields['price'];
$p_chp_promo = $okt->catalog->config->fields['promo'];
$p_chp_nouvo = $okt->catalog->config->fields['nouvo'];
$p_chp_favo = $okt->catalog->config->fields['favo'];



/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (!empty($_GET['minregen']))
{
	$okt->catalog->regenMinImages();

	$okt->page->flash->success(__('c_c_confirm_thumb_regenerated'));

	http::redirect('module.php?m=catalog&action=config');
}

# suppression filigrane
if (!empty($_GET['delete_watermark']))
{
	$okt->catalog->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));

	$okt->page->flash->success(__('c_c_confirm_watermark_deleted'));

	http::redirect('module.php?m=catalog&action=config');
}

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_categories_enable = !empty($_POST['p_categories_enable']) ? true : false;
	$p_seo_enable = !empty($_POST['p_seo_enable']) ? true : false;
	$p_enable_filters = !empty($_POST['p_enable_filters']) ? true : false;
	$p_rte_enable = !empty($_POST['p_rte_enable']) ? $_POST['p_rte_enable'] : '';

	$p_chp_subtitle = !empty($_POST['p_chp_subtitle']) ? intval($_POST['p_chp_subtitle']) : 0;
	$p_chp_content_short = !empty($_POST['p_chp_content_short']) ? intval($_POST['p_chp_content_short']) : 0;
	$p_chp_price = !empty($_POST['p_chp_price']) ? intval($_POST['p_chp_price']) : 0;

	$p_chp_promo = !empty($_POST['p_chp_promo']) ? intval($_POST['p_chp_promo']) : 0;
	$p_chp_nouvo = !empty($_POST['p_chp_nouvo']) ? intval($_POST['p_chp_nouvo']) : 0;
	$p_chp_favo = !empty($_POST['p_chp_favo']) ? intval($_POST['p_chp_favo']) : 0;

	$p_enable_files = !empty($_POST['p_enable_files']) ? true : false;
	$p_number_files = !empty($_POST['p_number_files']) ? intval($_POST['p_number_files']) : 0;
	$p_allowed_exts = !empty($_POST['p_allowed_exts']) ? $_POST['p_allowed_exts'] : '';

	$aImagesConfig = $oImageUploadConfig->getPostConfig();

	$p_name = !empty($_POST['p_name']) ? $_POST['p_name'] : 'Actualités';
	$p_title = !empty($_POST['p_title']) ? $_POST['p_title'] : '';
	$p_meta_description = !empty($_POST['p_meta_description']) ? $_POST['p_meta_description'] : '';
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'name' => $p_name,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'categories_enable' => (boolean)$p_categories_enable,
			'seo_enable' => (boolean)$p_seo_enable,
			'enable_filters' => (boolean)$p_enable_filters,
			'rte_enable' => $p_rte_enable,

			'fields' => array(
				'subtitle' => (integer)$p_chp_subtitle,
				'content_short' => (integer)$p_chp_content_short,
				'price' => (integer)$p_chp_price,
				'promo' => (integer)$p_chp_promo,
				'nouvo' => (integer)$p_chp_nouvo,
				'favo' => (integer)$p_chp_favo,
			),

			'files' => array(
				'enable' => (boolean)$p_enable_files,
				'number' => $p_number_files,
				'allowed_exts' => $p_allowed_exts
			),

			'images' => $aImagesConfig
		);

		try
		{
			$okt->catalog->config->write($new_conf);

			$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=catalog&action=config');
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
$okt->page->addGlobalTitle('Configuration');

# Lockable
$okt->page->lockable();

# Javascript
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');


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

				<p class="field"><label><?php echo form::checkbox('p_categories_enable',1,$okt->catalog->config->categories_enable) ?>
				Activer les categories</label></p>

				<p class="field"><label><?php echo form::checkbox('p_seo_enable',1,$okt->catalog->config->seo_enable) ?>
				<?php _e('c_c_enable_seo_help') ?></label></p>

				<p class="field"><label><?php echo form::checkbox('p_enable_filters',1,$okt->catalog->config->enable_filters) ?>
				Afficher les filtres sur la partie publique</label></p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_rte_enable">Éditeur de texte riche</label>
				<?php echo form::select('p_rte_enable',array_merge(array('Désactivé'=>0),$okt->page->getRteList(true)),$okt->catalog->config->rte_enable) ?></p>
			<?php else : ?>
				<p>Il n’y a aucun éditeur de texte riche de disponible.
				<?php echo form::hidden('p_rte_enable',0); ?></p>
			<?php endif;?>
			</fieldset>
		</div><!-- #tab_general -->

		<div id="tab_fields">
			<h3>Champs</h3>
			<fieldset class="three-cols">
				<legend><?php _e('c_c_Description')?></legend>
					<p class="field col"><label for="p_chp_subtitle"><?php _e('m_catalog_action_subtitle') ?></label>
					<?php echo form::select('p_chp_subtitle', $field_choice, $p_chp_subtitle) ?></p>

					<p class="field col"><label for="p_chp_content_short"><?php _e('m_catalog_action_content_short') ?></label>
					<?php echo form::select('p_chp_content_short', $field_choice, $p_chp_content_short) ?></p>

					<p class="field col"><label for="p_chp_price"><?php _e('m_catalog_action_price') ?></label>
					<?php echo form::select('p_chp_price', $field_choice, $p_chp_price) ?></p>
			</fieldset>

			<fieldset class="three-cols">
				<legend><?php _e('m_catalog_tab_status_title')?></legend>
					<p class="field col"><label for="p_chp_promo"><?php _e('m_catalog_action_promo') ?></label>
					<?php echo form::select('p_chp_promo', $field_choice_pnf, $p_chp_promo) ?></p>

					<p class="field col"><label for="p_chp_nouvo"><?php _e('m_catalog_action_nouvo') ?></label>
					<?php echo form::select('p_chp_nouvo', $field_choice_pnf, $p_chp_nouvo) ?></p>

					<p class="field col"><label for="p_chp_favo"><?php _e('m_catalog_action_favo') ?></label>
					<?php echo form::select('p_chp_favo', $field_choice_pnf, $p_chp_favo) ?></p>
			</fieldset>
		</div><!-- #tab_fields -->

		<div id="tab_files">
			<h3>Fichiers joints</h3>

			<h4>Images</h4>

			<?php echo $oImageUploadConfig->getForm(); ?>

			<h4>Autres fichiers</h4>

			<fieldset>
				<legend>Autres fichiers</legend>

				<p class="field"><label><?php echo form::checkbox('p_enable_files',1,$okt->catalog->config->files['enable']) ?>
				Activer les fichiers joints</label></p>

				<p class="field"><label for="p_number_files">Nombre de fichiers joints</label>
				<?php echo form::text('p_number_files', 10, 255, $okt->catalog->config->files['number']) ?></p>

				<p class="field"><label for="p_allowed_exts">Liste des extensions autorisées séparées par des virgules</label>
				<?php echo form::text('p_allowed_exts', 60, 255, $okt->catalog->config->files['allowed_exts']) ?></p>
			</fieldset>
		</div><!-- #tab_files -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<p class="field"><label for="p_name"><?php _e('c_c_seo_module_intitle') ?></label>
				<?php echo form::text('p_name', 40, 255, html::escapeHTML($okt->catalog->config->name)) ?></p>

				<p class="field"><label for="p_title"><?php _e('c_c_seo_module_title_tag') ?></label>
				<?php echo form::text('p_title', 40, 255, html::escapeHTML($okt->catalog->config->title)) ?></p>

				<p class="field"><label for="p_meta_description"><?php _e('c_c_seo_meta_desc') ?></label>
				<?php echo form::text('p_meta_description', 60, 255, html::escapeHTML($okt->catalog->config->meta_description)) ?></p>

				<p class="field"><label for="p_meta_keywords"><?php _e('c_c_seo_meta_keywords') ?></label>
				<?php echo form::textarea('p_meta_keywords', 57, 5, html::escapeHTML($okt->catalog->config->meta_keywords)) ?></p>

			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('m'),'catalog'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="enregistrer" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
