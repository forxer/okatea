<?php
/**
 * @ingroup okt_module_users
 * @brief Page de modification d'un utilisateur
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Users\Authentification;
use Okatea\Tao\Misc\Mailer;

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/

$aEditPageInfos = new ArrayObject();

# récupération des infos utilisateur
$aEditPageInfos['iUserId'] = ! empty($_REQUEST['id']) ? $_REQUEST['id'] : NULL;

if (is_null($aEditPageInfos['iUserId']) || $aEditPageInfos['iUserId'] == 1)
{
	http::redirect('module.php?m=users');
}

$user = $okt->users->getUser($aEditPageInfos['iUserId']);

$edit_group_id = $user->group_id;
$edit_civility = $user->civility;
$edit_active = $user->active;
$edit_username = $user->username;
$edit_lastname = $user->lastname;
$edit_firstname = $user->firstname;
$edit_email = $user->email;
$edit_timezone = $user->timezone;
$edit_language = $user->language;

$edit_password = '';
$edit_password_confirm = '';

# Champs personnalisés
if ($okt->users->config->enable_custom_fields)
{
	$aPostedData = array();
	
	# Liste des champs
	$rsFields = $okt->users->fields->getFields(array(
		'status' => true,
		'language' => $okt['visitor']->language
	));
	
	# Valeurs des champs
	$rsFieldsValues = $okt->users->fields->getUserValues($aEditPageInfos['iUserId']);
	$aFieldsValues = array();
	while ($rsFieldsValues->fetch())
	{
		$aFieldsValues[$rsFieldsValues->field_id] = $rsFieldsValues->value;
	}
	
	# Initialisation des données des champs
	while ($rsFields->fetch())
	{
		switch ($rsFields->type)
		{
			default:
			case 1: # Champ texte
			case 2: # Zone de texte
				$aPostedData[$rsFields->id] = ! empty($aFieldsValues[$rsFields->id]) ? $aFieldsValues[$rsFields->id] : '';
				break;
			
			case 3: # Menu déroulant
				$aPostedData[$rsFields->id] = ! empty($aFieldsValues[$rsFields->id]) ? $aFieldsValues[$rsFields->id] : '';
				break;
			
			case 4: # Boutons radio
				$aPostedData[$rsFields->id] = ! empty($aFieldsValues[$rsFields->id]) ? $aFieldsValues[$rsFields->id] : '';
				break;
			
			case 5: # Cases à cocher
				$aPostedData[$rsFields->id] = ! empty($aFieldsValues[$rsFields->id]) ? $aFieldsValues[$rsFields->id] : '';
				break;
		}
	}
}

# un super admin ne peut etre modifié par un non super admin
if ($edit_group_id == Authentification::superadmin_group_id && ! $okt['visitor']->is_superadmin)
{
	http::redirect('module.php?m=users');
}

# un admin ne peut etre modifié par un non admin
if ($edit_group_id == Authentification::admin_group_id && ! $okt['visitor']->is_admin)
{
	http::redirect('module.php?m=users');
}

if ($user->group_id == Groups::UNVERIFIED)
{
	$aEditPageInfos['bWaitingValidation'] = true;
}
else
{
	$aEditPageInfos['bWaitingValidation'] = false;
}

# -- CORE TRIGGER : adminModUsersEditInit
$okt['triggers']->callTrigger('adminModUsersEditInit', $aEditPageInfos);

/* Traitements
----------------------------------------------------------*/

# Validation de l'utilisateur
if (! empty($_GET['valide']) && $okt->checkPerm('users_edit'))
{
	$upd_params = array(
		'id' => $aEditPageInfos['iUserId'],
		'group_id' => $okt['config']->users_registration['default_group']
	);
	
	if ($okt->users->updUser($upd_params))
	{
		$oMail = new Mailer($okt);
		
		$oMail->setFrom();
		
		$oMail->useFile(__DIR__ . '/../../Locales/' . $edit_language . '/Templates/validate_user.tpl', array(
			'SITE_TITLE' => $okt->page->getSiteTitle($edit_language),
			'SITE_URL' => $okt['request']->getSchemeAndHttpHost() . $okt['app_url']
		));
		
		$oMail->message->setTo($edit_email);
		
		$oMail->send();
		
		$okt['flash']->success(__('m_users_validated_user'));
		
		http::redirect('module.php?m=users&action=edit&id=' . $aEditPageInfos['iUserId']);
	}
}

