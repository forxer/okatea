<?php
/**
 * @ingroup okt_module_media_manager
 * @brief Media manager item.
 *
 * Fork of dcMedia Dotclear media manage
 *
 */

use Tao\Forms\StaticFormElements as form;

# Accès direct interdit
if (!defined('ON_MEDIA_MANAGER_MODULE')) die;

$file = null;
$popup = (integer) !empty($_GET['popup']);
$page_url = 'module.php?m=media_manager&action=item&popup='.$popup;
$media_page_url = 'module.php?m=media_manager&popup='.$popup;

$id = !empty($_REQUEST['id']) ? (integer) $_REQUEST['id'] : '';

$core_media_writable = false;
try
{
	$okt->media = new oktMedia($okt);

	if ($id) {
		$file = $okt->media->getFile($id);
	}

	if ($file === null) {
		throw new Exception(__('Not a valid file'));
	}

	$okt->media->chdir(dirname($file->relname));
	$core_media_writable = $okt->media->writable();

	# Prepare directories combo box
	$dirs_combo = array();
	foreach ($okt->media->getRootDirs() as $v)
	{
		if ($v->w) {
			$dirs_combo['/'.$v->relname] = $v->relname;
		}
	}
	ksort($dirs_combo);
}
catch (Exception $e) {
	$okt->error->set($e->getMessage());
}

