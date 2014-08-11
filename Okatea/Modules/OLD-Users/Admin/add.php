<?php
/**
 * @ingroup okt_module_users
 * @brief Ajout d'un utilisateur
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/

$add_civility = 0;
$add_active = 1;
$add_username = '';
$add_lastname = '';
$add_firstname = '';
$add_password = '';
$add_password_confirm = '';
$add_email = '';
$add_timezone = $okt['config']->timezone;
$add_language = $okt['config']->language;

# Champs personnalisés
if ($okt->users->config->enable_custom_fields)
{
	$aPostedData = [];
	
	# Liste des champs
	$rsFields = $okt->users->fields->getFields(array(
		'status' => true,
		'language' => $okt['visitor']->language
	));
	
	# Initialisation des données des champs
	while ($rsFields->fetch())
	{
		switch ($rsFields->type)
		{
			default:
			case 1: # Champ texte
			case 2: # Zone de texte
				$aPostedData[$rsFields->id] = !empty($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : $rsFields->value;
				break;
			
			case 3: # Menu déroulant
				$aPostedData[$rsFields->id] = isset($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : '';
				break;
			
			case 4: # Boutons radio
				$aPostedData[$rsFields->id] = isset($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : '';
				break;
			
			case 5: # Cases à cocher
				$aPostedData[$rsFields->id] = !empty($_POST[$rsFields->html_id]) && is_array($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : [];
				break;
		}
	}
}

# Ajout d'un utilisateur
if (!empty($_POST['add_user']))
{
	$add_civility = !empty($_POST['add_civility']) ? intval($_POST['add_civility']) : 0;
	$add_active = !empty($_POST['add_active']) ? intval($_POST['add_active']) : 1;
	$add_username = !empty($_POST['add_username']) ? $_POST['add_username'] : '';
	$add_lastname = !empty($_POST['add_lastname']) ? $_POST['add_lastname'] : '';
	$add_firstname = !empty($_POST['add_firstname']) ? $_POST['add_firstname'] : '';
	$add_password = !empty($_POST['add_password']) ? $_POST['add_password'] : '';
	$add_password_confirm = !empty($_POST['add_password_confirm']) ? $_POST['add_password_confirm'] : '';
	$add_email = !empty($_POST['add_email']) ? $_POST['add_email'] : '';
	$add_timezone = !empty($_POST['add_timezone']) ? $_POST['add_timezone'] : '';
	$add_language = !empty($_POST['add_language']) ? $_POST['add_language'] : '';
	
	# peuplement et vérification des champs personnalisés obligatoires
	if ($okt->users->config->enable_custom_fields)
	{
		$okt->users->fields->getPostData($rsFields, $aPostedData);
	}
	
	$add_params = array(
		'civility' => $add_civility,
		'active' => $add_active,
		'username' => $add_username,
		'lastname' => $add_lastname,
		'firstname' => $add_firstname,
		'password' => $add_password,
		'password_confirm' => $add_password_confirm,
		'email' => $add_email,
		'timezone' => $add_timezone,
		'language' => $add_language
	);
	
	if ($okt->error->isEmpty() && ($new_id = $okt->users->addUser($add_params)) !== false)
	{
		if ($okt->users->config->enable_custom_fields)
		{
			while ($rsFields->fetch())
			{
				$okt->users->fields->setUserValues($new_id, $rsFields->id, $aPostedData[$rsFields->id]);
			}
		}
		
		$okt['flashMessages']->success(__('m_users_user_added'));
		
		http::redirect('module.php?m=users&action=edit&id=' . $new_id);
	}
}

/* Affichage
----------------------------------------------------------*/

# Langues
$rs = $okt['languages']->getLanguages();
$aLanguages = [];
while ($rs->fetch())
{
	$aLanguages[html::escapeHTML($rs->title)] = $rs->code;
}

# Civilités
$aCivilities = array_merge(array(
	'&nbsp;' => 0
), module_users::getCivilities(true));

# Titre de la page
$okt->page->addGlobalTitle(__('c_c_action_Add'));

