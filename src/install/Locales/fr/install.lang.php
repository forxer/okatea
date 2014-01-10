<?php

# common
$GLOBALS['__l10n']['i_install_interface'] = 'Interface d’installation';
$GLOBALS['__l10n']['i_update_interface'] = 'Interface de mise à jour';
$GLOBALS['__l10n']['i_errors'] = 'Erreur(s)';

# steps
$GLOBALS['__l10n']['i_step_start'] = 'accueil';
$GLOBALS['__l10n']['i_step_checks'] = 'pré-requis';
$GLOBALS['__l10n']['i_step_db_conf'] = 'base de données';
$GLOBALS['__l10n']['i_step_db'] = 'tables';
$GLOBALS['__l10n']['i_step_supa'] = 'utilisateurs';
$GLOBALS['__l10n']['i_step_config'] = 'configuration';
$GLOBALS['__l10n']['i_step_log'] = 'enregistrement';
$GLOBALS['__l10n']['i_step_theme'] = 'thème';
$GLOBALS['__l10n']['i_step_colors'] = 'couleurs';
$GLOBALS['__l10n']['i_step_modules'] = 'modules';
$GLOBALS['__l10n']['i_step_pages'] = 'pages';
$GLOBALS['__l10n']['i_step_merge_config'] = 'fusion configuration';
$GLOBALS['__l10n']['i_step_end'] = 'fin';

# start
$GLOBALS['__l10n']['i_start_about_install'] = 'Vous êtes sur le point <strong>d’installer</strong> Okatea %s.';
$GLOBALS['__l10n']['i_start_about_update'] = 'Vous êtes sur le point <strong>de mettre à jour</strong> Okatea à la version <em>%s</em>.';

$GLOBALS['__l10n']['i_start_choose_lang'] = 'Vous pouvez choisir la langue de l’interface&nbsp;:';
$GLOBALS['__l10n']['i_start_click_next'] = 'Pour continuer veuillez cliquer sur le bouton "suivant" ci-dessous.';

# checks
$GLOBALS['__l10n']['i_checks_title'] = 'Vérification des pré-requis';
$GLOBALS['__l10n']['i_checks_warning'] = '<strong>Avertissement :</strong> le système de vérification à émis des alertes qui n’empêche pas le système de fonctionner mais il est possible que certaines fonctionnalités soient défaillantes.';
$GLOBALS['__l10n']['i_checks_big_loose'] = 'La configuration serveur présente des problèmes majeurs. Le système ne peut pas être installé sur ce serveur.';

# db conf
$GLOBALS['__l10n']['i_db_conf_title'] = 'Connexion à la base de données';
$GLOBALS['__l10n']['i_db_conf_ok'] = 'Connexion à la base de données réussie, fichier de connexion créé. Cliquez sur suivant pour créer les tables.';
$GLOBALS['__l10n']['i_db_conf_environement_choice'] = 'Tester la connexion sur l’environnement de :';
$GLOBALS['__l10n']['i_db_conf_environement_prod'] = 'production';
$GLOBALS['__l10n']['i_db_conf_environement_dev'] = 'développement';
$GLOBALS['__l10n']['i_db_conf_environement_note'] = 'Vous devez choisir l’environnement sur lequel vous êtes en train d’installer le système.';
$GLOBALS['__l10n']['i_db_conf_prod_server'] = 'Serveur de production';
$GLOBALS['__l10n']['i_db_conf_dev_server'] = 'Serveur de développement';
$GLOBALS['__l10n']['i_db_conf_db_host'] = 'Hôte de la base de données';
$GLOBALS['__l10n']['i_db_conf_db_name'] = 'Nom de la base de données';
$GLOBALS['__l10n']['i_db_conf_db_username'] = 'Nom d’utilisateur de la base de données';
$GLOBALS['__l10n']['i_db_conf_db_password'] = 'Mot de passe de la base de données';
$GLOBALS['__l10n']['i_db_conf_db_prefix'] = 'Préfixe des tables';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_prefix_form'] = 'Le préfixe pour l’environnement de production n’est pas valide. Il ne peut contenir que des lettres et le caractère "_".';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_prefix_form'] = 'Le préfixe pour l’environnement de développement n’est pas valide. Il ne peut contenir que des lettres et le caractère "_".';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_prefix'] = 'Vous devez saisir un préfixe de base de données pour l’environnement de production.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_prefix'] = 'Vous devez saisir un préfixe de base de données pour l’environnement de développement.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_host'] = 'Vous devez saisir un hote de base de données pour l’environnement de production.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_host'] = 'Vous devez saisir un hote de base de données pour l’environnement de développement.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_name'] = 'Vous devez saisir un nom de base de données pour l’environnement de production.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_name'] = 'Vous devez saisir un nom de base de données pour l’environnement de développement.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_username'] = 'Vous devez saisir un nom d’utilisateur de base de données pour l’environnement de production.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_username'] = 'Vous devez saisir un nom d’utilisateur de base de données pour l’environnement de développement.';

