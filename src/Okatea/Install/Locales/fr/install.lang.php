<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

# common
$GLOBALS['okt_l10n']['i_install_interface'] = 'Interface d’installation';
$GLOBALS['okt_l10n']['i_update_interface'] = 'Interface de mise à jour';
$GLOBALS['okt_l10n']['i_errors'] = 'Erreur(s)';

# steps
$GLOBALS['okt_l10n']['i_step_start'] = 'accueil';
$GLOBALS['okt_l10n']['i_step_checks'] = 'pré-requis';
$GLOBALS['okt_l10n']['i_step_db_conf'] = 'base de données';
$GLOBALS['okt_l10n']['i_step_db'] = 'tables';
$GLOBALS['okt_l10n']['i_step_supa'] = 'utilisateurs';
$GLOBALS['okt_l10n']['i_step_config'] = 'configuration';
$GLOBALS['okt_l10n']['i_step_log'] = 'enregistrement';
$GLOBALS['okt_l10n']['i_step_theme'] = 'thème';
$GLOBALS['okt_l10n']['i_step_colors'] = 'couleurs';
$GLOBALS['okt_l10n']['i_step_modules'] = 'modules';
$GLOBALS['okt_l10n']['i_step_pages'] = 'pages';
$GLOBALS['okt_l10n']['i_step_merge_config'] = 'fusion configuration';
$GLOBALS['okt_l10n']['i_step_end'] = 'fin';

# start
$GLOBALS['okt_l10n']['i_start_about_install'] = 'Vous êtes sur le point <strong>d’installer</strong> Okatea %s.';
$GLOBALS['okt_l10n']['i_start_about_update'] = 'Vous êtes sur le point <strong>de mettre à jour</strong> Okatea à la version <em>%s</em>.';

$GLOBALS['okt_l10n']['i_start_choose_lang'] = 'Vous pouvez choisir la langue de l’interface&nbsp;:';
$GLOBALS['okt_l10n']['i_start_click_next'] = 'Pour continuer veuillez cliquer sur le bouton "suivant" ci-dessous.';

# checks
$GLOBALS['okt_l10n']['i_checks_title'] = 'Vérification des pré-requis';
$GLOBALS['okt_l10n']['i_checks_warning'] = '<strong>Avertissement :</strong> le système de vérification à émis des alertes qui n’empêche pas le système de fonctionner mais il est possible que certaines fonctionnalités soient défaillantes.';
$GLOBALS['okt_l10n']['i_checks_big_loose'] = 'La configuration serveur présente des problèmes majeurs. Le système ne peut pas être installé sur ce serveur.';

# db conf
$GLOBALS['okt_l10n']['i_db_conf_title'] = 'Connexion à la base de données';
$GLOBALS['okt_l10n']['i_db_conf_ok'] = 'Connexion à la base de données réussie, fichier de connexion créé. Cliquez sur suivant pour créer les tables.';
$GLOBALS['okt_l10n']['i_db_conf_environement_choice'] = 'Tester la connexion sur l’environnement de :';
$GLOBALS['okt_l10n']['i_db_conf_environement_prod'] = 'production';
$GLOBALS['okt_l10n']['i_db_conf_environement_dev'] = 'développement';
$GLOBALS['okt_l10n']['i_db_conf_environement_note'] = 'Vous devez choisir l’environnement sur lequel vous êtes en train d’installer le système.';
$GLOBALS['okt_l10n']['i_db_conf_prod_server'] = 'Serveur de production';
$GLOBALS['okt_l10n']['i_db_conf_dev_server'] = 'Serveur de développement';
$GLOBALS['okt_l10n']['i_db_conf_db_host'] = 'Hôte de la base de données';
$GLOBALS['okt_l10n']['i_db_conf_db_name'] = 'Nom de la base de données';
$GLOBALS['okt_l10n']['i_db_conf_db_username'] = 'Nom d’utilisateur de la base de données';
$GLOBALS['okt_l10n']['i_db_conf_db_password'] = 'Mot de passe de la base de données';
$GLOBALS['okt_l10n']['i_db_conf_db_prefix'] = 'Préfixe des tables';
$GLOBALS['okt_l10n']['i_db_conf_db_error_prod_prefix_form'] = 'Le préfixe pour l’environnement de production n’est pas valide. Il ne peut contenir que des lettres et le caractère "_".';
$GLOBALS['okt_l10n']['i_db_conf_db_error_dev_prefix_form'] = 'Le préfixe pour l’environnement de développement n’est pas valide. Il ne peut contenir que des lettres et le caractère "_".';
$GLOBALS['okt_l10n']['i_db_conf_db_error_prod_must_prefix'] = 'Vous devez saisir un préfixe de base de données pour l’environnement de production.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_dev_must_prefix'] = 'Vous devez saisir un préfixe de base de données pour l’environnement de développement.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_prod_must_host'] = 'Vous devez saisir un hote de base de données pour l’environnement de production.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_dev_must_host'] = 'Vous devez saisir un hote de base de données pour l’environnement de développement.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_prod_must_name'] = 'Vous devez saisir un nom de base de données pour l’environnement de production.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_dev_must_name'] = 'Vous devez saisir un nom de base de données pour l’environnement de développement.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_prod_must_username'] = 'Vous devez saisir un nom d’utilisateur de base de données pour l’environnement de production.';
$GLOBALS['okt_l10n']['i_db_conf_db_error_dev_must_username'] = 'Vous devez saisir un nom d’utilisateur de base de données pour l’environnement de développement.';

