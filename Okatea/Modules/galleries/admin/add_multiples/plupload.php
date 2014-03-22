<?php
/**
 * @ingroup okt_module_galleries
 * @brief Ajout de plusieurs éléments d'un coup
 *
 */

use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../../../Locales/%s/admin.plupload');

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
	$okt->galleries->items->regenMinImages($iGalleryId);

	$okt->page->flash->success(__('m_galleries_items_added'));

	http::redirect('module.php?m=galleries&action=items&gallery_id='.$iGalleryId);
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

$okt->page->addGlobalTitle(__('m_galleries_plupload_add_items'));


# Récupération de la liste complète des galeries
$rsGalleries = $okt->galleries->tree->getGalleries(array('active' => 2));


# plupload
$okt->page->css->addFile($okt->options->public_url.'/components/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css');
$okt->page->js->addFile($okt->options->public_url.'/components/plupload/js/plupload.full.js');
$okt->page->js->addFile($okt->options->public_url.'/components/plupload/js/jquery.ui.plupload/jquery.ui.plupload.js');
$okt->page->js->addFile($okt->options->public_url.'/components/plupload/js/i18n/'.$okt->user->language.'.js');

$okt->page->js->addReady('

	$("#uploader").plupload({
		runtimes: "html5,html4",
		url: "'.$okt->options->modules_url.'/galleries/service_multiple_upload_plupload.php",
		filters: [ {title : "Image Files", extensions : "jpg,gif,png"} ],
		unique_names: false,
		preinit: attachCallbacks
	});

	function attachCallbacks(Uploader)
	{
		Uploader.bind("FileUploaded", function(Up, File, Response) {
			var obj = jQuery.parseJSON(Response.response);

			if (obj.error) {
				alert(obj.error.message);
				return false;
			}

		});
	};

	var uploader = $("#uploader").plupload("getUploader");

	uploader.bind("QueueChanged",function() {
		uploader.settings.multipart_params = {
			title: $("#p_title").val(),
			gallery_id: $("#gallery_id").val()
		};
	});

	$("#multiple-upload-form").submit(function(e) {

		// Files in queue upload them first
		if (uploader.files.length > 0) {
				// When all files are uploaded submit form
				uploader.bind("StateChanged", function() {
						if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
								$("#multiple-upload-form")[0].submit();
						}
				});

				uploader.start();
		} else {
				alert(\'You must queue at least one file.\');
		}

		e.preventDefault();
	});

	$(".plupload_start").hide();

');


# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#items-title', '.lang-switcher-buttons');
}

# Loader
$okt->page->loader('.lazy-load');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('galleriesBtSt'); ?>

<form id="multiple-upload-form" action="module.php" method="post">

	<div class="two-cols">
		<div id="items-title" class="col">
			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_galleries_plupload_items_title') : printf(__('m_galleries_plupload_items_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aItemLocalesData[$aLanguage['code']]['title'])) ?></p>

			<?php endforeach; ?>
		</div>

		<p class="field col"><label for="gallery_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_galleries_plupload_gallery') ?></label>
		<select id="gallery_id" name="gallery_id">
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
	</div>

	<div id="uploader"><p><?php _e('m_galleries_plupload_update_or_change_browser') ?></p></div>

	<p><?php echo form::hidden('m', 'galleries') ?>
	<?php echo form::hidden('action', 'add_multiples') ?>
	<?php echo form::hidden('sended', 1) ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" class="lazy-load" value="<?php _e('c_c_action_add') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