# Formulaire de changement de mot de passe
if (! empty($_POST['change_password']) && $okt->checkPerm('change_password') && $okt->checkPerm('users_edit'))
{
	$upd_params = array(
		'id' => $aEditPageInfos['iUserId']
	);
	
	$upd_params['password'] = ! empty($_POST['edit_password']) ? $_POST['edit_password'] : '';
	$upd_params['password_confirm'] = ! empty($_POST['edit_password_confirm']) ? $_POST['edit_password_confirm'] : '';
	
	if ($okt->users->changeUserPassword($upd_params))
	{
		if (! empty($_POST['send_password_mail']))
		{
			$oMail = new Mailer($okt);
			
			$oMail->setFrom();
			
			$oMail->useFile(__DIR__ . '/../../Locales/' . $edit_language . '/Templates/admin_change_user_password.tpl', array(
				'SITE_TITLE' => $okt->page->getSiteTitle($edit_language),
				'SITE_URL' => $okt['request']->getSchemeAndHttpHost() . $okt['app_url'],
				'NEW_PASSWORD' => $upd_params['password']
			));
			
			$oMail->message->setTo($edit_email);
			$oMail->send();
		}
		
		$okt['flash']->success(__('m_users_user_edited'));
		
		http::redirect('module.php?m=users&action=edit&id=' . $aEditPageInfos['iUserId']);
	}
}

# Formulaire de modification de l'utilisateur envoyé
if (! empty($_POST['form_sent']) && ! isset($_POST['do']) && $okt->checkPerm('users_edit'))
{
	$upd_params = array(
		'id' => $aEditPageInfos['iUserId']
	);
	
	if (isset($_POST['edit_civility']))
	{
		$upd_params['civility'] = $_POST['edit_civility'];
	}
	
	if (isset($_POST['edit_active']))
	{
		$upd_params['active'] = $_POST['edit_active'];
	}
	
	if (isset($_POST['edit_username']))
	{
		$upd_params['username'] = $_POST['edit_username'];
	}
	
	if (isset($_POST['edit_email']))
	{
		$upd_params['email'] = $_POST['edit_email'];
	}
	
	if (isset($_POST['edit_lastname']))
	{
		$upd_params['lastname'] = $_POST['edit_lastname'];
	}
	
	if (isset($_POST['edit_firstname']))
	{
		$upd_params['firstname'] = $_POST['edit_firstname'];
	}
	
	if (isset($_POST['edit_language']))
	{
		$upd_params['language'] = $_POST['edit_language'];
	}
	
	if (isset($_POST['edit_timezone']))
	{
		$upd_params['timezone'] = $_POST['edit_timezone'];
	}
	
	if (isset($_POST['edit_group_id']))
	{
		$upd_params['group_id'] = $_POST['edit_group_id'];
	}
	
	# peuplement et vérification des champs personnalisés obligatoires
	if ($okt->users->config->enable_custom_fields)
	{
		$okt->users->fields->getPostData($rsFields, $aPostedData);
	}
	
	if ($okt->error->isEmpty() && $okt->users->updUser($upd_params))
	{
		if ($okt->users->config->enable_custom_fields)
		{
			while ($rsFields->fetch())
			{
				$okt->users->fields->setUserValues($aEditPageInfos['iUserId'], $rsFields->id, $aPostedData[$rsFields->id]);
			}
		}
		
		$okt['flash']->success(__('m_users_user_edited'));
		
		http::redirect('module.php?m=users&action=edit&id=' . $aEditPageInfos['iUserId']);
	}
}

# -- CORE TRIGGER : adminModUsersEditProcess
$okt['triggers']->callTrigger('adminModUsersEditProcess', $aEditPageInfos);

/* Affichage
----------------------------------------------------------*/

# Langues
$rs = $okt['languages']->getLanguages();
$aLanguages = array();
while ($rs->fetch())
{
	$aLanguages[html::escapeHTML($rs->title)] = $rs->code;
}

# Civilités
$aCivilities = array_merge(array(
	'&nbsp;' => 0
), module_users::getCivilities(true));

