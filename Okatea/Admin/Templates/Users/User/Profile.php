<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use forxer\GravatarLib\Gravatar;
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle($view->escape($okt->user->usedname));

# Tabs
$okt->page->tabs();


$okt->page->css->addCss('
.avatar {
	float: left;
	margin: 0 1em 1em 0;

	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
');

# Avatar
$gravatar = new Gravatar();

$avatarUrl = $gravatar
	->setDefaultImage('mm')
	->setAvatarSize(120)
	->getAvatar($aPageData['user']['email']);


?>

<div id="tabered">
	<ul>
		<li><a href="#tab-show-profil"><?php echo $view->escape($okt->user->usedname) ?></a></li>
		<li><a href="#tab-user-form"><?php _e('c_c_action_Edit')?></a></li>
		<?php if ($okt->checkPerm('change_password')) : ?>
		<li><a href="#tab-password-form"><?php _e('c_c_user_Password')?></a></li>
		<?php endif; ?>
	</ul>

	<div id="tab-show-profil">
		<p><img src="<?php echo $avatarUrl ?>" width="<?php echo $gravatar->getAvatarSize() ?>" height="<?php echo $gravatar->getAvatarSize() ?>" alt="" class="avatar"></p>

	</div><!-- #tab-show-profil -->

	<div id="tab-user-form">
		<form id="edit-user-form" action="<?php echo $view->generateUrl('User_profile') ?>" method="post">

			<?php echo $view->render('Users/User/form_user', array(
				'aPageData'      => $aPageData,
				'aLanguages'     => $aLanguages,
				'aCivilities'    => $aCivilities
			)); ?>

			<p><?php echo form::hidden('form_sent', 1) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
		</form>
	</div><!-- #tab-user-form -->

	<?php if ($okt->checkPerm('change_password')) : ?>
	<div id="tab-password-form">
		<form id="change-password-form" action="<?php echo $view->generateUrl('User_profile') ?>" method="post">

			<?php echo $view->render('Users/User/form_password', array(
				'aPageData'      => $aPageData
			)); ?>

			<p class="note"><?php _e('c_c_users_Note_password')?></p>

			<p><?php echo form::hidden('change_password', 1) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
		</form>
	</div><!-- #tab-password-form -->
	<?php endif; ?>

</div><!-- #tabered -->
