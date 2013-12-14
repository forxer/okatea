<?php
/**
 * @ingroup okt_module_rte_tinyMCE_3
 * @brief La page de configuration de tinyMCE 3
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_RTE_TINYMCE_3_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_width = !empty($_POST['p_width']) ? $_POST['p_width'] : '';
	$p_height = !empty($_POST['p_height']) ? $_POST['p_height'] : '';
	$p_content_css = !empty($_POST['p_content_css']) ? $_POST['p_content_css'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'width' => $p_width,
			'height' => $p_height,
			'content_css' => $p_content_css,
		);

		try
		{
			$okt->rte_tinymce_3->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=rte_tinymce_3&action=config');
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
$okt->page->addGlobalTitle('Configuration TinyMCE');

# Liste de fichiers CSS éventuellement utilisables
$aUsableCSS = array(
	OKT_THEME.'/css/style.css',
	OKT_THEME.'/css/styles.css',
	OKT_THEME.'/css/editor.css',
	OKT_THEME.'/css/custom.css',
	$okt->config->app_path.'css/style.css',
	$okt->config->app_path.'css/styles.css',
	$okt->config->app_path.'css/editor.css',
	$okt->config->app_path.'css/custom.css',
	$okt->config->app_path.'editor.css',
	$okt->config->app_path.'custom.css'
);


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">

	<div class="two-cols">
		<p class="field col"><label for="p_width">Largeur de l'editeur</label>
		<?php echo form::text('p_width', 10, 255, $okt->rte_tinymce_3->config->width) ?></p>

		<p class="field col"><label for="p_height">Hauteur de l'editeur</label>
		<?php echo form::text('p_height', 10, 255, $okt->rte_tinymce_3->config->height) ?></p>
	</div>

	<p class="field">Feuille de styles du contenu</p>
	<ul class="checklist">
		<li><?php echo form::radio(array('p_content_css','p_content_css_0'), 0, ($okt->rte_tinymce_3->config->content_css == 0))?> <label for="p_content_css_0"><?php _e('c_c_none_f') ?></label></li>
		<?php foreach ($aUsableCSS as $sCss) : ?>
		<li><?php echo form::radio(array('p_content_css','p_content_css_'.$sCss), $sCss, ($okt->rte_tinymce_3->config->content_css == $sCss), '', '', !file_exists($_SERVER['DOCUMENT_ROOT'].$sCss)) ?>
		<label for="p_content_css_<?php echo $sCss ?>"><?php echo $sCss ?></label></li>
		<?php endforeach; ?>
	</ul>

	<p><?php echo form::hidden('m','rte_tinymce_3'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="enregistrer" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