# Groupes
$rs = $okt->users->getGroups();
$groups_array = array();
while ($rs->fetch())
{
	if ($rs->group_id == Authentification::superadmin_group_id && ! $okt['visitor']->is_superadmin)
	{
		continue;
	}
	
	$groups_array[html::escapeHTML($rs->title)] = $rs->group_id;
}

# Titre de la page
$okt->page->addGlobalTitle(sprintf(__('m_users_user_%s'), $edit_username));

# Validation javascript
$aJsValidateRules = new ArrayObject(array(
	array(
		'id' => 'edit_username',
		'rules' => array(
			'required: true',
			'minlength: 2',
			'maxlength: 125'
		)
	),
	array(
		'id' => 'edit_email',
		'rules' => array(
			'required: true',
			'email: true'
		)
	),
	array(
		'id' => 'edit_password',
		'rules' => array(
			'required: true',
			'minlength: 4'
		)
	),
	array(
		'id' => 'edit_password_confirm',
		'rules' => array(
			'required: true',
			'equalTo: \'#edit_password\''
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

$okt->page->validate('edit-user-form', $aJsValidateRules);

# Tabs
$okt->page->tabs();

if ($aEditPageInfos['bWaitingValidation'])
{
	$okt->page->warnings->set(__('m_users_user_in_wait_of_validation'));
}

$okt->page->addButton('users', array(
	'permission' => $aEditPageInfos['bWaitingValidation'],
	'title' => __('m_users_validate_this_user'),
	'url' => 'module.php?m=users&amp;action=edit&amp;id=' . $aEditPageInfos['iUserId'] . '&amp;valide=1',
	'ui-icon' => 'check'
));
$okt->page->addButton('users', array(
	'permission' => $okt->checkPerm('users_delete'),
	'title' => __('c_c_action_Delete'),
	'url' => 'module.php?m=users&amp;action=index&amp;delete=' . $aEditPageInfos['iUserId'],
	'ui-icon' => 'closethick',
	'onclick' => 'return window.confirm(\'' . html::escapeJS(__('m_users_confirm_user_deletion')) . '\')'
));

# Construction des onglets
$aEditTabs = new ArrayObject();

if ($okt->checkPerm('users_edit'))
{
	$aEditTabs[10] = array(
		'id' => 'tab-edit-user',
		'title' => __('m_users_General'),
		'content' => ''
	);
	
	$aEditTabs[10]['content'] = '<h3>' . sprintf(__('m_users_edit_informations_of_user_%s'), '<em>' . html::escapeHTML($edit_username) . '</em>') . '</h3>

		<form id="edit-user-form" action="module.php" method="post">
			<fieldset>
				<legend>' . __('m_users_Edit_informations') . '</legend>
				<div class="three-cols">
					<p class="field col"><label for="edit_username" title="' . __('c_c_required_field') . '" class="required">' . __('c_c_user_Username') . '</label>' . form::text('edit_username', 40, 255, html::escapeHTML($edit_username)) . '</p>

					<p class="field col"><label for="edit_email" title="' . __('c_c_required_field') . '" class="required">' . __('c_c_Email') . '</label>' . form::text('edit_email', 40, 255, html::escapeHTML($edit_email)) . '</p>

					<p class="field col"><label for="edit_active">' . form::checkbox('edit_active', 1, $edit_active) . ' ' . __('c_c_status_Active') . '</label></p>
				</div>
				<div class="three-cols">
					<p class="field col"><label for="edit_civility">' . __('c_c_Civility') . '</label>' . form::select('edit_civility', $aCivilities, $edit_civility) . '</p>

					<p class="field col"><label for="edit_lastname">' . __('c_c_Name') . '</label>' . form::text('edit_lastname', 40, 255, html::escapeHTML($edit_lastname)) . '</p>

					<p class="field col"><label for="edit_firstname">' . __('c_c_First_name') . '</label>' . form::text('edit_firstname', 40, 255, html::escapeHTML($edit_firstname)) . '</p>
				</div>
				<div class="two-cols">
					<p class="field col"><label for="edit_language">' . __('c_c_Language') . '</label>' . form::select('edit_language', $aLanguages, html::escapeHTML($edit_language)) . '</p>

					<p class="field col"><label for="edit_timezone">' . __('c_c_Timezone') . '</label>' . form::select('edit_timezone', dt::getZones(true, true), html::escapeHTML($edit_timezone)) . '</p>
				</div>';
	
	$aEditTabs[10]['content'] .= '
				<div class="two-cols">';
	
	if ($aEditPageInfos['bWaitingValidation'])
	{
		$aEditTabs[10]['content'] .= '<p class="col">' . __('m_users_user_in_wait_of_validation') . ', <a href="module.php?m=users&amp;action=edit&amp;id=' . $aEditPageInfos['iUserId'] . '&amp;valide=1">' . __('m_users_validate_this_user') . '</a>.</p>';
	}
	else
	{
		$aEditTabs[10]['content'] .= '<p class="field col"><label for="edit_group_id">' . __('c_c_Group') . '</label>' . form::select('edit_group_id', $groups_array, $edit_group_id) . '</p>';
	}
	
	$aEditTabs[10]['content'] .= '</div>';
	
	if ($okt->users->config->enable_custom_fields)
	{
		$aEditTabs[10]['content'] .= '<div class="two-cols">';
		while ($rsFields->fetch())
		{
			$aEditTabs[10]['content'] .= '<div class="col">' . $rsFields->getHtmlField($aPostedData) . '</div>';
		}
		$aEditTabs[10]['content'] .= '</div>';
	}
	
	$aEditTabs[10]['content'] .= '</fieldset>

			<p>' . form::hidden('form_sent', 1) . form::hidden('m', 'users') . form::hidden('action', 'edit') . form::hidden('id', $aEditPageInfos['iUserId']) . Page::formtoken() . '<input type="submit" value="' . __('c_c_action_Edit') . '" /></p>
		</form>';
	
	if ($okt->checkPerm('change_password'))
	{
		$aEditTabs[100] = array(
			'id' => 'tab-change-password',
			'title' => __('c_c_user_Password'),
			'content' => ''
		);
		
		$aEditTabs[100]['content'] = '<h3>Mot de passe</h3>

		<form action="module.php" method="post">
			<fieldset>
				<legend>' . __('m_users_Edit_password') . '</legend>
				<div class="two-cols">
					<p class="field col"><label for="edit_password">' . __('c_c_user_Password') . '</label>' . form::password('edit_password', 40, 255, html::escapeHTML($edit_password)) . '</p>

					<p class="field col"><label for="edit_password_confirm">' . __('c_c_auth_confirm_password') . '</label>' . form::password('edit_password_confirm', 40, 255, html::escapeHTML($edit_password_confirm)) . '</p>
				</div>
				<div class="two-cols">
					<p class="field col"><label>' . form::checkbox('send_password_mail', 1, 0) . ' ' . __('m_users_Alert_user_by_email') . '</label></p>
				</div>
			</fieldset>
			<p>' . form::hidden('change_password', 1) . form::hidden('m', 'users') . form::hidden('action', 'edit') . form::hidden('id', $aEditPageInfos['iUserId']) . Page::formtoken() . '<input type="submit" value="' . __('c_c_action_Edit') . '" /></p>
		</form>';
	}
}

# -- CORE TRIGGER : adminModUsersEditDisplayTabs
$okt['triggers']->callTrigger('adminModUsersEditDisplayTabs', $aEditPageInfos, $aEditTabs);

$aEditTabs->ksort();

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<?php 
# buttons set
echo $okt->page->getButtonSet('users');
?>

<div id="tabered">
	<ul>
		<?php foreach ($aEditTabs as $aTabInfos) : ?>
		<li><a href="#<?php
			
echo $aTabInfos['id']?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
		<?php endforeach; ?>
	</ul>

	<?php foreach ($aEditTabs as $sTabUrl=>$aTabInfos) : ?>
	<div id="<?php echo $aTabInfos['id'] ?>">
		<?php echo $aTabInfos['content']?>
	</div>
	<!-- #<?php echo $aTabInfos['id'] ?> -->
	<?php endforeach; ?>

</div>
<!-- #tabered -->

<?php 
# Pied-de-page
require OKT_ADMIN_FOOTER_FILE;
?>
