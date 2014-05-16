<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$okt->page->js->addFile($okt->options->public_url . '/components/ghostdown/ghostdown.js');
$okt->page->css->addFile($okt->options->public_url . '/components/ghostdown/ghostdown.css');
$okt->page->js->addFile($okt->options->public_url . '/components/ghostdown/jquery.ghostdown.js');
$okt->page->js->addReady('
	$(".editor").ghostDown();
');

?>

<h3><?php _e('c_a_infos_notes_title') ?></h3>

<?php if (!$aNotes['has']) : ?>

<p>
	<em><?php _e('c_a_infos_no_notes') ?></em>
</p>

<p>
	<a
		href="<?php echo $view->generateUrl('config_infos') ?>?create_notes=1"><?php _e('c_a_infos_create_notes_file') ?></a>
</p>

<?php else : ?>

	<?php if ($aNotes['edit']) : ?>

<form action="<?php echo $view->generateUrl('config_infos') ?>"
	method="post">
	<div class="features">
		<section class="editor">
			<div class="outer">
				<div class="editorwrap">
					<section class="entry-markdown">
						<header class="floatingheader"> &nbsp;&nbsp; Markdown </header>
						<section class="entry-markdown-content">
									<?php echo form::textarea('notes_content', 80, 20, $aNotes['md'])?>
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

	<p><?php echo form::hidden('save_notes', 1)?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php else : ?>

		<?php echo $aNotes['html']?>
<p>
	<a href="<?php echo $view->generateUrl('config_infos') ?>?edit_notes=1"
		class="button"><?php _e('c_c_action_edit') ?></a>
</p>

<?php endif; ?>

<?php endif; ?>
