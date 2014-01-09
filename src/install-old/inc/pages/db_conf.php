<?php
/**
 * Création du fichier de connexion
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

$prod_host = 'sql5';
$prod_database = 'okatea_project';
$prod_user = 'root';
$prod_password = '';
$prod_prefix = 'okt_';

$dev_host = 'localhost';
$dev_database = 'okatea_project';
$dev_user = 'root';
$dev_password = '';
$dev_prefix = 'okt_';

$environement = (defined('OKT_ENVIRONMENT') && OKT_ENVIRONMENT == 'dev') ? 'dev': 'prod';

$errors = new oktErrors;


/* Traitements
------------------------------------------------------------*/

if (!empty($_POST['sended']))
{
	# Données environnement de production
	$prod_host = !empty($_POST['prod_host']) ? $_POST['prod_host'] : $prod_host;
	$prod_database = !empty($_POST['prod_database']) ? $_POST['prod_database'] : $prod_database;
	$prod_user = !empty($_POST['prod_user']) ? $_POST['prod_user'] : $prod_user;
	$prod_password = !empty($_POST['prod_password']) ? $_POST['prod_password'] : $prod_password;
	$prod_prefix = !empty($_POST['prod_prefix']) ? $_POST['prod_prefix'] : $prod_prefix;

	if ($prod_prefix != '' && !preg_match('/^[A-Za-z0-9_]+$/',$prod_prefix)) {
		$errors->set(__('i_db_conf_db_error_prod_prefix_form'));
	}
	elseif ($prod_prefix == '') {
		$errors->set(__('i_db_conf_db_error_prod_must_prefix'));
	}

	if ($prod_host == '') {
		$errors->set(__('i_db_conf_db_error_prod_must_host'));
	}

	if ($prod_database == '') {
		$errors->set(__('i_db_conf_db_error_prod_must_name'));
	}

	if ($prod_user == '') {
		$errors->set(__('i_db_conf_db_error_prod_must_username'));
	}

	# Données environnement de développement
	$dev_host = !empty($_POST['dev_host']) ? $_POST['dev_host'] : $dev_host;
	$dev_database = !empty($_POST['dev_database']) ? $_POST['dev_database'] : $dev_database;
	$dev_user = !empty($_POST['dev_user']) ? $_POST['dev_user'] : $dev_user;
	$dev_password = !empty($_POST['dev_password']) ? $_POST['dev_password'] : $dev_password;
	$dev_prefix = !empty($_POST['dev_prefix']) ? $_POST['dev_prefix'] : $dev_prefix;

	if ($dev_prefix != '' && !preg_match('/^[A-Za-z_]+$/',$dev_prefix)) {
		$errors->set(__('i_db_conf_db_error_dev_prefix_form'));
	}
	elseif ($dev_prefix == '') {
		$errors->set(__('i_db_conf_db_error_dev_must_prefix'));
	}

	if ($dev_host == '') {
		$errors->set(__('i_db_conf_db_error_dev_must_host'));
	}

	if ($dev_database == '') {
		$errors->set(__('i_db_conf_db_error_dev_must_name'));
	}

	if ($dev_user == '') {
		$errors->set(__('i_db_conf_db_error_dev_must_username'));
	}

	$environement = (!empty($_POST['connect']) && ($_POST['connect'] == 'dev' || $_POST['connect'] == 'prod')) ? $_POST['connect'] : 'dev';


	# Tentative de connexion à la base de données
	$con_id = mysqli_connect(${$environement.'_host'}, ${$environement.'_user'}, ${$environement.'_password'});

	if (!$con_id) {
		$errors->set('MySQL: '.mysqli_connect_errno().' '.mysqli_connect_error());
	}
	else
	{
		mysqli_query($con_id, "CREATE DATABASE IF NOT EXISTS ".${$environement.'_database'});

		$db = mysqli_select_db($con_id, ${$environement.'_database'});

		if (!$db) {
			$errors->set('MySQL: '.mysqli_errno($con_id).' '.mysqli_error($con_id));
		}

		mysqli_close($con_id);
	}

	if ($errors->isEmpty())
	{
		$db = new mysql();
		$db->init(${$environement.'_user'},${$environement.'_password'},${$environement.'_host'},${$environement.'_database'});

		if ($db->error()) {
			$errors->set($db->error());
		}
		else {
			# Création du fichier de configuration
			$configfile = OKT_CONFIG_PATH.'/connexion.php';
			$config = implode('',(array)file($configfile.'.in'));

			$config = str_replace('%%DB_PROD_HOST%%',$prod_host,$config);
			$config = str_replace('%%DB_PROD_USER%%',$prod_user,$config);
			$config = str_replace('%%DB_PROD_PASS%%',$prod_password,$config);
			$config = str_replace('%%DB_PROD_BASE%%',$prod_database,$config);
			$config = str_replace('%%DB_PROD_PREFIX%%',$prod_prefix,$config);

			$config = str_replace('%%DB_DEV_HOST%%',$dev_host,$config);
			$config = str_replace('%%DB_DEV_USER%%',$dev_user,$config);
			$config = str_replace('%%DB_DEV_PASS%%',$dev_password,$config);
			$config = str_replace('%%DB_DEV_BASE%%',$dev_database,$config);
			$config = str_replace('%%DB_DEV_PREFIX%%',$dev_prefix,$config);

			if (($fp = @fopen($configfile,'w')) !== false)
			{
				fwrite($fp,$config,strlen($config));
				fclose($fp);
			}
			else {
				$errors->set(__('c_c_error_writing_configuration'));
			}

			$_SESSION['okt_install_environement'] = $environement;
		}
	}
}