# connexion
$GLOBALS['okt_l10n']['i_connexion_title'] = 'Connexion à la base de données';
$GLOBALS['okt_l10n']['i_connexion_success'] = 'Connexion à la base de données réussie. Cliquez sur suivant pour mettre à jours les tables.';

# db
$GLOBALS['okt_l10n']['i_db_title'] = 'Création des tables';
$GLOBALS['okt_l10n']['i_db_warning'] = '<strong>Avertissement :</strong> le système de vérification à émis des alertes mais cela ne devrait pas poser de problèmes.';
$GLOBALS['okt_l10n']['i_db_big_loose'] = 'Des erreurs bloquantes se sont produites, impossible de continuer l’installation.';

# supa
$GLOBALS['okt_l10n']['i_supa_title'] = 'Création des comptes administrateurs';
$GLOBALS['okt_l10n']['i_supa_account_sudo'] = 'Compte super-administrateur';
$GLOBALS['okt_l10n']['i_supa_account_sudo_note'] = 'Le compte super-administrateur est le compte qui a toutes les permissions. C’est vous :)';
$GLOBALS['okt_l10n']['i_supa_account_admin'] = 'Compte administrateur';
$GLOBALS['okt_l10n']['i_supa_account_admin_note'] = 'Le compte administrateur est un compte qui a des permissions par défaut, mais pas toutes. Il permet de donner un accès à l’administration du site mais pas à toutes les fonctionnalités. Utile par exemple pour laisser une autre personne administrer le site ou simplement avoir une interface épurée pour la gestion au quotidien. Ce compte est facultatif, il pourra être créé par la suite si besoin.';
$GLOBALS['okt_l10n']['i_supa_username'] = 'Nom d’utilisateur';
$GLOBALS['okt_l10n']['i_supa_password'] = 'Mot de passe';
$GLOBALS['okt_l10n']['i_supa_email'] = 'Adresse email';
$GLOBALS['okt_l10n']['i_supa_must_sudo_username'] = 'Vous devez saisir un nom d’utilisateur pour le compte super-administrateur.';
$GLOBALS['okt_l10n']['i_supa_must_sudo_password'] = 'Vous devez saisir un mot de passe pour le compte super-administrateur.';
$GLOBALS['okt_l10n']['i_supa_must_sudo_email'] = 'Vous devez saisir une adresse email pour le compte super-administrateur.';
$GLOBALS['okt_l10n']['i_supa_must_admin_info'] = 'Si vous souhaitez ajouter un compte administrateur, vous devez indiquer un nom d’utilisateur, un mot de passe et une adresse email.';

# configuration
$GLOBALS['okt_l10n']['i_config_title'] = 'Configuration de base';

# theme
$GLOBALS['okt_l10n']['i_theme_title'] = 'Choix du thème';

# colors
$GLOBALS['okt_l10n']['i_colors_title'] = 'Couleurs du thème';

# modules
$GLOBALS['okt_l10n']['i_modules_title'] = 'Installation des premiers modules';

# pages
$GLOBALS['okt_l10n']['i_pages_title'] = 'Créations des premieres pages';
$GLOBALS['okt_l10n']['i_pages_no_module_pages'] = 'Le module pages n’est pas installé, vous ne pouvez pas créer de page.';
$GLOBALS['okt_l10n']['i_pages_page_title_%s'] = 'Titre de la page %s';
$GLOBALS['okt_l10n']['i_pages_page_content_%s'] = 'Contenu de la page %s';
$GLOBALS['okt_l10n']['i_pages_page_home_%s'] = 'Définir la page %s comme page d’accueil';
$GLOBALS['okt_l10n']['i_pages_page_no_home'] = 'Pas de page d’accueil pour le moment';
$GLOBALS['okt_l10n']['i_pages_add_one_more'] = 'Ajouter une page de plus';
$GLOBALS['okt_l10n']['i_pages_first_home_title'] = 'Accueil';
$GLOBALS['okt_l10n']['i_pages_first_home_content'] = "Bienvenue sur notre nouveau site web.\n\nCe site web est en cours d’enrichissement, merci de revenir le consulter ultérieurement.";
$GLOBALS['okt_l10n']['i_pages_first_about_title'] = 'À propos';
$GLOBALS['okt_l10n']['i_pages_first_default_content'] = 'Ce site web est en cours d’enrichissement, merci de revenir le consulter ultérieurement.';


# merge config
$GLOBALS['okt_l10n']['i_merge_config_title'] = 'Fusion des données de configuration';
$GLOBALS['okt_l10n']['i_merge_config_done'] = 'Les données de configuration ont été fusionnées avec succès.';
$GLOBALS['okt_l10n']['i_merge_config_not'] = 'Les données de configuration n’ont pas été fusionnées.';

# end
$GLOBALS['okt_l10n']['i_end_install_title'] = 'This is the end... de l’installation';
$GLOBALS['okt_l10n']['i_end_update_title'] = 'This is the end... de la mise à jour';

$GLOBALS['okt_l10n']['i_end_install_congrat'] = 'Félicitations ! Vous avez correctement installé le système.';
$GLOBALS['okt_l10n']['i_end_update_congrat'] = 'Félicitations ! Vous avez correctement mis à jour le système.';

$GLOBALS['okt_l10n']['i_end_connect'] = 'Connectez-vous sur <a href="%s">l’interface d’administration</a> pour paramétrer le système.';

