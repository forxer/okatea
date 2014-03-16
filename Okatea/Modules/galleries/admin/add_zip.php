<?php
/**
 * @ingroup okt_module_galleries
 * @brief Ajout d'éléments à partir d'un fichier zip
 *
 */

use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../Locales/'.$okt->user->language.'/admin.zip');

$iGalleryId = !empty($_REQUEST['gallery_id']) ? intval($_REQUEST['gallery_id']) : null;

$aItemLocalesData = array();

foreach ($okt->languages->list as $aLanguage)
{
	$aItemLocalesData[$aLanguage['code']] = array();
	$aItemLocalesData[$aLanguage['code']]['title'] = '';
}


/* Traitements
----------------------------------------------------------*/

#  ajout d'éléments
if (!empty($_POST['sended']))
{
	try
	{
		if (!$iGalleryId) {
			throw new Exception(__('m_galleries_zip_error_must_gallery_id'));
		}

		$rsGalleryLocales = $okt->galleries->tree->getGalleryI18n($iGalleryId);

		foreach ($okt->languages->list as $aLanguage)
		{
			while ($rsGalleryLocales->fetch())
			{
				if ($rsGalleryLocales->language == $aLanguage['code']) {
					$aItemLocalesData[$aLanguage['code']]['title'] = $rsGalleryLocales->title;
				}
			}

			if (!empty($_POST['p_title'][$aLanguage['code']])) {
				$aItemLocalesData[$aLanguage['code']]['title'] = $_POST['p_title'][$aLanguage['code']];
			}
		}

		Utilities::uploadStatus($_FILES['p_zip_file']);

		if (empty($_FILES['p_zip_file']) || empty($_FILES['p_zip_file']['tmp_name'])) {
			throw new Exception(__('m_galleries_zip_error_must_zip_file').' (no file)');
		}

		if (pathinfo($_FILES['p_zip_file']['name'],PATHINFO_EXTENSION) != 'zip') {
			throw new Exception(__('m_galleries_zip_error_must_zip_file').' (not zip extension)');
		}

	//	if ($_FILES['p_zip_file']['type'] != 'application/zip' && $_FILES['p_zip_file']['type'] != 'application/x-zip-compressed') {
	//	if (strpos($_FILES['p_zip_file']['type'], 'zip') === false) {
	//		throw new Exception(__('m_galleries_zip_error_must_zip_file').' (not zip type : '.$_FILES['p_zip_file']['type'].')');
	//	}

		$sTempDir = $okt->galleries->upload_dir.'/temp/';
		files::makeDir($sTempDir,true);

		$sZipFile = $sTempDir.$_FILES['p_zip_file']['name'];

		if (!move_uploaded_file($_FILES['p_zip_file']['tmp_name'], $sZipFile)) {
			throw new Exception(__('m_galleries_zip_error_unable_move_uploaded_file'));
		}

		$oZip = new fileUnzip($sZipFile);

		foreach ($oZip->getList() as $sFileName=>$aFileInfos)
		{
			$sFileExtension = pathinfo($sFileName,PATHINFO_EXTENSION);

			if ($aFileInfos['is_dir'] || !in_array(strtolower($sFileExtension), array('jpg','gif','png'))) {
				continue;
			}

			$iItemId = $okt->galleries->items->addItem($okt->galleries->items->openItemCursor(array(
				'gallery_id' => $iGalleryId,
				'active' => 1
			)), $aItemLocalesData);

			$sDestination = $okt->galleries->upload_dir.'/img/items/'.$iItemId.'/1.'.$sFileExtension;

			files::makeDir(dirname($sDestination), true);

			$oZip->unzip($sFileName, $sDestination);

			$aNewImagesInfos = $okt->galleries->items->getImageUploadInstance()->buildImagesInfos($iItemId, array(1=>basename($sDestination)));

			if (isset($aNewImagesInfos[1]))
			{
				$aNewItemImages = $aNewImagesInfos[1];
				$aNewItemImages['original_name'] = utf8_encode(basename($sFileName));
			}
			else {
				$aNewItemImages = array();
			}

			$okt->galleries->items->updImages($iItemId, $aNewItemImages);
		}

		$oZip->close();

		files::deltree($sTempDir);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}

	if ($okt->error->isEmpty())
	{
		$okt->galleries->items->regenMinImages($iGalleryId);

		http::redirect('module.php?m=galleries&action=items&gallery_id='.$iGalleryId);
	}
}


/* Affichage
----------------------------------------------------------*/

$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> ($iGalleryId
							? 'module.php?m=galleries&amp;action=items&amp;gallery_id='.$iGalleryId
							: 'module.php?m=galleries&amp;action=index'),
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

$okt->page->addGlobalTitle(__('m_galleries_zip_main_title'));


# Récupération de la liste complète des galeries
$rsGalleries = $okt->galleries->tree->getGalleries(array(
	'language' => $okt->user->language,
	'active' => 2
));


# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#items-title', '.lang-switcher-buttons');
}

# Loader
$okt->page->loader('.lazy-load');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('galleriesBtSt'); ?>

<form id="zip-upload-form" action="module.php" method="post" enctype="multipart/form-data">

	<div id="items-title" class="two-cols">
		<?php foreach ($okt->languages->list as $aLanguage) : ?>

		<p class="field col" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_galleries_zip_items_title') : printf(__('m_galleries_zip_items_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aItemLocalesData[$aLanguage['code']]['title'])) ?></p>

		<?php endforeach; ?>
	</div>

	<div class="two-cols">

		<p class="field col"><label for="gallery_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_galleries_zip_gallery') ?></label>
		<select id="gallery_id" name="gallery_id">
			<option value="0">&nbsp;</option>
			<?php
			while ($rsGalleries->fetch())
			{
				echo '<option value="'.$rsGalleries->id.'"'.
				($iGalleryId == $rsGalleries->id ? ' selected="selected"' : '').
				'>'.str_repeat('&nbsp;&nbsp;',$rsGalleries->level).
				'&bull; '.html::escapeHTML($rsGalleries->title).
				'</option>';
			}
			?>
		</select></p>

		<p class="field col"><label for="p_zip_file" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_galleries_zip_file') ?></label>
		<?php echo form::file('p_zip_file') ?>
		<span class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></span></p>
	</div>


	<p><?php echo form::hidden('m','galleries') ?>
	<?php echo form::hidden('action','add_zip') ?>
	<?php echo form::hidden('sended',1) ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" class="lazy-load" value="<?php _e('c_c_action_add') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