/* Affichage
------------------------------------------------------------*/

$oHtmlPage->js->addReady('
	function focusEnvironmentPart() {
		if ($("#connect_prod").is(":checked")) {
			$("#dev-part legend").removeClass("formfocus");
			$("#prod-part legend").addClass("formfocus");
		}
		else if ($("#connect_dev").is(":checked")) {
			$("#dev-part legend").addClass("formfocus");
			$("#prod-part legend").removeClass("formfocus");
		}
	}

	focusEnvironmentPart();
	$(\'input[name="connect"]\').click(focusEnvironmentPart);
');


# En-tête
$title = __('i_db_conf_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<?php if (!empty($_POST['sended']) && $errors->isEmpty()) : ?>

	<form action="index.php" method="post">

		<p><?php _e('i_db_conf_ok') ?></p>

		<p><input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
		<input type="hidden" name="step" value="<?php echo $okt->stepper->getNextStep() ?>" /></p>
	</form>

<?php else : ?>
<form action="index.php" method="post">

	<p><?php _e('i_db_conf_environement_choice') ?></p>
	<ul class="checklist">
		<li><label for="connect_prod"><input type="radio" name="connect" id="connect_prod" value="prod"<?php if ($environement == 'prod') echo ' checked="checked"'; ?> /> <strong><?php _e('i_db_conf_environement_prod') ?></strong></label></li>
		<li><label for="connect_dev"><input type="radio" name="connect" id="connect_dev" value="dev"<?php if ($environement == 'dev') echo ' checked="checked"'; ?> /> <?php _e('i_db_conf_environement_dev') ?></label></li>
	</ul>
	<p class="note"><?php _e('i_db_conf_environement_note') ?></p>

	<div class="two-cols">
		<div id="prod-part" class="col">
		<fieldset>
			<legend><?php _e('i_db_conf_prod_server') ?></legend>

			<p class="field"><label for="prod_host" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_host') ?></label>
			<?php echo form::text('prod_host', 40, 256, html::escapeHTML($prod_host)) ?></p>

			<p class="field"><label for="prod_database" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_name') ?></label>
			<?php echo form::text('prod_database', 40, 256, html::escapeHTML($prod_database)) ?></p>

			<p class="field"><label for="prod_user" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_username') ?></label>
			<?php echo form::text('prod_user', 40, 256, html::escapeHTML($prod_user)) ?></p>

			<p class="field"><label for="prod_password"><?php _e('i_db_conf_db_password') ?></label>
			<?php echo form::text('prod_password', 40, 256, html::escapeHTML($prod_password)) ?></p>

			<p class="field"><label for="prod_prefix" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_prefix') ?></label>
			<?php echo form::text('prod_prefix', 40, 256, html::escapeHTML($prod_prefix)) ?></p>
		</fieldset>
		</div>

		<div id="dev-part" class="col">
		<fieldset>
			<legend><?php _e('i_db_conf_dev_server') ?></legend>

			<p class="field"><label for="dev_host" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_host') ?></label>
			<?php echo form::text('dev_host', 40, 256, html::escapeHTML($dev_host)) ?></p>

			<p class="field"><label for="dev_database" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_name') ?></label>
			<?php echo form::text('dev_database', 40, 256, html::escapeHTML($dev_database)) ?></p>

			<p class="field"><label for="dev_user" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_username') ?></label>
			<?php echo form::text('dev_user', 40, 256, html::escapeHTML($dev_user)) ?></p>

			<p class="field"><label for="dev_password"><?php _e('i_db_conf_db_password') ?></label>
			<?php echo form::text('dev_password', 40, 256, html::escapeHTML($dev_password)) ?></p>

			<p class="field"><label for="dev_prefix" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_db_conf_db_prefix') ?></label>
			<?php echo form::text('dev_prefix', 40, 256, html::escapeHTML($dev_prefix)) ?></p>
		</fieldset>
		</div>
	</div>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $okt->stepper->getCurrentStep() ?>" /></p>
</form>
<?php endif; ?>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
