<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# button set
$okt->page->setButtonset ( 'themesBtSt', array (
	'id' => 'themes-buttonset',
	'type' => '', # buttonset-single | buttonset-multi | ''
	'buttons' => array (
		array (
			'permission' => true,
			'title' => __ ( 'c_c_action_Go_back' ),
			'url' => $view->generateUrl ( 'config_themes' ),
			'ui-icon' => 'arrowreturnthick-1-w'
		)
	)
) );

# Onglets
$okt->page->tabs();

# Color picker et autres joyeusetés
if ($bHasDefinitionsLess) {
	$oDefinitionsLessEditor->setFormAssets($okt->page, $sThemeId);
}

# infos page
$okt->page->addGlobalTitle(__('c_a_themes_management'), $view->generateUrl('config_themes'));
$okt->page->addGlobalTitle($aThemeInfos['name']);

# CSS
$okt->page->css->addCss ( '
#theme-screenshot {
	float: left;
	margin: 0 1em 1em 0;
	width: 400px;
}
#no-screenshot {
	width: 400px;
	height: 300px;
	background: #f1f1f1;
	border: 1px solid #f1f1f1;
	text-align: center;
}
#no-screenshot em {
	position: relative;
	top: 45%;
}
');


$okt->page->js->addFile($okt->options->public_url.'/components/ghostdown/ghostdown.js');
$okt->page->css->addFile($okt->options->public_url.'/components/ghostdown/ghostdown.css');
$okt->page->js->addFile($okt->options->public_url.'/components/ghostdown/jquery.ghostdown.js');
$okt->page->js->addReady('
	$(".editor").ghostDown();
');

?>

<?php echo $okt->page->getButtonSet('themesBtSt'); ?>

<div id="tabered">
	<ul>
		<li><a href="#tab_infos"><span><?php _e("Infos") ?></span></a></li>
		<?php if ($bHasDevNotes) : ?>
		<li><a href="#tab_dev_notes"><span><?php _e('c_a_themes_notes') ?></span></a></li>
		<?php endif; ?>
		<?php if ($bHasDefinitionsLess) : ?>
		<li><a href="#tab_def_less"><span>definitions.less</span></a></li>
		<?php endif; ?>
	</ul>

	<div id="tab_infos" class="ui-helper-clearfix">
		<h3><?php _e($aThemeInfos['name']) ?></h3>

		<div id="theme-screenshot">
				<?php if ($aThemeInfos['screenshot']) : ?>
				<img src="<?php echo $okt->options->get('public_url').'/themes/'.$aThemeInfos['id'].'/screenshot.png' ?>" width="100%" height="100%" alt="" />
				<?php else : ?>
				<div id="no-screenshot"><em class="note"><?php _e('c_a_themes_no_screenshot') ?></em></div>
				<?php endif; ?>
		</div>

		<div class="theme-infos">

			<p><?php _e($aThemeInfos['desc']) ?></p>

			<p><?php printf(__('c_a_themes_version_%s'), $aThemeInfos['version']) ?></p>

			<p><?php printf(__('c_a_themes_author_%s'), $aThemeInfos['author']) ?></p>

			<p><?php #echo $view->escape($aThemeInfos['tags']) ?></p>
		</div>
	</div><!-- #tab_infos -->

	<?php if ($bHasDevNotes) : ?>
	<div id="tab_dev_notes">
		<?php if ($bEditDevNotes) : ?>
		<form action="<?php $view->generateUrl('config_theme', array('theme_id' => $sThemeId)) ?>" method="post">
			<div class="features">
				<section class="editor">
					<div class="outer">
						<div class="editorwrap">
							<section class="entry-markdown">
								<header class="floatingheader"> &nbsp;&nbsp; Markdown </header>
								<section class="entry-markdown-content">
									<?php echo form::textarea('notes_content', 80, 20, $sDevNotesMd)?>
								</section>
							</section>
							<section class="entry-preview active">
								<header class="floatingheader">
									&nbsp;&nbsp; Preview <span class="entry-word-count">0 words</span>
								</header>
								<section class="entry-preview-content">
									<div class="rendered-markdown"></div>
								</section>
							</section>
						</div>
					</div>
				</section>
			</div>

			<p><?php echo form::hidden('save_notes', 1) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>
		<?php else : ?>
			<?php echo $sDevNotesHtml ?>
			<p><a href="<?php $view->generateUrl('config_theme', array('theme_id' => $sThemeId)) ?>?edit_notes=1"class="button"><?php _e('c_c_action_edit') ?></a></p>
		<?php endif; ?>
	</div><!-- #tab_dev_notes -->
	<?php endif; ?>

	<?php if ($bHasDefinitionsLess) : ?>
	<div id="tab_def_less">
		<h3>definitions.less</h3>

		<form action="<?php $view->generateUrl('config_theme', array('theme_id' => $sThemeId)) ?>" method="post">
			<?php # affichage champs definitions.less
			echo $oDefinitionsLessEditor->getHtmlFields ( $aCurrentDefinitionsLess, 4 ); ?>

			<p><?php echo form::hidden('save_def_less', 1)?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>
	</div><!-- #tab_def_less -->
	<?php endif; ?>

</div><!-- #tabered -->
