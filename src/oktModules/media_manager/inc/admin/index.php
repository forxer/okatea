<?php
/**
 * @ingroup okt_module_media_manager
 * @brief Media manager.
 *
 * Fork of dcMedia Dotclear media manage
 *
 */

# Accès direct interdit
if (!defined('ON_MEDIA_MANAGER_MODULE')) die;


/* HTML page
-------------------------------------------------------- */

$d = isset($_REQUEST['d']) ? $_REQUEST['d'] : null;
$dir = null;

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

# We are on home not comming from media manager
if ($d === null && isset($_SESSION['media_manager_dir']))
{
	# We get session information
	$d = $_SESSION['media_manager_dir'];
}

if (!isset($_GET['page']) && isset($_SESSION['media_manager_page'])) {
	$page = $_SESSION['media_manager_page'];
}

# We set session information about directory and page
if ($d) {
	$_SESSION['media_manager_dir'] = $d;
}
else {
	unset($_SESSION['media_manager_dir']);
}

if ($page != 1) {
	$_SESSION['media_manager_page'] = $page;
}
else {
	unset($_SESSION['media_manager_page']);
}

# Sort combo
$sort_combo = array(
	__('By names, ascendant') => 'name-asc',
	__('By names, descendant') => 'name-desc',
	__('By dates, ascendant') => 'date-asc',
	__('By dates, descendant') => 'date-desc'
);

if (!empty($_GET['file_sort']) && in_array($_GET['file_sort'],$sort_combo)) {
	$_SESSION['media_file_sort'] = $_GET['file_sort'];
}

$file_sort = !empty($_SESSION['media_file_sort']) ? $_SESSION['media_file_sort'] : null;

$popup = (integer) !empty($_GET['popup']);

$page_url = 'module.php?m=media_manager&popup='.$popup;

$core_media_writable = false;
try
{
	$okt->media = new oktMedia($okt);

	if ($file_sort) {
		$okt->media->setFileSort($file_sort);
	}

	$okt->media->chdir($d);
	$okt->media->getDir();
	$core_media_writable = $okt->media->writable();
	$dir =& $okt->media->dir;

	if  (!$core_media_writable) {
		throw new Exception('you do not have sufficient permissions to write to this folder: ');
	}
}
catch (Exception $e) {
	$okt->error->set($e->getMessage());
}

