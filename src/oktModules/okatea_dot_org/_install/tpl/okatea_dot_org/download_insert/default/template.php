
<?php use Tao\Utils as util; ?>


<?php # début Okatea : récupération des infos de dernières versions
$aStableVersion = $okt->okatea_dot_org->getLatestStableVersionInfos();
$aDevVersion = $okt->okatea_dot_org->getLatestDevVersionInfos();
# début Okatea : récupération des infos de dernières versions ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<div id="download-insert">
	<h2><?php _e('Download') ?></h2>

	<div class="version">
		<?php if (!empty($aStableVersion['version']) && !empty($aStableVersion['href'])) : ?>
		<a href="<?php echo util::escapeAttrHTML($aStableVersion['href']); ?>">
			<h3><?php _e('Stable') ?></h3>
			<p><strong><?php echo html::escapeHTML($aStableVersion['version']); ?></strong></p>
		</a>
		<?php else : ?>
			<h3><?php _e('Stable') ?></h3>
			<p><abbr title="<?php echo util::escapeAttrHTML(__('currently no release')) ?>">-</abbr></p>
		<?php endif; ?>
	</div>

	<div class="version">
		<?php if (!empty($aDevVersion['version']) && !empty($aDevVersion['href'])) : ?>
		<a href="<?php echo util::escapeAttrHTML($aDevVersion['href']); ?>">
			<h3><?php _e('Dev') ?></h3>
			<p><?php echo html::escapeHTML($aDevVersion['version']); ?></p>
		</a>
		<?php else : ?>
			<h3><?php _e('Dev') ?></h3>
			<p><abbr title="<?php echo util::escapeAttrHTML(__('currently no release')) ?>">-</abbr></p>
		<?php endif; ?>
	</div>

	<p class="github"><a href="https://github.com/okateadotorg/okatea"><?php _e('View sources on GitHub') ?></a></p>
</div>
