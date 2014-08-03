<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$aDefaultGravatarImages = array(
	null => 'c_a_users_config_gravatar_default_image_gravatar',
	'mm' => 'c_a_users_config_gravatar_default_image_mm',
	'identicon' => 'c_a_users_config_gravatar_default_image_identicon',
	'monsterid' => 'c_a_users_config_gravatar_default_image_monsterid',
	'wavatar' => 'c_a_users_config_gravatar_default_image_wavatar',
	'retro' => 'c_a_users_config_gravatar_default_image_retro',
	'blank' => 'c_a_users_config_gravatar_default_image_blank',
	'404' => 'c_a_users_config_gravatar_default_image_404'
);

$aGravatarRatings = array(
	'g' => 'c_a_users_config_gravatar_rating_g',
	'pg' => 'c_a_users_config_gravatar_rating_pg',
	'r' => 'c_a_users_config_gravatar_rating_r',
	'x' => 'c_a_users_config_gravatar_rating_x'
);

$okt->page->css->addCss('
#gravatar_default_image_preview {
	float: left;
	margin: 1em 1em 1em 0;
}
.gravatar_default_image_preview {
	display: none;

	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
');

$okt->page->js->addScript('
	function handleGravatarDefaultImagePreview() {
		var selected = $("#p_users_gravatar_default_image option:checked");

		$("#gravatar_default_image_note").text(selected.attr("title"));

		$(".gravatar_default_image_preview").each(function() {
			if ($(this).attr("id") == "gravatar_default_image_" + selected.val()) {
				$(this).show()
			}
			else {
				$(this).hide();
			}
		});
	}

	function handleGravatarRating() {
		var selected = $("#p_users_gravatar_rating option:checked");
		$("#gravatar_default_rating_note").text(selected.attr("title"));
	}


	function handleGravatarStatus() {
		if ($("#p_users_gravatar_enabled").is(":checked")) {
			$("#p_users_gravatar_default_image,#p_users_gravatar_rating").removeAttr("disabled")
				.parent().parent().removeClass("disabled");
		} else {
			$("#p_users_gravatar_default_image,#p_users_gravatar_rating").attr("disabled", "")
				.parent().parent().addClass("disabled");
		}
	}
');

$okt->page->js->addReady('
	$("#p_users_gravatar_default_image_label").tooltip();
	$("#p_users_gravatar_rating_label").tooltip();

	handleGravatarStatus();
	$("#p_users_gravatar_enabled").change(function(){
		handleGravatarStatus();
	});

	handleGravatarDefaultImagePreview();
	$("#p_users_gravatar_default_image").change(function(){
		handleGravatarDefaultImagePreview();
	});

	handleGravatarRating();
	$("#p_users_gravatar_rating").change(function(){
		handleGravatarRating();
	});
');

?>

<h3><?php _e('c_a_users_config_tab_image_title') ?></h3>

<p class="field">
	<label><?php echo form::checkbox('p_users_gravatar_enabled', 1, $aPageData['config']['users']['gravatar']['enabled'])?>
	<?php printf(__('c_a_users_config_enable_gravatar_%s'), '<a href="https://'.$okt['visitor']->language.'.gravatar.com/">Gravatar</a>') ?></label>
</p>

<div class="two-cols">
	<div class="col">
		<div id="gravatar_default_image_preview">
			<img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?s=60"
				id="gravatar_default_image_" class="gravatar_default_image_preview"
				alt="" /> <img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=mm&amp;s=60"
				id="gravatar_default_image_mm"
				class="gravatar_default_image_preview" alt="" /> <img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=identicon&amp;s=60"
				id="gravatar_default_image_identicon"
				class="gravatar_default_image_preview" alt="" /> <img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=monsterid&amp;s=60"
				id="gravatar_default_image_monsterid"
				class="gravatar_default_image_preview" alt="" /> <img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=wavatar&amp;s=60"
				id="gravatar_default_image_wavatar"
				class="gravatar_default_image_preview" alt="" /> <img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=retro&amp;s=60"
				id="gravatar_default_image_retro"
				class="gravatar_default_image_preview" alt="" /> <img
				src="http://www.gravatar.com/avatar/00000000000000000000000000000000?d=blank&amp;s=60"
				id="gravatar_default_image_blank"
				class="gravatar_default_image_preview" alt="" />
		</div>

		<p class="field">
			<label for="p_users_gravatar_default_image"
				id="p_users_gravatar_default_image_label"
				title="<?php
				_e('c_a_users_config_gravatar_default_image_note')?>"><?php _e('c_a_users_config_gravatar_default_image') ?></label>
			<select name="p_users_gravatar_default_image"
				id="p_users_gravatar_default_image">
				<?php foreach ($aDefaultGravatarImages as $k=>$v) : ?>
				<option value="<?php echo $k ?>"
					title="<?php echo $view->escapeHtmlAttr(__($v.'_note')) ?>"
					<?php
					if ($aPageData['config']['users']['gravatar']['default_image'] == $k)
						echo ' selected="selected"';
					?>><?php _e($v) ?></option>
				<?php endforeach; ?>
			</select> <span id="gravatar_default_image_note" class="note"></span>
		</p>
	</div>
	<div class="col">
		<p class="field">
			<label for="p_users_gravatar_rating"
				id="p_users_gravatar_rating_label"
				title="<?php
				_e('c_a_users_config_gravatar_rating_note')?>"><?php _e('c_a_users_config_gravatar_rating') ?></label>
			<select name="p_users_gravatar_rating" id="p_users_gravatar_rating">
			<?php foreach ($aGravatarRatings as $k=>$v) : ?>
				<option value="<?php echo $k ?>"
					title="<?php echo $view->escapeHtmlAttr(__($v.'_note')) ?>"
					<?php
				if ($aPageData['config']['users']['gravatar']['rating'] == $k)
					echo ' selected="selected"';
				?>><?php _e($v) ?></option>
			<?php endforeach; ?>
			</select> <span id="gravatar_default_rating_note" class="note"></span>
		</p>
	</div>
</div>
<!-- .two-cols -->
