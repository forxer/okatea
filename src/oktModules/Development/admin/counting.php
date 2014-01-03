<?php
/**
 * @ingroup okt_module_development
 * @brief Page de l'outil de comptage
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$oCountig = new countingFilesAndLines($okt->options->getRootPath());
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_development_counting_title'));

$okt->page->loader('.lazy-load');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<p><?php _e('m_development_counting_desc') ?></p>

<?php if (isset($oCountig)) : ?>
<ul>
	<li><?php printf(__('m_development_counting_total_folders'), util::formatNumber($oCountig->getNumFolders(),0)) ?></li>
	<li><?php printf(__('m_development_counting_total_files'), util::formatNumber($oCountig->getNumFiles(),0)) ?></li>
	<li><?php printf(__('m_development_counting_total_lines'), util::formatNumber($oCountig->getNumLines(),0)) ?></li>
</ul>
<?php endif; ?>

<form action="module.php" method="post">

	<p><?php echo form::hidden(array('m'),'development') ?>
	<?php echo form::hidden(array('action'), 'counting') ?>
	<?php echo form::hidden(array('form_sent'), 1) ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" class="lazy-load" value="<?php _e('m_development_counting_action') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>