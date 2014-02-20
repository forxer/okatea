<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$aDefaultGravatarImages = array(
	null		=> __('c_a_users_config_gravatar_default_image_gravatar'),
	'mm' 		=> __('c_a_users_config_gravatar_default_image_mm'),
	'identicon' => __('c_a_users_config_gravatar_default_image_identicon'),
	'monsterid' => __('c_a_users_config_gravatar_default_image_monsterid'),
	'wavatar' 	=> __('c_a_users_config_gravatar_default_image_wavatar'),
	'retro' 	=> __('c_a_users_config_gravatar_default_image_retro'),
	'blank' 	=> __('c_a_users_config_gravatar_default_image_blank'),
	'404' 		=> __('c_a_users_config_gravatar_default_image_404')
);

$aGravatarRatings = array(
	'g' 		=> __('c_a_users_config_gravatar_rating_g'),
	'pg' 		=> __('c_a_users_config_gravatar_rating_pg'),
	'r' 		=> __('c_a_users_config_gravatar_rating_r'),
	'x' 		=> __('c_a_users_config_gravatar_rating_x')
);

$okt->page->css->addCss('
#gravatar_default_image .col1 {
	width: 65%;
}
#gravatar_default_image .col2 {
	width: 33%;
	text-align: center;
}
.gravatar_default_image_preview {
	display: none;
}
');

$okt->page->js->addScript('
	function handleGravatarDefaultImagePreview() {
		var selected = $("input[name=\'p_users_gravatar_default_image\']:checked").val();

		$(".gravatar_default_image_preview").each(function() {
			if ($(this).attr("id") == "gravatar_default_image_" + selected) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}
');

$okt->page->js->addReady('
	handleGravatarDefaultImagePreview();
	$("input[name=\'p_users_gravatar_default_image\']").change(function(){handleGravatarDefaultImagePreview();});
');


?>

<h3><?php _e('c_a_users_config_tab_image_title') ?></h3>

	<p class="field"><label><?php echo form::checkbox('p_users_gravatar_enabled', 1, $aPageData['config']['users']['gravatar']['enabled']) ?>
	<?php printf(__('c_a_users_config_enable_gravatar_%s'), '<a href="https://'.$okt->user->language.'.gravatar.com/">Gravatar</a>') ?></label></p>

	<fieldset>
		<legend><?php _e('c_a_users_config_gravatar_default_image') ?></legend>

		<p><?php _e('c_a_users_config_gravatar_default_image_note') ?></p>

		<div id="gravatar_default_image" class="two-cols">
			<div class="col col1">
				<?php foreach ($aDefaultGravatarImages as $k=>$v) : ?>
				<p class="field"><label for="p_users_gravatar_default_image_<?php echo $k ?>"><?php echo form::radio(array('p_users_gravatar_default_image','p_users_gravatar_default_image_'.$k), $k, ($aPageData['config']['users']['gravatar']['default_image'] == $k)) ?>
				<?php _e($v) ?></label></p>
				<?php endforeach; ?>
			</div>
			<div class="col col2">
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?s=140" id="gravatar_default_image_" class="gravatar_default_image_preview" alt="" />
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&amp;s=140" id="gravatar_default_image_mm" class="gravatar_default_image_preview" alt="" />
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=identicon&amp;s=140" id="gravatar_default_image_identicon" class="gravatar_default_image_preview" alt="" />
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=monsterid&amp;s=140" id="gravatar_default_image_monsterid" class="gravatar_default_image_preview" alt="" />
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=wavatar&amp;s=140" id="gravatar_default_image_wavatar" class="gravatar_default_image_preview" alt="" />
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=retro&amp;s=140" id="gravatar_default_image_retro" class="gravatar_default_image_preview" alt="" />
				<img src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=blank&amp;s=140" id="gravatar_default_image_blank" class="gravatar_default_image_preview" alt="" />
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend><?php _e('c_a_users_config_gravatar_rating') ?></legend>

		<p><?php _e('c_a_users_config_gravatar_rating_note') ?></p>

		<?php foreach ($aGravatarRatings as $k=>$v) : ?>
		<p class="field"><label for="p_users_gravatar_rating_<?php echo $k ?>"><?php echo form::radio(array('p_users_gravatar_rating','p_users_gravatar_rating_'.$k), $k, ($aPageData['config']['users']['gravatar']['rating'] == $k)) ?>
		<?php _e($v) ?></label></p>
		<?php endforeach; ?>

	</fieldset>