# connexion
$GLOBALS['__l10n']['i_connexion_title'] = 'Connexion à la base de données';
$GLOBALS['__l10n']['i_connexion_success'] = 'Connexion à la base de données réussie. Cliquez sur suivant pour mettre à jours les tables.';

# db
$GLOBALS['__l10n']['i_db_title'] = 'Création des tables';
$GLOBALS['__l10n']['i_db_warning'] = '<strong>Avertissement :</strong> le système de vérification à émis des alertes mais cela ne devrait pas poser de problèmes.';
$GLOBALS['__l10n']['i_db_big_loose'] = 'Des erreurs bloquantes se sont produites, impossible de continuer l’installation.';

# supa
$GLOBALS['__l10n']['i_supa_title'] = 'Création des comptes administrateurs';
$GLOBALS['__l10n']['i_supa_account_sudo'] = 'Compte super-administrateur';
$GLOBALS['__l10n']['i_supa_account_sudo_note'] = 'Le compte super-administrateur est le compte que nous utilisons. Il permet notamment de configurer le système.';
$GLOBALS['__l10n']['i_supa_account_admin'] = 'Compte administrateur';
$GLOBALS['__l10n']['i_supa_account_admin_note'] = 'Le compte administrateur est le compte que le client va utiliser.';
$GLOBALS['__l10n']['i_supa_username'] = 'Nom d’utilisateur';
$GLOBALS['__l10n']['i_supa_password'] = 'Mot de passe';
$GLOBALS['__l10n']['i_supa_email'] = 'Adresse email';
$GLOBALS['__l10n']['i_supa_must_sudo_username'] = 'Vous devez saisir un nom d’utilisateur pour le compte super-administrateur.';
$GLOBALS['__l10n']['i_supa_must_admin_username'] = 'Vous devez saisir un nom d’utilisateur pour le compte administrateur.';
$GLOBALS['__l10n']['i_supa_must_sudo_password'] = 'Vous devez saisir un mot de passe pour le compte super-administrateur.';
$GLOBALS['__l10n']['i_supa_must_admin_password'] = 'Vous devez saisir un mot de passe pour le compte administrateur.';
$GLOBALS['__l10n']['i_supa_must_sudo_email'] = 'Vous devez saisir une adresse email pour le compte super-administrateur.';
$GLOBALS['__l10n']['i_supa_must_admin_email'] = 'Vous devez saisir une adresse email pour le compte administrateur.';

# configuration
$GLOBALS['__l10n']['i_config_title'] = 'Configuration de base';

# theme
$GLOBALS['__l10n']['i_theme_title'] = 'Choix du thème';

# colors
$GLOBALS['__l10n']['i_colors_title'] = 'Couleurs du thème';

# modules
$GLOBALS['__l10n']['i_modules_title'] = 'Installation des premiers modules';

# pages
$GLOBALS['__l10n']['i_pages_title'] = 'Créations des premieres pages';
$GLOBALS['__l10n']['i_pages_no_module_pages'] = 'Le module pages n’est pas installé, vous ne pouvez pas créer de page.';
$GLOBALS['__l10n']['i_pages_page_title_%s'] = 'Titre de la page %s';
$GLOBALS['__l10n']['i_pages_page_content_%s'] = 'Contenu de la page %s';
$GLOBALS['__l10n']['i_pages_page_home_%s'] = 'Définir la page %s comme page d’accueil';
$GLOBALS['__l10n']['i_pages_page_no_home'] = 'Pas de page d’accueil pour le moment';
$GLOBALS['__l10n']['i_pages_add_one_more'] = 'Ajouter une page de plus';
$GLOBALS['__l10n']['i_pages_first_home_title'] = 'Accueil';
$GLOBALS['__l10n']['i_pages_first_home_content'] = "Bienvenue sur notre nouveau site web.\n\nCe site web est en cours d’enrichissement, merci de revenir le consulter ultérieurement.";
$GLOBALS['__l10n']['i_pages_first_about_title'] = 'À propos';
$GLOBALS['__l10n']['i_pages_first_default_content'] = 'Ce site web est en cours d’enrichissement, merci de revenir le consulter ultérieurement.';


# merge config
$GLOBALS['__l10n']['i_merge_config_title'] = 'Fusion des données de configuration';
$GLOBALS['__l10n']['i_merge_config_done'] = 'Les données de configuration ont été fusionnées avec succès.';
$GLOBALS['__l10n']['i_merge_config_not'] = 'Les données de configuration n’ont pas été fusionnées.';

# end
$GLOBALS['__l10n']['i_end_install_title'] = 'This is the end... de l’installation';
$GLOBALS['__l10n']['i_end_update_title'] = 'This is the end... de la mise à jour';

$GLOBALS['__l10n']['i_end_install_congrat'] = 'Félicitations ! Vous avez correctement installé le système.';
$GLOBALS['__l10n']['i_end_update_congrat'] = 'Félicitations ! Vous avez correctement mis à jour le système.';

$GLOBALS['__l10n']['i_end_connect'] = 'Connectez-vous sur <a href="%s">l’interface d’administration</a> pour paramétrer le système.';

