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

	$(\'input[name="p_modules[]"]\').each(function(){
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
		var checkbox = $(this).find(\'input[name="p_modules[]"]\');
		checkbox.prop("checked", !checkbox.prop("checked"));
		$(this).toggleClass("ui-state-highlight");
	})
	.css("cursor", "pointer");

');
?>

<p><?php _e('i_modules_infos') ?></p>

<form action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<ul id="modules_list_choice" class="three-cols">
		<?php foreach ($aModulesList as $aModuleInfos) : ?>
		<li class="col"><div class="extension ui-state-default ui-corner-all">

			<?php if (file_exists($okt['modules_dir'].'/'.$aModuleInfos['id'].'/Install/Assets/module_icon.png')) : ?>
				<img src="<?php echo Utilities::base64EncodeImage($okt['modules_dir'].'/'.$aModuleInfos['id'].'/Install/Assets/module_icon.png', 'image/png'); ?>"
				width="32" height="32" alt="" class="left" />
			<?php else: ?>
				<img src="<?php echo $okt['public_url'] ?>/img/admin/module.png"
				width="32" height="32" alt="" class="left" />
			<?php endif; ?>

			<h3><label for="p_modules_<?php echo $aModuleInfos['id'] ?>"><?php echo form::checkbox(array('p_modules[]','p_modules_'.$aModuleInfos['id']), $aModuleInfos['id'], in_array($aModuleInfos['id'], $aDefaultModules)) ?>
			<?php _e($aModuleInfos['name_l10n']) ?></label></h3>

			<p><?php _e($aModuleInfos['desc_l10n']) ?></p>
		</div></li>
		<?php endforeach; ?>
	</ul>

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
	</p>
</form>