# Validation javascript
$aJsValidateRules = new ArrayObject(array(
	array(
		'id' => 'add_username',
		'rules' => array(
			'required: true',
			'minlength: 2',
			'maxlength: 125'
		)
	),
	array(
		'id' => 'add_email',
		'rules' => array(
			'required: true',
			'email: true'
		)
	),
	array(
		'id' => 'add_password',
		'rules' => array(
			'required: true',
			'minlength: 4'
		)
	),
	array(
		'id' => 'add_password_confirm',
		'rules' => array(
			'required: true',
			'equalTo: \'#add_password\''
		)
	)
));

if ($okt->users->config->enable_custom_fields)
{
	while ($rsFields->fetch())
	{
		if ($rsFields->status != 2)
		{
			continue;
		}
		
		$aJsValidateRules[] = array(
			'id' => $rsFields->html_id,
			'rules' => array(
				'required: true'
			)
		);
	}
}

$okt->page->validate('add-user-form', $aJsValidateRules);

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<?php 
# buttons set
echo $okt->page->getButtonSet('users');
?>

<form id="add-user-form" action="module.php" method="post">
	<fieldset>
		<legend><?php _e('m_users_enter_new_user_informations')?></legend>

		<div class="three-cols">
			<p class="field col">
				<label for="add_username" title="<?php _e('c_c_required_field') ?>"
					class="required"><?php _e('c_c_user_Username') ?></label>
			<?php echo form::text('add_username', 40, 255, html::escapeHTML($add_username)) ?></p>

			<p class="field col">
				<label for="add_email" title="<?php _e('c_c_required_field') ?>"
					class="required"><?php _e('c_c_Email') ?></label>
			<?php echo form::text('add_email', 40, 255, html::escapeHTML($add_email)) ?></p>

			<p class="field col">
				<label for="add_active"><?php echo form::checkbox('add_active', 1, $add_active) ?> <?php _e('c_c_status_Active') ?></label>
			</p>
		</div>

		<div class="three-cols">
			<p class="field col">
				<label for="add_civility"><?php _e('c_c_Civility')?></label>
			<?php echo form::select('add_civility', $aCivilities, $add_civility) ?></p>

			<p class="field col">
				<label for="add_lastname"><?php _e('c_c_Last_name')?></label>
			<?php echo form::text('add_lastname', 40, 255, html::escapeHTML($add_lastname)) ?></p>

			<p class="field col">
				<label for="add_firstname"><?php _e('c_c_First_name')?></label>
			<?php echo form::text('add_firstname', 40, 255, html::escapeHTML($add_firstname)) ?></p>
		</div>

		<div class="two-cols">
			<p class="field col">
				<label for="add_password" title="<?php _e('c_c_required_field') ?>"
					class="required"><?php _e('c_c_user_Password')?></label>
			<?php echo form::password('add_password', 40, 255, html::escapeHTML($add_password)) ?></p>

			<p class="field col">
				<label for="add_password_confirm"
					title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_auth_confirm_password')?></label>
			<?php echo form::password('add_password_confirm', 40, 255, html::escapeHTML($add_password_confirm)) ?></p>
		</div>

		<div class="two-cols">
			<p class="field col">
				<label for="add_language"><?php _e('c_c_Language')?></label>
			<?php echo form::select('add_language', $aLanguages, html::escapeHTML($add_language)) ?></p>

			<p class="field col">
				<label for="add_timezone"><?php _e('c_c_Timezone')?></label>
			<?php echo form::select('add_timezone', dt::getZones(true,true), html::escapeHTML($add_timezone)) ?></p>
		</div>

		<?php if ($okt->users->config->enable_custom_fields) : ?>
		<div class="two-cols">
		<?php 
# début Okatea : boucle sur les champs
			while ($rsFields->fetch())
			:
				?>

			<div class="col"><?php echo $rsFields->getHtmlField($aPostedData) ?></div>

		<?php endwhile; # fin Okatea : boucle sur les champs ?>
		</div>
		<?php endif; ?>

	</fieldset>
	<p><?php echo form::hidden('m','users')?>
	<?php echo form::hidden('action','add')?>
	<?php echo form::hidden('add_user',1)?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Add') ?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
