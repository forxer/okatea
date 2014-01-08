<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page principale de l'éditeur de themes.
 *
 * @addtogroup Okatea
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Themes\Editor\Editor as ThemesEditor;

# Accès direct interdit
if (!defined('ON_THEME_EDITOR')) die;


/* Initialisations
----------------------------------------------------------*/

# locales
$okt->l10n->loadFile($okt->options->locales_dir.'/'.$okt->user->language.'/admin.theme.editor');

$sThemeId = !empty($_REQUEST['theme']) ? $_REQUEST['theme'] : null;
$sFilename = !empty($_REQUEST['file']) ? rawurldecode($_REQUEST['file']) : null;
$sMode = null;


$oThemeEditor = new ThemesEditor($okt, $okt->options->get('themes_dir'));


if ($sThemeId)
{
	try
	{
		$oThemeEditor->loadTheme($sThemeId);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
		$sThemeId = null;
	}
}
else {
	$okt->error->set(__('c_a_te_error_choose_theme'));
}

if ($sThemeId && $sFilename)
{
	try
	{
		$oThemeEditor->loadFile($sFilename);

		$sMode = $oThemeEditor->getCodeMirrorMode();
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
		$sFilename = null;
	}
}


/* Traitements
----------------------------------------------------------*/

# Modification d'un fichier
if (!empty($_POST['save']) && !empty($_POST['editor']) && $sThemeId && $sFilename)
{
	try
	{
		$oThemeEditor->saveFile($_POST['editor'], !empty($_POST['make_backup']));

		$okt->logAdmin->warning(array(
			'code' => 41,
			'component' => 'themes editor',
			'message' => 'saved file '.$sFilename.' in '.$sThemeId
		));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}

	$okt->page->flash->success(__('c_a_te_confirm_saved'));

	http::redirect('configuration.php?action=theme_editor&theme='.$sThemeId.'&file='.$sFilename);
}

# Restauration d'un fichier de backup
if (!empty($_GET['restore_backup']) && $sThemeId && $sFilename)
{
	try
	{
		$oThemeEditor->restoreBackupFile(rawurldecode($_GET['restore_backup']));

		$okt->logAdmin->warning(array(
			'code' => 41,
			'component' => 'themes editor',
			'message' => 'restore file '.$_GET['restore_backup'].' in '.$sThemeId
		));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}

	$okt->page->flash->success(__('c_a_te_confirm_restored'));

	http::redirect('configuration.php?action=theme_editor&theme='.$sThemeId.'&file='.$sFilename);
}

# Suppression d'un fichier de backup
if (!empty($_GET['delete_backup']) && $sThemeId && $sFilename)
{
	try
	{
		$oThemeEditor->deleteBackupFile(rawurldecode($_GET['delete_backup']));

		$okt->logAdmin->warning(array(
			'code' => 41,
			'component' => 'themes editor',
			'message' => 'delete file '.$_GET['delete_backup'].' in '.$sThemeId
		));
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}

	$okt->page->flash->success(__('c_a_te_confirm_deleted'));

	http::redirect('configuration.php?action=theme_editor&theme='.$sThemeId.'&file='.$sFilename);
}


/* Affichage
----------------------------------------------------------*/

# Infos page
$okt->page->addGlobalTitle(__('c_a_theme_editor'), 'configuration.php?action=theme_editor');

if ($sThemeId) {
	$okt->page->addGlobalTitle($oThemeEditor->getThemeInfo('name'), 'configuration.php?action=theme_editor&theme='.$sThemeId);
}

$okt->page->css->addCss('
#editor_wrapper {
	width: 80%;
	float: left;
}
	.CodeMirror {
		border: 1px solid #eee;
		width: 100%;
		height: 600px;
		font-size: 1.2em;
	}
	.CodeMirror-focused .cm-matchhighlight {
		background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAFklEQVQI12NgYGBgkKzc8x9CMDAwAAAmhwSbidEoSQAAAABJRU5ErkJggg==);
		background-position: bottom;
		background-repeat: repeat-x;
	}
	.CodeMirror-activeline-background {
		background: #e8f2ff !important;
	}
	.CodeMirror-fullscreen {
		display: block;
		position: absolute;
		top: 0; left: 0;
		width: 100%;
		z-index: 9999;
	}
#treecontrol a {
	text-decoration: none;
	padding: 0 1em
}
#files_list {
	width: 17%;
	float: right;
	padding-left: 1em;
	word-wrap: break-word;
}
#editor_actions {
	list-style: none;
}
	#editor_actions li {
		display: inline-block;
		margin-right: 1em;
	}
');

