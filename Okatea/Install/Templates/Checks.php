<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

?>

<?php foreach ($requirements as $i => $group) : ?>

<h3><?php echo $group['group_title'] ?></h3>
<?php echo $requirements[$i]['check_'.$group['group_id']]->getHTML(); ?>

<?php endforeach; ?>

<?php if ($pass_test) : ?>

	<?php if (!$warning_empty) : ?>
<p><?php _e('i_checks_warning') ?></p>
<?php endif; ?>

<form
	action="<?php echo $view->generateUrl($okt->stepper->getNextStep()) ?>"
	method="post">
	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" />
	</p>
</form>

<?php else : ?>

<p class="warning"><?php _e('i_checks_big_loose') ?></p>

<?php endif; ?>