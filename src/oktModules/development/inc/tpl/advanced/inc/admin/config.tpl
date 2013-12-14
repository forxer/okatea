<?php
##header##


use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Forms\Statics\FormElements as form;
use Tao\Images\ImageUploadConfig;

# Accès direct interdit
if (!defined('ON_##module_upper_id##_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$oImageUploadConfig = new ImageUploadConfig($okt,$okt->##module_id##->getImageUpload());
$oImageUploadConfig->setBaseUrl('module.php?m=##module_id##&amp;action=config&amp;');


/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (!empty($_GET['minregen']))
{
	$okt->##module_id##->regenMinImages();
	http::redirect('module.php?m=##module_id##&action=config&minregenerated=1');
}

# suppression filigrane
if (!empty($_GET['delete_watermark']))
{
	$okt->##module_id##->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));
	http::redirect('module.php?m=##module_id##&action=config&watermarkdeleted=1');
}

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_enable_metas = !empty($_POST['p_enable_metas']) ? true : false;
	$p_enable_rte = !empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';

	$aImagesConfig = $oImageUploadConfig->getPostConfig();

	$p_enable_files = !empty($_POST['p_enable_files']) ? true : false;
	$p_number_files = !empty($_POST['p_number_files']) ? intval($_POST['p_number_files']) : 0;
	$p_allowed_exts = !empty($_POST['p_allowed_exts']) ? $_POST['p_allowed_exts'] : '';

	$p_name = !empty($_POST['p_name']) ? $_POST['p_name'] : '';
	$p_title = !empty($_POST['p_title']) ? $_POST['p_title'] : '';
	$p_meta_description = !empty($_POST['p_meta_description']) ? $_POST['p_meta_description'] : '';
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : '';

	$p_public_list_url = !empty($_POST['p_public_list_url']) ? $_POST['p_public_list_url'] : '';
	$p_public_list_url = util::formatAppPath($p_public_list_url,false,false);
	$p_public_item_url = !empty($_POST['p_public_item_url']) ? $_POST['p_public_item_url'] : '';
	$p_public_item_url = util::formatAppPath($p_public_item_url,false,false);

	$p_public_list_file = !empty($_POST['p_public_list_file']) ? $_POST['p_public_list_file'] : '';
	$p_public_item_file = !empty($_POST['p_public_item_file']) ? $_POST['p_public_item_file'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'enable_metas' => (boolean)$p_enable_metas,
			'enable_rte' => $p_enable_rte,

			'images' => $aImagesConfig,

			'files' => array(
				'enable' => (boolean)$p_enable_files,
				'number' => (integer)$p_number_files,
				'allowed_exts' => $p_allowed_exts
			),

			'name' => $p_name,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'public_list_url' => $p_public_list_url,
			'public_item_url' => $p_public_item_url,
			'public_list_file' => $p_public_list_file,
			'public_item_file' => $p_public_item_file
		);

		try
		{
			$okt->##module_id##->config->write($new_conf);
			http::redirect('module.php?m=##module_id##&action=config&updated=1');
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
$okt->page->addGlobalTitle(__('Configuration'));

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));
$okt->page->messages->success('minregenerated',__('c_c_confirm_thumb_regenerated'));
$okt->page->messages->success('watermarkdeleted',__('c_c_confirm_watermark_deleted'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span>Général</span></a></li>
			<li><a href="#tab_files"><span>Fichiers joints</span></a></li>
			<li><a href="#tab_seo"><span>Référencement</span></a></li>
		</ul>

		<div id="tab_general">
			<h3>Général</h3>

			<fieldset>
				<legend>Fonctionnalités</legend>

				<p class="field"><label for="p_enable_metas"><?php echo form::checkbox('p_enable_metas',1,$okt->##module_id##->config->enable_metas) ?>
				<?php _e('c_c_enable_seo_help') ?></label></p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_enable_rte">Éditeur de texte riche</label>
				<?php echo form::select('p_enable_rte',array_merge(array('Désactivé'=>0),$okt->page->getRteList(true)),$okt->##module_id##->config->enable_rte) ?></p>
			<?php else : ?>
				<p>Il n’y a aucun éditeur de texte riche de disponible.
				<?php echo form::hidden('p_enable_rte',0); ?></p>
			<?php endif;?>
			</fieldset>

		</div><!-- #tab_general -->

		<div id="tab_files">
			<h3>Fichiers joints</h3>

			<h4>Images</h4>

			<?php echo $oImageUploadConfig->getForm(); ?>

			<h4>Autres fichiers</h4>

			<fieldset>
				<legend>Autres fichiers</legend>

				<p class="field"><label for="p_enable_files"><?php echo form::checkbox('p_enable_files',1,$okt->##module_id##->config->files['enable']) ?>
				Activer les fichiers joints</label></p>

				<p class="field"><label for="p_number_files">Nombre de fichiers joints</label>
				<?php echo form::text('p_number_files', 10, 255, $okt->##module_id##->config->files['number']) ?></p>

				<p class="field"><label for="p_allowed_exts">Liste des extensions autorisées séparées par des virgules</label>
				<?php echo form::text('p_allowed_exts', 60, 255, $okt->##module_id##->config->files['allowed_exts']) ?></p>
			</fieldset>
		</div><!-- #tab_files -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<p class="field"><label for="p_name"><?php _e('c_c_seo_module_intitle') ?></label>
				<?php echo form::text('p_name', 40, 255, html::escapeHTML($okt->##module_id##->getName())) ?></p>

				<p class="field"><label for="p_title"><?php _e('c_c_seo_module_title_tag') ?></label>
				<?php echo form::text('p_title', 40, 255, html::escapeHTML($okt->##module_id##->getTitle())) ?></p>

				<p class="field"><label for="p_meta_description"><?php _e('c_c_seo_meta_desc') ?></label>
				<?php echo form::text('p_meta_description', 60, 255, html::escapeHTML($okt->##module_id##->config->meta_description)) ?></p>

				<p class="field"><label for="p_meta_keywords"><?php _e('c_c_seo_meta_keywords') ?></label>
				<?php echo form::textarea('p_meta_keywords', 57, 5, html::escapeHTML($okt->##module_id##->config->meta_keywords)) ?></p>

			</fieldset>

			<fieldset>
				<legend>Schéma des URL</legend>

				<p class="field"><label for="p_public_list_url">URL de la liste des éléments depuis <code><?php echo $okt->config->app_url ?></code></label>
				<?php echo form::text('p_public_list_url', 40, 255, html::escapeHTML($okt->##module_id##->config->public_list_url)) ?></p>

				<p class="field"><label for="p_public_item_url">URL d’un élément depuis <code><?php echo $okt->config->app_url ?></code></label>
				<?php echo form::text('p_public_item_url', 40, 255, html::escapeHTML($okt->##module_id##->config->public_item_url)) ?></p>

			</fieldset>

			<fieldset>
				<legend>Noms des fichiers publics</legend>

				<div class="lockable">
					<p class="field"><label for="p_public_list_file">Fichier liste des éléments</label>
					<?php echo form::text('p_public_list_file', 40, 255, html::escapeHTML($okt->##module_id##->config->public_list_file)) ?></p>

					<p class="field"><label for="p_public_item_file">Fichier d’un élément</label>
					<?php echo form::text('p_public_item_file', 40, 255, html::escapeHTML($okt->##module_id##->config->public_item_file)) ?></p>
				</div>
			</fieldset>

			<h4><?php _e('c_c_seo_rewrite_rules') ?></h4>
<pre>
# start Okatea module ##module_name##
RewriteRule ^<?php echo html::escapeHTML($okt->##module_id##->config->public_item_url) ?>/(.+)$ <?php echo html::escapeHTML($okt->##module_id##->config->public_item_file) ?>?slug=$1 [QSA,L]
RewriteRule ^<?php echo html::escapeHTML($okt->##module_id##->config->public_list_url) ?>$ <?php echo html::escapeHTML($okt->##module_id##->config->public_list_file) ?> [QSA,L]
# end Okatea module ##module_name##
</pre>

			<?php if ($okt->checkPerm('tools')) : ?>
			<p><?php printf(__('c_c_seo_go_to_htaccess_modification_tool'), 'configuration.php?action=tools#tab-htaccess') ?></p>
			<?php endif; ?>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','##module_id##'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
