<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Misc\Utilities;

$view->extend('Layout');

$okt->page->js->addReady('

	function toggleSelected(element) {
		var e = $(element);

		if (e.is(":checked")) {
			e.closest(".extension").addClass("ui-state-highlight");
		}
		else {
			e.closest(".extension").removeClass("ui-state-highlight");
		}
	}

	$(\'input[name="p_themes[]"]\').each(function(){
		toggleSelected($(this));
	}).change(function(){
		toggleSelected($(this));
	});

	$(".extension").hover(function() {
		$(this).addClass("ui-state-hover");
	},function() {
		$(this).removeClass("ui-state-hover");
	})
	.click(function(){
		var checkbox = $(this).find(\'input[name="p_themes[]"]\');
		checkbox.prop("checked", !checkbox.prop("checked"));
		$(this).toggleClass("ui-state-highlight");
	})
	.css("cursor", "pointer");

');
?>

<p><?php _e('i_themes_infos') ?></p>

<form action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<ul id="themes_list_choice" class="three-cols">
		<?php foreach ($aThemesList as $aThemeInfos) : ?>
		<li class="col"><div class="extension ui-state-default ui-corner-all">

			<?php if (file_exists($okt->options->get('themes_dir').'/'.$aThemeInfos['id'].'/Install/Assets/theme_icon.png')) : ?>
				<img src="<?php echo Utilities::base64EncodeImage($okt->options->get('themes_dir').'/'.$aThemeInfos['id'].'/Install/Assets/theme_icon.png', 'image/png'); ?>"
				width="32" height="32" alt="" class="left" />
			<?php else: ?>
				<img src="<?php echo $okt->options->public_url ?>/img/admin/theme.png"
				width="32" height="32" alt="" class="left" />
			<?php endif; ?>

			<h3><label for="p_themes_<?php echo $aThemeInfos['id'] ?>"><?php echo form::checkbox(array('p_themes[]','p_themes_'.$aThemeInfos['id']), $aThemeInfos['id'], in_array($aThemeInfos['id'], $aDefaultThemes)) ?>
			<?php _e($aThemeInfos['name_l10n']) ?></label></h3>

			<p><?php _e($aThemeInfos['desc_l10n']) ?></p>
		</div></li>
		<?php endforeach; ?>
	</ul>

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
	</p>
</form>