if ($sFilename && $sMode)
{
	$okt->page->addGlobalTitle($sFilename);

	# CSS
	$okt->page->css->addFile($okt->options->public_url.'/components/codemirror/lib/codemirror.css');
	$okt->page->css->addFile($okt->options->public_url.'/components/codemirror/addon/dialog/dialog.css');

	# JS
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/lib/codemirror.js');

	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/clike/clike.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/css/css.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/htmlmixed/htmlmixed.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/javascript/javascript.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/less/less.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/php/php.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/xml/xml.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/mode/yaml/yaml.js');

	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/addon/search/search.js');
	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/addon/search/searchcursor.js');

	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/addon/search/match-highlighter.js');

	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/addon/dialog/dialog.js');

	$okt->page->js->addFile($okt->options->public_url.'/components/codemirror/addon/selection/active-line.js');

	$okt->page->js->addScript('

		function isFullScreen(cm) {
			return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
		}
		function winHeight() {
			return window.innerHeight || (document.documentElement || document.body).clientHeight;
		}
		function setFullScreen(cm, full) {
			var wrap = cm.getWrapperElement();
			if (full) {
				wrap.className += " CodeMirror-fullscreen";
				wrap.style.height = winHeight() + "px";
				document.documentElement.style.overflow = "hidden";
			} else {
				wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
				wrap.style.height = "";
				document.documentElement.style.overflow = "";
			}
			cm.refresh();
		}
		CodeMirror.on(window, "resize", function() {
			var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
			if (!showing) return;
			showing.CodeMirror.getWrapperElement().style.height = winHeight() + "px";
		});

		var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
			mode:  "'.$sMode.'",
			indentUnit: 4,
			indentWithTabs: true,
			styleActiveLine: true,
			lineNumbers: true,
			lineWrapping: true,
			highlightSelectionMatches: true,
			extraKeys: {
				"F11": function(cm) {
					setFullScreen(cm, !isFullScreen(cm));
				},
				"Esc": function(cm) {
					if (isFullScreen(cm)) setFullScreen(cm, false);
				}
			}
		});

	');
}

$okt->page->js->addReady('

	$("#new_buttons").buttonset();


	$("#new_file_button").button("option", "icons", {
		primary: "ui-icon-document"
	});
	$("#new_tpl_button").button("option", "icons", {
		primary: "ui-icon-script"
	});

');


# tree view
$okt->page->treeview(array(
	'persist' => 'cookie',
	'control' => "#treecontrol",
	'animated' => "fast",
	'collapsed' => false
),'#browser');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php if ($sThemeId) : ?>

<div class="two-cols">
	<p id="new_buttons" class="col">
		<a href="configuration.php?action=theme_editor&amp;theme=<?php echo $sThemeId ?>&amp;new_file=1" id="new_file_button"><?php
		_e('c_a_te_new_file')  ?></a>
		<a href="configuration.php?action=theme_editor&amp;theme=<?php echo $sThemeId ?>&amp;new_template=1" id="new_tpl_button"><?php
		_e('c_a_te_new_tpl')  ?></a>
	</p>

	<div id="treecontrol" class="col right">
		<a title="Collapse the entire tree below" href="#"><img src="<?php echo $okt->options->public_url ?>/plugins/treeview/images/minus.gif" /> <?php _e('c_a_te_action_collapse_all') ?></a>
		<a title="Expand the entire tree below" href="#"><img src="<?php echo $okt->options->public_url ?>/plugins/treeview/images/plus.gif" /> <?php _e('c_a_te_action_expand_all') ?></a>
	</div>
</div>
<div class="ui-helper-clearfix">
	<div id="editor_wrapper">

	<?php if ($sFilename) : ?>
		<form class="col" action="configuration.php" method="post">

			<textarea id="editor" name="editor" rows="35" cols="97"><?php echo file_get_contents($oThemeEditor->getThemePath().$sFilename) ?></textarea>

			<p><?php echo form::hidden(array('action'), 'theme_editor') ?>
			<?php echo form::hidden('theme', $sThemeId) ?>
			<?php echo form::hidden('file', rawurlencode($sFilename)) ?>
			<?php echo Page::formtoken() ?>
			<input type="submit" name="save" value="<?php _e('c_c_action_Save') ?>" />
			<?php echo form::checkbox('make_backup', 1, true) ?><label for="make_backup"><?php _e('c_a_te_make_backup') ?></label></p>
		</form>

		<?php $aBackupFiles = $oThemeEditor->getBackupFiles();
		if (!empty($aBackupFiles)) : ?>
		<p><?php _e('c_a_te_backup_files') ?></p>
		<ul>
		<?php foreach ($aBackupFiles as $sBackupFile) : ?>
			<li><?php echo $sBackupFile ?>
				- <a href="configuration.php?action=theme_editor&amp;theme=<?php echo $sThemeId ?>&amp;file=<?php echo rawurlencode($sFilename) ?>&amp;restore_backup=<?php echo rawurlencode($sBackupFile) ?>" onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_te_action_restore_confirm')) ?>')"><?php _e('c_a_te_action_restore') ?></a>
				- <a href="configuration.php?action=theme_editor&amp;theme=<?php echo $sThemeId ?>&amp;file=<?php echo rawurlencode($sFilename) ?>&amp;delete_backup=<?php echo rawurlencode($sBackupFile) ?>" onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_te_action_delete_confirm')) ?>')"><?php _e('c_a_te_action_delete') ?></a>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<ul id="editor-hints" class="note">
			<li><?php _e('c_a_te_editor_hint_f11') ?></li>
			<li><?php _e('c_a_te_editor_hint_ctrl_f') ?></li>
			<li><?php _e('c_a_te_editor_hint_shift_ctrl_f') ?></li>
		</ul>
	<?php else : ?>
		<p><?php _e('c_a_te_please_choose_file') ?></p>
	<?php endif; ?>

	</div><!-- #editor_wrapper -->

	<div id="files_list">
		<ul id="browser" class="filetree">
		<?php $oThemeEditor->loadThemeFilesTree();
		foreach ($oThemeEditor->getThemeFiles() as $key=>$item); ?>
		</ul>
	</div><!-- #files_list -->
</div>

<?php else : ?>

<ul>
	<?php foreach ($oThemeEditor->getThemes() as $aTheme) : ?>
	<li><a href="configuration.php?action=theme_editor&amp;theme=<?php echo $aTheme['id'] ?>"><?php echo $aTheme['name'] ?></a></li>
	<?php endforeach; ?>
</ul>

<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