# Zip download
if (!empty($_GET['zipdl']) && $okt->checkPerm('media_admin'))
{
	try
	{
		set_time_limit(0);
		$fp = fopen('php://output','wb');
		$zip = new fileZip($fp);
		$zip->addExclusion('#(^|/).(.*?)_(m|s|sq|t).jpg$#');
		$zip->addDirectory($okt->media->root.'/'.$d,'',true);

		header('Content-Disposition: attachment;filename='.($d ? $d : 'media').'.zip');
		header('Content-Type: application/x-zip');
		$zip->write();
		unset($zip);
		exit;
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# New directory
if ($dir && !empty($_POST['newdir']))
{
	try
	{
		$okt->media->makeDir($_POST['newdir']);

		$okt->page->flashMessages->addSuccess(__('Directory has been successfully created.'));

		http::redirect($page_url.'&d='.rawurlencode($d));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Adding a file
if ($dir && !empty($_FILES['upfile']))
{
	try
	{
		util::uploadStatus($_FILES['upfile']);

		$f_title = (isset($_POST['upfiletitle']) ? $_POST['upfiletitle'] : '');
		$f_private = (isset($_POST['upfilepriv']) ? $_POST['upfilepriv'] : false);

		$okt->media->uploadFile($_FILES['upfile']['tmp_name'], $_FILES['upfile']['name'], $f_title, $f_private);

		$okt->page->flashMessages->addSuccess(__('Files have been successfully uploaded.'));

		http::redirect($page_url.'&d='.rawurlencode($d));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


# Removing item
if ($dir && !empty($_POST['rmyes']) && !empty($_POST['remove']))
{
	$_POST['remove'] = rawurldecode($_POST['remove']);

	try
	{
		$okt->media->removeItem($_POST['remove']);

		$okt->page->flashMessages->addSuccess(__('File has been successfully removed.'));

		http::redirect($page_url.'&d='.rawurlencode($d));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Rebuild directory
if ($dir && $okt->user->is_superadmin && !empty($_POST['rebuild']))
{
	try
	{
		$okt->media->rebuild($d);

		$okt->page->flashMessages->addSuccess(__('Directory has been successfully rebuilt.'));

		http::redirect($page_url.'&d='.rawurlencode($d));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


# DISPLAY confirm page for rmdir & rmfile
if ($dir && !empty($_GET['remove']))
{
	# En-tête
	if ($popup) {
		require OKT_ADMIN_HEADER_SIMPLE_FILE;
	}
	else {
		require OKT_ADMIN_HEADER_FILE;
	}

	echo '<h2>'.__('Media manager').' &rsaquo; '.__('confirm removal').'</h2>';

	echo
	'<form action="'.html::escapeURL($page_url).'" method="post">'.
	'<p>'.sprintf(__('Are you sure you want to remove %s?'),
	html::escapeHTML($_GET['remove'])).'</p>'.
	'<p><input type="submit" value="'.__('c_c_action_cancel').'" /> '.
	' &nbsp; <input type="submit" name="rmyes" value="'.__('c_c_yes').'" />'.
	form::hidden('d',$d).
	adminPage::formtoken().
	form::hidden('remove',html::escapeHTML($_GET['remove'])).'</p>'.
	'</form>';

	# Pied-de-page
	if ($popup) {
		require OKT_ADMIN_FOOTER_SIMPLE_FILE;
	}
	else {
		require OKT_ADMIN_FOOTER_FILE;
	}

	exit;
}


/* Affichage
-------------------------------------------------------- */

if ($popup)
{
	$okt->page->js->addFile(OKT_MODULES_URL.'/rte_tinymce/tinyMCE_jquery/tiny_mce_popup.js');

	$okt->page->js->addReady('
	var FileBrowserDialogue = {
		init : function () {
			// Here goes your code for setting your custom things onLoad.
		},
		mySubmit : function () {
			var URL = document.my_form.my_field.value;
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
	');
}

$okt->page->css->addFile($okt->media_manager->url().'/styles.css');

# En-tête
if ($popup) {
	require OKT_ADMIN_HEADER_SIMPLE_FILE;
}
else {
	require OKT_ADMIN_HEADER_FILE;
}

echo '<h3><a href="'.html::escapeURL($page_url.'&d=').'">'.__('Media manager').'</a>'.
' / '.$okt->media->breadCrumb(html::escapeURL($page_url).'&amp;d=%s').'</h3>';

if (!$dir) {
	require OKT_ADMIN_FOOTER_FILE;
	exit;
}

if ($popup) {
	echo '<p><strong>'.sprintf(__('Choose a file to insert by clicking on %s.'),
	'<img src="'.OKT_PUBLIC_URL.'/img/ico/plus.png" alt="'.__('Attach this file').'" />').'</strong></p>';
}

$items = array_values(array_merge($dir['dirs'],$dir['files']));
$num_items = count($items);
if ($num_items == 0)
{
	echo '<p><strong>'.__('No file.').'</strong></p>';
}
else
{
	$pager = new adminPager($page,$num_items,$nb_per_page,10);

	echo
	'<form action="module.php" method="get">'.
	'<p><label>'.__('Sort files:').' '.
	form::select('file_sort',$sort_combo,$file_sort).'</label>'.
	form::hidden(array('m'),'media_manager').
	form::hidden(array('popup'),$popup).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	'</form>';

	echo '<div class="media-list">';

	if ($pager->getNbPages() > 1) {
		echo '<ul class="pagination">'.$pager->getLinks().'</ul>';
	}

	for ($i=$pager->index_start, $j=0; $i<=$pager->index_end; $i++, $j++) {
		echo mediaItemLine($items[$i],$j);
	}

	echo '<div class="clearer"></div>';

	if ($pager->getNbPages() > 1) {
		echo '<ul class="pagination">'.$pager->getLinks().'</ul>';
	}

	echo '</div>';
}

if ($core_media_writable)
{
	echo '<div class="two-cols">';

	echo
	'<div class="col"><h3 id="add-file">'.__('Add files').'</h3>'.
	'<form id="media-upload" action="'.html::escapeURL($page_url).'" method="post" enctype="multipart/form-data">'.

	'<div>'.form::hidden(array('MAX_FILE_SIZE'),OKT_MAX_UPLOAD_SIZE).
	adminPage::formtoken().'</div>'.

	'<fieldset id="add-file-f">'.

	'<p class="field"><label for="upfile">'.__('Choose a file:').'</label>'.
	'<input type="file" name="upfile" id="upfile" size="20" /></p>'.

	'<p class="note">'.sprintf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)).'</p>'.

	'<p class="field"><label for="upfiletitle">'.__('Title:').'</label>'.
	form::text('upfiletitle',35,255).'</p>'.

	'<p class="field"><label>'.form::checkbox(array('upfilepriv'),1).' '.
	__('Private').'</label></p>'.

	'<p><input type="submit" value="'.__('c_c_action_send').'" />'.
	form::hidden(array('d'),$d).'</p>'.

	'</fieldset>'.
	'</form>'.

	'<p class="note">'.__('Please take care to publish media that you own and that are not protected by copyright.').'</p>'.
	'</div>';

	echo
	'<div class="col"><h3 id="new-dir">'.__('New directory').'</h3>'.
	'<form class="clear" action="'.html::escapeURL($page_url).'" method="post">'.
	'<fieldset id="new-dir-f">'.
	adminPage::formtoken().

	'<p class="field"><label for="newdir">'.__('Directory Name:').'</label>'.
	form::text('newdir',35,255).'</p>'.

	'<p><input type="submit" value="'.__('c_c_action_save').'" />'.
	form::hidden(array('d'),html::escapeHTML($d)).'</p>'.

	'</fieldset>'.
	'</form></div>';

	echo '</div>';
}


# Get zip directory
if ($okt->checkPerm('media_admin'))
{
	echo '<p><a href="'.html::escapeURL($page_url).'&amp;zipdl=1" class="link_sprite ss_package">'.
	__('Download this directory as a zip file').'</a></p>';
}

# Pied-de-page
if ($popup) {
	require OKT_ADMIN_FOOTER_SIMPLE_FILE;
}
else {
	require OKT_ADMIN_FOOTER_FILE;
}


/* ----------------------------------------------------- */
function mediaItemLine($f,$i)
{
	global $okt, $page_url, $popup;

	$fname = $f->basename;

	if ($f->d) {
		$link = html::escapeURL($page_url).'&amp;d='.html::sanitizeURL($f->relname);
		if ($f->parent) {
			$fname = '..';
		}
	} else {
		$link = 'module.php?m=media_manager&action=item&id='.$f->media_id.'&amp;popup='.$popup;
	}

	$class = 'media-item media-col-'.($i%2);

	$res =
	'<div class="'.$class.'"><a class="media-icon media-link" href="'.$link.'">'.
	'<img src="'.$f->media_icon.'" alt="" /></a>'.
	'<ul>'.
	'<li><a class="media-link" href="'.$link.'">'.$fname.'</a></li>';

	if (!$f->d) {
		$res .=
		'<li>'.$f->media_title.'</li>'.
		'<li>'.$f->media_dtstr.' - '.
		util::l10nFileSize($f->size).' - '.
		'<a href="'.$f->file_url.'">'.__('c_c_action_open').'</a>'.
		'</li>';
	}

	$res .= '<li class="media-action">&nbsp;';

	if ($popup && !$f->d) {
		$res .= '<a href="'.$link.'"><img src="'.OKT_PUBLIC_URL.'/img/ico/plus.png" alt="'.__('Insert this file').'" '.
		'title="'.__('Insert this file').'" /></a> ';
	}

	if ($f->del) {
		$res .= '<a href="'.html::escapeURL($page_url).'&amp;d='.
		rawurlencode($GLOBALS['d']).'&amp;remove='.rawurlencode($f->basename).'" class="link_sprite ss_delete">'.
		__('c_c_action_delete').'</a>';
	}

	$res .= '</li>';

	if ($f->type == 'audio/mpeg3') {
		$res .= '<li>'.oktMedia::mp3player($f->file_url,'index.php?pf=player_mp3.swf').'</li>';
	}

	$res .= '</ul></div>';

	return $res;
}