# Upload a new file
if ($file && !empty($_FILES['upfile']) && $file->editable && $core_media_writable)
{
	try
	{
		util::uploadStatus($_FILES['upfile']);

		$okt->media->uploadFile($_FILES['upfile']['tmp_name'], $file->basename, null, false, true);

		$okt->page->flashMessages->addSuccess(__('File has been successfully updated.'));

		http::redirect($page_url.'&id='.$id);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Update file
if ($file && !empty($_POST['media_file']) && $file->editable && $core_media_writable)
{
	$newFile = clone $file;

	$newFile->basename = $_POST['media_file'];

	if ($_POST['media_path']) {
		$newFile->dir = $_POST['media_path'];
		$newFile->relname = $_POST['media_path'].'/'.$newFile->basename;
	} else {
		$newFile->dir = '';
		$newFile->relname = $newFile->basename;
	}
	$newFile->media_title = $_POST['media_title'];
	$newFile->media_dt = strtotime($_POST['media_dt']);
	$newFile->media_dtstr = $_POST['media_dt'];
	$newFile->media_priv = !empty($_POST['media_private']);

	try
	{
		$okt->media->updateFile($file,$newFile);

		$okt->page->flashMessages->addSuccess(__('File has been successfully updated.'));

		http::redirect($page_url.'&id='.$id);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Update thumbnails
if (!empty($_POST['thumbs']) && $file->media_type == 'image' && $file->editable && $core_media_writable)
{
	try
	{
		$okt->media->imageThumbCreate(null, $file->basename);

		$okt->page->flashMessages->addSuccess(__('Thumbnails have been successfully updated.'));

		http::redirect($page_url.'&id='.$id);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Unzip file
if (!empty($_POST['unzip']) && $file->type == 'application/zip' && $file->editable && $core_media_writable)
{
	try
	{
		$unzip_dir = $okt->media->inflateZipFile($file,$_POST['inflate_mode'] == 'new');

		$okt->page->flashMessages->addSuccess(__('Zip file has been successfully extracted.'));

		http::redirect($media_page_url.'&d='.$unzip_dir);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Function to get image title based on meta
function dcGetImageTitle($file, $pattern)
{
	return $file->media_title;
	$res = array();
	$pattern = preg_split('/\s*;;\s*/',$pattern);
	$sep = ', ';

	foreach ($pattern as $v)
	{
		if ($v == 'Title') {
			$res[] = $file->media_title;
		}
		elseif ($file->media_meta->{$v}) {
			$res[] = (string) $file->media_meta->{$v};
		}
		elseif (preg_match('/^Date\((.+?)\)$/u',$v,$m)) {
			$res[] = dt::str($m[1],$file->media_dt);
		}
		elseif (preg_match('/^DateTimeOriginal\((.+?)\)$/u',$v,$m) && $file->media_meta->DateTimeOriginal) {
			$res[] = dt::dt2str($m[1],(string) $file->media_meta->DateTimeOriginal);
		}
		elseif (preg_match('/^separator\((.*?)\)$/u',$v,$m)) {
			$sep = $m[1];
		}
	}

	return implode($sep, $res);
}

/* Affichage
-------------------------------------------------------- */

if ($popup)
{
	/*
	$okt->page->js->addFile(OKT_MODULES_URL.'/rte_tinymce/tinyMCE_jquery/tiny_mce_popup.js');


	$sReadyStr = '
	var FileBrowserDialogue = {
		init : function () {
			// Here goes your code for setting your custom things onLoad.
		},
		mySubmit : function () {
	';

	if ($file->media_type == 'image') {
		$sReadyStr .= 'var URL = $("input[name=src]:checked").val();';
	}
	else {
		$sReadyStr .= 'var URL = $("input[name=url]").val();';
	}

	$sReadyStr .= '

			var win = tinyMCEPopup.getWindowArg("window");

			// insert information now
			win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

			// are we an image browser
			if (typeof(win.ImageDialog) != "undefined") {
				// we are, so update image dimensions...
				if (win.ImageDialog.getImageData) {
					win.ImageDialog.getImageData();
				}

				// ... and preview if necessary
				if (win.ImageDialog.showPreviewImage) {
					win.ImageDialog.showPreviewImage(URL);
				}
			}

			// close popup window
			tinyMCEPopup.close();
		}
	}

	tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);

	$("#media-insert-cancel").click(function(){
		tinyMCEPopup.close();
	});

	$("#media-insert-ok").click(FileBrowserDialogue.mySubmit);

	';
	$okt->page->js->addReady($sReadyStr);

	*/

	if ($okt->modules->moduleExists('rte_tinymce_4'))
	{
		$okt->page->js->addReady('
			var windowManager = top.tinymce.activeEditor.windowManager;

			$("#media-insert-ok").click(function(){
				var url = $("input[name=src]:checked").val();
				windowManager.getParams().oninsert(url);
				windowManager.close();
			});

			$("#media-insert-cancel").click(function(){
				windowManager.close();
			});
		');
	}
}

$okt->page->css->addFile($okt->media_manager->url().'/styles.css');

# Tabs
$okt->page->tabs();


# En-tête
if ($popup) {
	require OKT_ADMIN_HEADER_SIMPLE_FILE;
}
else {
	require OKT_ADMIN_HEADER_FILE;
}

if ($file === null)
{
	if ($popup) {
		require OKT_ADMIN_FOOTER_SIMPLE_FILE;
	}
	else {
		require OKT_ADMIN_FOOTER_FILE;
	}

	exit;
}

echo '<h3><a href="'.html::escapeURL($media_page_url).'">'.__('Media manager').'</a>'.
' / '.$okt->media->breadCrumb(html::escapeURL($media_page_url).'&amp;d=%s').
$file->basename.'</h3>';

echo
'<div id="tabered">'.
'<ul>'.
($popup ? '<li><a href="#media-insert"><span>'.__('Insert media item').'</span></a></li>' : '').
'<li><a href="#media-details-tab"><span>'.__('Media details').'</span></a></li>'.
'</ul>';

# Insertion popup
if ($popup)
{
	$media_desc = $file->media_title;

	echo
	'<div id="media-insert">'.
	'<form id="media-insert-form" action="" method="get">';

	if ($file->media_type == 'image')
	{
		$media_type = 'image';
		$media_desc = dcGetImageTitle($file,'Title ;; City ;; Country ;; Date(%b %Y) ;; separator(, )');

		if ($media_desc == $file->basename) {
			$media_desc = '';
		}

		echo
		'<h3>'.__('Image size:').'</h3> ';

		$s_checked = false;
		echo '<p>';
		foreach (array_reverse($file->media_thumb) as $s => $v)
		{
			$s_checked = ($s == 'm');
			echo '<label class="classic">'.
				form::radio(array('src'),html::escapeHTML($v),$s_checked).' '.
				__($okt->media->thumb_sizes[$s][2]).'</label><br /> ';
		}

		$s_checked = (!isset($file->media_thumb['m']));
		echo '<label class="classic">'.
		form::radio(array('src'),$file->file_url,$s_checked).' '.__('original').'</label><br /> ';
		echo '</p>';

/*
		echo '<h3>'.__('Image alignment').'</h3>';
		$i_align = array(
			'none' => array(__('c_c_None'),1),
			'left' => array(__('c_c_direction_Left'),0),
			'right' => array(__('c_c_direction_Right'),0),
			'center' => array(__('c_c_direction_Center'),0)
		);

		echo '<p>';
		foreach ($i_align as $k => $v) {
			echo '<label class="classic">'.
			form::radio(array('alignment'),$k,$v[1]).' '.$v[0].'</label><br /> ';
		}
		echo '</p>';

		echo
		'<h3>'.__('Image insertion').'</h3>'.
		'<p>'.
		'<label class="classic">'.form::radio(array('insertion'),'simple',true).
		__('As a single image').'</label><br />'.
		'<label class="classic">'.form::radio(array('insertion'),'link',false).
		__('As a link to original image').'</label>'.
		'</p>';
*/
	}
/*	elseif ($file->type == 'audio/mpeg3')
	{
		$media_type = 'mp3';

		echo '<h3>'.__('MP3 disposition').'</h3>'.
		'<p class="message">'.__("Please note that you cannot insert mp3 files with visual editor.").'</p>';

		$i_align = array(
			'none' => array(__('c_c_None'),0),
			'left' => array(__('c_c_direction_Left'),0),
			'right' => array(__('c_c_direction_Right'),0),
			'center' => array(__('c_c_direction_Center'),1)
		);

		echo '<p>';
		foreach ($i_align as $k => $v) {
			echo '<label class="classic">'.
			form::radio(array('alignment'),$k,$v[1]).' '.$v[0].'</label><br /> ';
		}

		$public_player_style = unserialize($core->blog->settings->themes->mp3player_style);
		$public_player = oktMedia::mp3player($file->file_url,$core->blog->getQmarkURL().'player_mp3.swf',$public_player_style);
		echo form::hidden('public_player',html::escapeHTML($public_player));
		echo '</p>';
	}
	elseif ($file->type == 'video/x-flv' || $file->type == 'video/mp4' || $file->type == 'video/x-m4v')
	{
		$media_type = 'flv';

		echo
		'<p class="message">'.__("Please note that you cannot insert video files with visual editor.").'</p>';

		echo
		'<h3>'.__('Video size').'</h3>'.
		'<p><label class="classic">'.__('Width:').' '.
		form::field('video_w',3,4,400).'  '.
		'<label class="classic">'.__('Height:').' '.
		form::field('video_h',3,4,300).
		'</p>';

		echo '<h3>'.__('Video disposition').'</h3>';

		$i_align = array(
			'none' => array(__('c_c_None'),0),
			'left' => array(__('c_c_direction_Left'),0),
			'right' => array(__('c_c_direction_Right'),0),
			'center' => array(__('c_c_direction_Center'),1)
		);

		echo '<p>';
		foreach ($i_align as $k => $v) {
			echo '<label class="classic">'.
			form::radio(array('alignment'),$k,$v[1]).' '.$v[0].'</label><br /> ';
		}

		$public_player_style = unserialize($core->blog->settings->themes->flvplayer_style);
		$public_player = dcMedia::flvplayer($file->file_url,$core->blog->getQmarkURL().'pf=player_flv.swf',$public_player_style);
		echo form::hidden('public_player',html::escapeHTML($public_player));
		echo '</p>';
	}
*/	else
	{
		$media_type = 'default';
		echo '<p>'.__('Media item will be inserted as a link.').'</p>';
	}

	echo
	'<p><a id="media-insert-cancel" class="button" href="#">'.__('c_c_action_Cancel').'</a> - '.
	'<strong><a id="media-insert-ok" class="button" href="#">'.__('c_c_action_Insert').'</a></strong>'.
	form::hidden(array('type'),html::escapeHTML($media_type)).
	form::hidden(array('title'),html::escapeHTML($file->media_title)).
	form::hidden(array('description'),html::escapeHTML($media_desc)).
	form::hidden(array('url'),$file->file_url).
	'</p>';

	echo '</form></div>';
}

echo '<div id="media-details-tab">'.
'<p id="media-icon"><img src="'.$file->media_icon.'" alt="" /></p>';

echo '<div id="media-details">';

if ($file->media_image)
{
	$thumb_size = !empty($_GET['size']) ? $_GET['size'] : 's';

	if (!isset($okt->media->thumb_sizes[$thumb_size]) && $thumb_size != 'o') {
		$thumb_size = 's';
	}

	echo '<p>'.__('Available sizes:').' ';
	foreach (array_reverse($file->media_thumb) as $s => $v)
	{
		$strong_link = ($s == $thumb_size) ? '<strong>%s</strong>' : '%s';
		printf($strong_link,'<a href="'.html::escapeURL($page_url).
		'&amp;id='.$id.'&amp;size='.$s.'">'.__($okt->media->thumb_sizes[$s][2]).'</a> | ');
	}
	echo '<a href="'.html::escapeURL($page_url).'&amp;id='.$id.'&amp;size=o">'.__('original').'</a>';
	echo '</p>';

	if (isset($file->media_thumb[$thumb_size])) {
		echo '<p><img src="'.$file->media_thumb[$thumb_size].'" alt="" /></p>';
	}
	elseif ($thumb_size == 'o')
	{
		$S = getimagesize($file->file);
		$class = ($S[1] > 500) ? ' class="overheight"' : '';
		unset($S);
		echo '<p id="media-original-image"'.$class.'><img src="'.$file->file_url.'" alt="" /></p>';
	}
}

if ($file->type == 'audio/mpeg3') {
	echo oktMedia::mp3player($file->file_url,'index.php?pf=player_mp3.swf');
}

if ($file->type == 'video/x-flv' || $file->type == 'video/mp4' || $file->type == 'video/x-m4v') {
	echo oktMedia::flvplayer($file->file_url,'index.php?pf=player_flv.swf');
}

echo
'<h3>'.__('Media details').'</h3>'.
'<ul>'.
	'<li><strong>'.__('File owner:').'</strong> '.$file->media_user.'</li>'.
	'<li><strong>'.__('File type:').'</strong> '.$file->type.'</li>'.
	'<li><strong>'.__('File size:').'</strong> '.util::l10nFileSize($file->size).'</li>'.
	'<li><strong>'.__('File URL:').'</strong> <a href="'.$file->file_url.'">'.$file->file_url.'</a></li>'.
'</ul>';


if ($file->type == 'image/jpeg')
{
	echo '<h3>'.__('Image details').'</h3>';

	if (count($file->media_meta) == 0) {
		echo '<p>'.__('No detail').'</p>';
	}
	else
	{
		echo '<ul>';
		foreach ($file->media_meta as $k => $v)
		{
			if ((string) $v) {
				echo '<li><strong>'.$k.':</strong> '.html::escapeHTML($v).'</li>';
			}
		}
		echo '</ul>';
	}
}

if ($file->editable && $core_media_writable)
{
	if ($file->media_type == 'image')
	{
		echo
		'<form class="clear" action="'.html::escapeURL($page_url).'" method="post">'.
		'<fieldset><legend>'.__('Update thumbnails').'</legend>'.
		'<p>'.__('This will create or update thumbnails for this image.').'</p>'.
		'<p><input type="submit" name="thumbs" value="'.__('update thumbnails').'" />'.
		form::hidden(array('id'),$id).
		adminPage::formtoken().'</p>'.
		'</fieldset></form>';
	}

	if ($file->type == 'application/zip')
	{
		$inflate_combo = array(
			__('Extract in a new directory') => 'new',
			__('Extract in current directory') => 'current'
		);

		echo
		'<form class="clear" id="file-unzip" action="'.html::escapeURL($page_url).'" method="post">'.
		'<fieldset><legend>'.__('Extract archive').'</legend>'.
		'<ul>'.
		'<li><strong>'.__('Extract in a new directory').'</strong> : '.
		__('This will extract archive in a new directory that should not exists yet.').'</li>'.
		'<li><strong>'.__('Extract in current directory').'</strong> : '.
		__('This will extract archive in current directory and will overwrite existing files or directory.').'</li>'.
		'</ul>'.
		'<p><label class="classic">'.__('Extract mode:').' '.
		form::select('inflate_mode',$inflate_combo,'new').'</label> '.
		'<input type="submit" name="unzip" value="'.__('c_c_action_extract').'" />'.
		form::hidden(array('id'),$id).
		adminPage::formtoken().'</p>'.
		'</fieldset></form>';
	}

	echo
	'<form class="clear" action="'.html::escapeURL($page_url).'" method="post">'.
	'<fieldset><legend>'.__('Change media properties').'</legend>'.

	'<p class="field"><label>'.__('File name:').'</label>'.
	form::text('media_file',30,255,html::escapeHTML($file->basename)).'</p>'.

	'<p class="field"><label>'.__('File title:').'</label>'.
	form::text('media_title',30,255,html::escapeHTML($file->media_title)).'</p>'.

	'<p class="field"><label>'.__('File date:').'</label>'.
	form::text('media_dt',16,16,html::escapeHTML($file->media_dtstr)).'</p>'.
	'<p class="field"><label class="classic">'.form::checkbox('media_private',1,$file->media_priv).' '.
	__('Private').'</label></p>'.

	'<p class="field"><label>'.__('New directory:').'</label>'.
	form::select('media_path',$dirs_combo,dirname($file->relname)).'</p>'.

	'<p><input type="submit" value="'.__('c_c_action_save').'" />'.
	form::hidden(array('id'),$id).
	adminPage::formtoken().'</p>'.
	'</fieldset></form>';

	echo
	'<form class="clear" action="'.html::escapeURL($page_url).'" method="post" enctype="multipart/form-data">'.
	'<fieldset><legend>'.__('Change file').'</legend>'.
	'<div>'.form::hidden(array('MAX_FILE_SIZE'),OKT_MAX_UPLOAD_SIZE).'</div>'.
	'<p class="field"><label for="upfile">'.__('Choose a file:').'</label>'.
	'<input type="file" id="upfile" name="upfile" size="35" /></p>'.
	'<p class="note">'.sprintf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)).'</p>'.
	'<p><input type="submit" value="'.__('c_c_action_send').'" />'.
	form::hidden(array('id'),$id).
	adminPage::formtoken().'</p>'.
	'</fieldset></form>';
}

echo '</div>';
echo '</div>';
echo '</div>';

# Pied-de-page
if ($popup) {
	require OKT_ADMIN_FOOTER_SIMPLE_FILE;
}
else {
	require OKT_ADMIN_FOOTER_FILE;
}
