<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<div class="<?php echo $type ?>_box ui-corner-all">
	<?php if (count($messages) > 1) : ?>
	<ul>
		<?php foreach ($messages as $message) : ?>
		<li><?php echo $message ?></li>
		<?php endforeach; ?>
	</ul>
	<?php else : ?>
	<p>
		<?php foreach ($messages as $message) : ?>
			<?php echo $message ?>
		<?php endforeach; ?>
	</p>
	<?php endif; ?>
</div>
