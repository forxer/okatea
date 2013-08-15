<?php
/**
 * Création des premiers utilisateurs
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

require OKT_CONFIG_PATH.'/connexion.php';

$sudo_user = OKT_SUDO_USERNAME;
$sudo_password = '';
$sudo_email = OKT_SUDO_EMAIL;

$admin_user = $admin_email = 'contact@'.str_replace('www.','',$_SERVER['HTTP_HOST']);
$admin_password = '';

$errors = new oktErrors;


/* Traitements
------------------------------------------------------------*/

if (!empty($_POST['sended']))
{
	$db = oktDb::getInstance();

	# données en post
	$sudo_user = !empty($_POST['sudo_user']) ? $_POST['sudo_user'] : '';
	$sudo_password = !empty($_POST['sudo_password']) ? $_POST['sudo_password'] : '';
	$sudo_email = !empty($_POST['sudo_email']) ? $_POST['sudo_email'] : '';

	if ($sudo_user == '') {
		$errors->set(__('i_supa_must_sudo_username'));
	}

	if ($sudo_password == '') {
		$errors->set(__('i_supa_must_sudo_password'));
	}

	if ($sudo_email == '') {
		$errors->set(__('i_supa_must_sudo_email'));
	}

	$admin_user = !empty($_POST['admin_user']) ? $_POST['admin_user'] : '';
	$admin_password = !empty($_POST['admin_password']) ? $_POST['admin_password'] : '';
	$admin_email = !empty($_POST['admin_email']) ? $_POST['admin_email'] : '';

	if ($admin_user == '') {
		$errors->set(__('i_supa_must_admin_username'));
	}

	if ($admin_password == '') {
		$errors->set(__('i_supa_must_admin_password'));
	}

	if ($admin_email == '') {
		$errors->set(__('i_supa_must_admin_email'));
	}

	$current_timestamp = time();

	# si pas d'erreur on ajoutent les utilisateurs
	if ($errors->isEmpty())
	{
		# insertion invité id 1
		$query =
		'INSERT INTO `'.OKT_DB_PREFIX.'core_users` (`id`, `username`, `group_id`, `password`) '.
		'VALUES ( 1, \'Guest\', 3, \'Guest\' );';

		$db->query($query);

		# insertion superadmin (id 2)
		$salt = util::random_key(12);
		$password_hash = util::hash($sudo_password, $salt);

		$query =
		'INSERT INTO `'.OKT_DB_PREFIX.'core_users` ('.
			'`id`, `username`, `group_id`, `salt`, `password`, `language`, `timezone`, `email`, `registered`, `last_visit`'.
		') VALUES ( '.
			'2, '.
			'\''.$db->escapeStr($sudo_user).'\', '.
			'1, '.
			'\''.$db->escapeStr($salt).'\', '.
			'\''.$db->escapeStr($password_hash).'\', '.
			'\'fr\', '.
			'\'Europe/Paris\', '.
			'\''.$db->escapeStr($sudo_email).'\', '.
			$current_timestamp.', '.
			$current_timestamp.' '.
		');';

		$db->query($query);

		# insertion admin id 3
		$salt = util::random_key(12);
		$password_hash = util::hash($admin_password, $salt);

		$query =
		'INSERT INTO `'.OKT_DB_PREFIX.'core_users` ('.
			'`id`, `username`, `group_id`, `salt`, `password`, `language`, `timezone`, `email`, `registered`, `last_visit`'.
		') VALUES ( '.
			'3, '.
			'\''.$db->escapeStr($admin_user).'\', '.
			'2, '.
			'\''.$db->escapeStr($salt).'\', '.
			'\''.$db->escapeStr($password_hash).'\', '.
			'\'fr\', '.
			'\'Europe/Paris\', '.
			'\''.$db->escapeStr($admin_email).'\', '.
			$current_timestamp.', '.
			$current_timestamp.' '.
		');';

		$db->query($query);

		$_SESSION['okt_install_sudo_user'] = $sudo_user;
		$_SESSION['okt_install_sudo_password'] = $sudo_password;
		$_SESSION['okt_install_admin_user'] = $admin_user;
		$_SESSION['okt_install_admin_password'] = $admin_password;

		# Inclusion du prepend
		require_once __DIR__.'/../../../oktInc/prepend.php';

		# login
		$okt->user->login($sudo_user,$sudo_password,1);

		http::redirect('index.php?step='.$stepper->getNextStep());
	}
}



/* Affichage
------------------------------------------------------------*/

# En-tête
$title = __('i_supa_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<form action="index.php" method="post">

	<div class="two-cols">
		<div class="col">
			<h3><?php _e('i_supa_account_sudo') ?></h3>

			<p><?php _e('i_supa_account_sudo_note') ?></p>

			<p class="field"><label for="sudo_user" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_username') ?></label>
			<?php echo form::text('sudo_user', 40, 255, html::escapeHTML($sudo_user)) ?></p>

			<p class="field"><label for="sudo_password" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_password') ?></label>
			<?php echo form::text('sudo_password', 40, 255, html::escapeHTML($sudo_password)) ?></p>

			<p class="field"><label for="sudo_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_email') ?></label>
			<?php echo form::text('sudo_email', 40, 255, html::escapeHTML($sudo_email)) ?></p>
		</div>

		<div class="col">
			<h3><?php _e('i_supa_account_admin') ?></h3>

			<p><?php _e('i_supa_account_admin_note') ?></p>

			<p class="field"><label for="admin_user" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_username') ?></label>
			<?php echo form::text('admin_user', 40, 255, html::escapeHTML($admin_user)) ?></p>

			<p class="field"><label for="admin_password" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_password') ?></label>
			<?php echo form::text('admin_password', 40, 255, html::escapeHTML($admin_password)) ?></p>

			<p class="field"><label for="admin_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_email') ?></label>
			<?php echo form::text('admin_email', 40, 255, html::escapeHTML($admin_email)) ?></p>
		</div>
	</div>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $stepper->getCurrentStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>