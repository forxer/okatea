<?php

$GLOBALS['__l10n']['c_a_route_name_galleriesList'] = 'Liste des galeries';
$GLOBALS['__l10n']['c_a_route_desc_galleriesList'] = 'Liste des galeries du module galeries';
$GLOBALS['__l10n']['c_a_route_name_galleriesGallery'] = 'Galerie';
$GLOBALS['__l10n']['c_a_route_desc_galleriesGallery'] = 'Liste des éléments et des sous-galeries d’une galerie';
$GLOBALS['__l10n']['c_a_route_name_galleriesItem'] = 'Elément galerie';
$GLOBALS['__l10n']['c_a_route_desc_galleriesItem'] = 'Détails d’un élément d’une galerie';

$GLOBALS['__l10n']['m_galleries_menu_add_gallery'] = 'Ajouter une galerie';
$GLOBALS['__l10n']['m_galleries_menu_add_item'] = 'Ajouter un élément';
$GLOBALS['__l10n']['m_galleries_menu_add_items'] = 'Ajouter plusieurs éléments';
$GLOBALS['__l10n']['m_galleries_menu_add_zip'] = 'Envoyer fichier zip';

$GLOBALS['__l10n']['m_galleries_error_gallery_%s_doesnt_exist'] = 'La galerie #%s n’existe pas.';
$GLOBALS['__l10n']['m_galleries_error_item_%s_doesnt_exist'] = 'L’élément #%s n’existe pas.';
$GLOBALS['__l10n']['m_galleries_error_gallery_in_children'] = 'Vous ne pouvez pas mettre une galerie dans ses propres enfants.';
$GLOBALS['__l10n']['m_galleries_error_parent_gallery_hidden'] = 'La galerie parent est masquée, vous devez la rendre visible avant de le faire pour celle-ci.';
$GLOBALS['__l10n']['m_galleries_error_you_must_enter_title'] = 'Vous devez saisir un titre.';
$GLOBALS['__l10n']['m_galleries_error_you_must_enter_title_in_%s'] = 'Vous devez saisir un titre en %s.';
$GLOBALS['__l10n']['m_galleries_error_you_must_choose_gallery'] = 'Vous devez choisir une galerie.';

/*
$GLOBALS['__l10n']['m_galleries_add_from_zip'] = 'Envoyer un fichier zip';
$GLOBALS['__l10n']['m_galleries_add_gallery'] = 'Ajouter une galerie';
$GLOBALS['__l10n']['m_galleries_edit_gallery'] = 'Modifier une galerie';
$GLOBALS['__l10n']['m_galleries_gallery_not_found'] = 'La galerie est introuvable.';
$GLOBALS['__l10n']['m_galleries_enter_gallery_name'] = 'Veuillez entrer au moins {0} caractères.';
$GLOBALS['__l10n']['m_galleries_item'] = 'un élément';
$GLOBALS['__l10n']['m_galleries_items'] = 'éléments';
$GLOBALS['__l10n']['m_galleries_item_description'] = 'Item description';
$GLOBALS['__l10n']['m_galleries_details'] = 'Détails';
$GLOBALS['__l10n']['m_galleries_item_details'] = 'Détails de l’élément';
$GLOBALS['__l10n']['m_galleries_legend'] = 'Légende';
$GLOBALS['__l10n']['m_galleries_author'] = 'Auteur';
$GLOBALS['__l10n']['m_galleries_place'] = 'Lieu';
$GLOBALS['__l10n']['m_galleries_password'] = 'Mot de passe';
$GLOBALS['__l10n']['m_galleries_password_empty'] = 'Laissez vide pour ne pas utiliser de mot de passe';
$GLOBALS['__l10n']['m_galleries_content_management'] = 'Gestion du contenu';
$GLOBALS['__l10n']['m_galleries_protected_password'] = 'Protegée par un mot de passe';
$GLOBALS['__l10n']['m_galleries_images_delete_confirm'] = 'Etes-vous sûr de vouloir supprimer cette image ? Cette action est irréversible.';
$GLOBALS['__l10n']['m_galleries_item_delete_confirm'] = 'Etes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.';
$GLOBALS['__l10n']['m_galleries_gallery_delete_confirm'] = 'Etes-vous sûr de vouloir supprimer cette galerie ? Cette action est irréversible.';
$GLOBALS['__l10n']['m_galleries_edit_order_drag_drop'] = 'Vous pouvez modifier l’ordre des éléments en les glissants/déposant.';
$GLOBALS['__l10n']['m_galleries_action_selected_items'] = 'Action sur les éléments sélectionnés';
$GLOBALS['__l10n']['m_galleries_item_added_%s'] = 'Elément ajouté le <em>%s</em>';
$GLOBALS['__l10n']['m_galleries_item_last_edit_%s'] = 'Dernière modification le <em>%s</em>';
$GLOBALS['__l10n']['m_galleries_switch_visibility_%s'] = 'Basculer la visibilité de l’élement %s';
$GLOBALS['__l10n']['m_galleries_edit_%s'] = 'Modifier l’élément %s';
$GLOBALS['__l10n']['m_galleries_delete_%s'] = 'Supprimer l’élément %s';
$GLOBALS['__l10n']['m_galleries_title'] = 'Titre';
$GLOBALS['__l10n']['m_galleries_gallery'] = 'Gallerie';
$GLOBALS['__l10n']['m_galleries_list'] = 'Liste des galeries';
$GLOBALS['__l10n']['m_galleries_items_added'] = 'Les éléments ont été ajoutés.';
$GLOBALS['__l10n']['m_galleries_item_added'] = 'L’élement a été ajouté';
$GLOBALS['__l10n']['m_galleries_item_updated'] = 'L’élement a été mis à jour';
$GLOBALS['__l10n']['m_galleries_item_deleted'] = 'L’élément a été supprimé';
$GLOBALS['__l10n']['m_galleries_gallery_added'] = 'La galerie a été ajoutée.';
$GLOBALS['__l10n']['m_galleries_gallery_updated'] = 'La galerie a été  mise à jour.';
$GLOBALS['__l10n']['m_galleries_gallery_order_update'] = 'L’ordre des galeries a été  mis à jour.';
$GLOBALS['__l10n']['m_galleries_gallery_deleted'] = 'Galerie supprimée.';
$GLOBALS['__l10n']['m_galleries_do_not_supported'] = 'Votre navigateur ne supporte pas cette fonctionnalité, vous devriez le mettre à jour ou en changer...';
$GLOBALS['__l10n']['m_galleries_alternative_text'] = 'Texte alternatif de l’image';
$GLOBALS['__l10n']['m_galleries_delete_images'] = 'Supprimer cette image';
$GLOBALS['__l10n']['m_galleries_must_name'] = 'Vous devez saisir un nom.';
$GLOBALS['__l10n']['m_galleries_impossible_add_gallery'] = 'Impossible d’ajouter la galerie';
$GLOBALS['__l10n']['m_galleries_impossible_update_gallery'] = 'Impossible de mettre a jour la galerie';
$GLOBALS['__l10n']['m_galleries_title_tag_module '] = 'Elément titre de la page';
$GLOBALS['__l10n']['m_galleries_title_seo_module '] = 'Titre SEO';

$GLOBALS['__l10n']['m_galleries_file'] = 'Fichiers';
$GLOBALS['__l10n']['m_galleries_attached_files'] = 'Fichiers joints';
$GLOBALS['__l10n']['m_galleries_display_images'] = 'Affichage des images';
$GLOBALS['__l10n']['m_galleries_display_website'] = 'Affichage côté site';
$GLOBALS['__l10n']['m_galleries_expansion_images'] = 'Interface d’agrandissement des images';
$GLOBALS['__l10n']['m_galleries_no_interface_images'] = 'Il n’y a aucune interface d’affichage des images de disponible.';
$GLOBALS['__l10n']['m_galleries_choose_display'] = 'Choisissez l’interface d’affichage des images';
$GLOBALS['__l10n']['m_galleries_currently_used'] = 'Actuellement utilisé';
$GLOBALS['__l10n']['m_galleries_first_level'] = 'Premier niveau';
$GLOBALS['__l10n']['m_galleries_order_adjacent_sections'] = 'Ordre des galeries voisines';
$GLOBALS['__l10n']['m_galleries_image_gallery_list'] = 'Lorsque l’on clique sur une image de galerie sur la liste des galeries';
$GLOBALS['__l10n']['m_galleries_item_galleries'] = 'Lorsque l’on clique sur une image d’un élément d’une galerie';
$GLOBALS['__l10n']['m_galleries_enter_gallery'] = 'entrer dans la galerie';
$GLOBALS['__l10n']['m_galleries_extend_image_gallery'] = 'agrandir l’image de la galerie';
$GLOBALS['__l10n']['m_galleries_show_iem_details'] = 'voir les détails de l’élément';
$GLOBALS['__l10n']['m_galleries_extend_image_item'] = 'agrandir l’image de l’élément';
$GLOBALS['__l10n']['m_galleries_display_date'] = 'Afficher la date';
$GLOBALS['__l10n']['m_galleries_display_author'] = 'Afficher l’auteur';
$GLOBALS['__l10n']['m_galleries_website_view'] = 'Côté site';
$GLOBALS['__l10n']['m_galleries_enable_images_article'] = 'Activer les images sur les articles';
$GLOBALS['__l10n']['m_galleries_num_images'] = 'Nombre d’images';
$GLOBALS['__l10n']['m_galleries_thumbnails_width'] = 'Largeur miniatures';
$GLOBALS['__l10n']['m_galleries_thumbnails_height'] = 'Hauteur miniatures';
$GLOBALS['__l10n']['m_galleries_maximum_width'] = 'Largeur maximum';
$GLOBALS['__l10n']['m_galleries_maximum_height'] = 'Hauteur maximum';
$GLOBALS['__l10n']['m_galleries_disable_resize'] = 'Vous pouvez désactiver le redimensionnement des images en mettant la valeur 0 (zero';
$GLOBALS['__l10n']['m_galleries_images_resize'] = 'Redimensionnement des images';
$GLOBALS['__l10n']['m_galleries_type_resize'] = 'Type de redimensionnement';
$GLOBALS['__l10n']['m_galleries_watermark_image'] = 'Image filigrane';
$GLOBALS['__l10n']['m_galleries_watermark'] = 'Filigrane';
$GLOBALS['__l10n']['m_galleries_watermark_position'] = 'Position filigrane';
$GLOBALS['__l10n']['m_galleries_watermark_delete'] = 'Supprimer ce filigrane';
$GLOBALS['__l10n']['m_galleries_other_files'] = 'Autres fichiers';
$GLOBALS['__l10n']['m_galleries_enable_attached_files'] = 'Activer les fichiers joints';
$GLOBALS['__l10n']['m_galleries_num_attached_files'] = 'Nombre de fichiers joints';
$GLOBALS['__l10n']['m_galleries_extensions_list_allowed'] = 'Liste des extensions autorisées séparées par des virgules';
$GLOBALS['__l10n']['m_galleries_Parent'] = 'Parent';
$GLOBALS['__l10n']['m_galleries_Active'] = 'Active';
$GLOBALS['__l10n']['m_galleries_Locked'] = 'Verrouillée';
$GLOBALS['__l10n']['m_galleries_manage_elements_of_gallery_%s'] = 'Gérer les éléments de la galerie %s';
$GLOBALS['__l10n']['m_galleries_add_element_to_gallery_%s'] = 'Ajouter un élément à la galerie %s';
$GLOBALS['__l10n']['m_galleries_add_multiple_elements_to_gallery_%s'] = 'Ajouter plusieurs éléments à la galerie %s';
$GLOBALS['__l10n']['m_galleries_add_zip_to_gallery_%s'] = 'Ajouter plusieurs éléments depuis un fichier zip à la galerie %s';
$GLOBALS['__l10n']['m_galleries_modify_gallery_%s'] = 'Modifier la galerie %s';
$GLOBALS['__l10n']['m_galleries_update_or_change_browser'] = 'Votre navigateur ne supporte pas cette fonctionnalité, vous devriez le mettre à jour ou en changer...';
$GLOBALS['__l10n']['m_galleries_Images'] = 'Images';


$GLOBALS['__l10n']['m_galleries_Empty_URL'] = 'URL vide';

# Skitter
$GLOBALS['__l10n']['m_galleries_enable_skitter'] = 'Activer Skitter';
$GLOBALS['__l10n']['m_galleries_Activation'] = 'Activation';
$GLOBALS['__l10n']['m_galleries_Animations'] = 'Animations';
$GLOBALS['__l10n']['m_galleries_velocity'] = 'Velocité';
$GLOBALS['__l10n']['m_galleries_interval'] = 'Transition après (durée en ms)';
$GLOBALS['__l10n']['m_galleries_animation'] = 'Animation';
$GLOBALS['__l10n']['m_galleries_Navigation'] = 'Navigation';
$GLOBALS['__l10n']['m_galleries_Numbers'] = 'Nombres';
$GLOBALS['__l10n']['m_galleries_Dots'] = 'Points';
$GLOBALS['__l10n']['m_galleries_Thumbs'] = 'Miniatures';
$GLOBALS['__l10n']['m_galleries_Display_navigation_arrows'] = 'Afficher les flèches de navigation';
$GLOBALS['__l10n']['m_galleries_Hide_tools'] = 'Cacher les outils (affichés uniquement au survol)';
$GLOBALS['__l10n']['m_galleries_Colors_navigation'] = 'Couleurs des éléments de navigation';
$GLOBALS['__l10n']['m_galleries_Number_Out'] = 'Element';
$GLOBALS['__l10n']['m_galleries_Number_Over'] = 'Elément au survol';
$GLOBALS['__l10n']['m_galleries_Number_Active'] = 'Elément actif';
$GLOBALS['__l10n']['m_galleries_Background_color'] = 'Couleur du fond';
$GLOBALS['__l10n']['m_galleries_Text_color'] = 'Couleur du texte';
$GLOBALS['__l10n']['m_galleries_Label'] = 'Label';
$GLOBALS['__l10n']['m_galleries_Display_labels'] = 'Afficher les labels';

# Galleria
$GLOBALS['__l10n']['m_galleries_General'] = 'Général';
$GLOBALS['__l10n']['m_galleries_Show_info'] = 'Afficher les informations';
$GLOBALS['__l10n']['m_galleries_Show_counter'] = 'Afficher le compteur';
$GLOBALS['__l10n']['m_galleries_Autoplay'] = 'Temps entre chaque transition (laisser à 0 pour désactiver les transitions automatiques)';
$GLOBALS['__l10n']['m_galleries_Transition_speed'] = 'Durée de la transition';
$GLOBALS['__l10n']['m_galleries_Lightbox'] = 'Lightbox';
$GLOBALS['__l10n']['m_galleries_Enable_lightbox'] = 'Activer Lightbox';
$GLOBALS['__l10n']['m_galleries_Overlay_opacity'] = 'Opacité de l’overlay';
$GLOBALS['__l10n']['m_galleries_Overlay_Background'] = 'Couleur de l’overlay';
$GLOBALS['__l10n']['m_galleries_Dimensions'] = 'Dimensions';
$GLOBALS['__l10n']['m_galleries_Width'] = 'Largeur';
$GLOBALS['__l10n']['m_galleries_Height'] = 'Hauteur';

# Galleriffic
$GLOBALS['__l10n']['m_galleries_Auto_start'] = 'Lancement automatique';
$GLOBALS['__l10n']['m_galleries_Delay'] = 'Temps entre chaque transition';
$GLOBALS['__l10n']['m_galleries_Enable_keyboard_navigation'] = 'Activer la navigation au clavier';
$GLOBALS['__l10n']['m_galleries_Render_ss_controls'] = 'Afficher les boutons Jouer / Arrêter le diaporama';
$GLOBALS['__l10n']['m_galleries_Render_nav_controls'] = 'Afficher les boutons Photo suivante / précédente';
$GLOBALS['__l10n']['m_galleries_Enable_top_pager'] = 'Afficher la pagination en haut';
$GLOBALS['__l10n']['m_galleries_Enable_bottom_pager'] = 'Afficher la pagination en bas';
$GLOBALS['__l10n']['m_galleries_Num_thumbs'] = 'Nombre de miniatures par page';
$GLOBALS['__l10n']['m_galleries_Max_pages_to_show'] = 'Nombre maximum de page à afficher';
$GLOBALS['__l10n']['m_galleries_Galleriffic_warning'] = 'Attention : Il faut limiter la taille des images dans les options de redimensionnement. Des images trop grandes peuvent provoquer des problèmes d’alignement sur la partie publique.';


# Flexslider
$GLOBALS['__l10n']['m_galleries_animation'] = 'Type animation (glissement/effacement)';
$GLOBALS['__l10n']['m_galleries_slideDirection'] = 'Direction animation (Horizontale/Verticale)';
$GLOBALS['__l10n']['m_galleries_slideshow'] = 'Démarrage automatique';
$GLOBALS['__l10n']['m_galleries_slideshowSpeed'] = 'Vitesse du cycle (durée en ms)';
$GLOBALS['__l10n']['m_galleries_animationDuration'] = 'Vitesse du diaporama (durée en ms)';
$GLOBALS['__l10n']['m_galleries_directionNav'] = 'Affichage boutons "Précédent"/"Suivant"';
$GLOBALS['__l10n']['m_galleries_controlNav'] = 'Á activer avec le contrôle manuel';
$GLOBALS['__l10n']['m_galleries_keyboardNav'] = 'Navigation par touche clavier';
$GLOBALS['__l10n']['m_galleries_mousewheel'] = 'Navigation par la molette de la souris';
$GLOBALS['__l10n']['m_galleries_prevText'] = 'Réglage du texte du bouton "précédent"';
$GLOBALS['__l10n']['m_galleries_nextText'] = 'Réglage du texte du bouton "suivant"';
$GLOBALS['__l10n']['m_galleries_pausePlay'] = 'Afficher en bas du diaporama un texte dyamique "PAUSE"/"PLAY"';
$GLOBALS['__l10n']['m_galleries_playText'] = 'Réglage du texte dynamique "Play"';
$GLOBALS['__l10n']['m_galleries_randomize'] = ' Ordre aléatoire des slides';
$GLOBALS['__l10n']['m_galleries_slideToStart'] = 'Image de départ du diaporama (position 0, 1, 2, .. du tableau)';
$GLOBALS['__l10n']['m_galleries_animationLoop'] = 'Activation/Désactivation des boutons "Précédent"/"Suivant")';
$GLOBALS['__l10n']['m_galleries_pauseOnAction'] = 'Pause défilement si utilisation des boutons "Précédent"/"Suivant")';
$GLOBALS['__l10n']['m_galleries_pauseOnHover'] = 'Pause du défilement au survol du diaporama';

# BxSlider
$GLOBALS['__l10n']['m_bxslider_galleries_general'] = 'Général';
$GLOBALS['__l10n']['m_bxslider_galleries_mode'] = 'Type de transition entre les vues';
$GLOBALS['__l10n']['m_bxslider_galleries_speed'] = 'Durée de transitions entre les vues (durée en ms)';
$GLOBALS['__l10n']['m_bxslider_galleries_infiniteLoop'] = 'Afficher la première diapositive après la dernière';
$GLOBALS['__l10n']['m_bxslider_galleries_controls'] = 'Afficher les contrôles (précédent et suivant)';
$GLOBALS['__l10n']['m_bxslider_galleries_startingSlide'] = 'Image de départ spécifiée (0,1,2...)';
$GLOBALS['__l10n']['m_bxslider_galleries_randomStart'] = 'Choix aléatoire des images';
$GLOBALS['__l10n']['m_bxslider_galleries_hideControlOnEnd'] = 'Bouton suivant plus actif à sur la dernière image';
$GLOBALS['__l10n']['m_bxslider_galleries_captions'] = 'Afficher les légendes des images (lit attribut title)';
$GLOBALS['__l10n']['m_bxslider_galleries_easing'] = 'Effet easing';
$GLOBALS['__l10n']['m_bxslider_galleries_Auto'] = 'Auto';
$GLOBALS['__l10n']['m_bxslider_galleries_auto'] = 'Transition automatique';
$GLOBALS['__l10n']['m_bxslider_galleries_pause'] = 'Pause entre chaque transition de diapositive (durée en ms)';
$GLOBALS['__l10n']['m_bxslider_galleries_autoControls'] = 'Afficher dynamiquement départ et arrêt';
$GLOBALS['__l10n']['m_bxslider_galleries_autoDelay'] = 'Temps avant le lancement du diaporama (durée en ms)';
$GLOBALS['__l10n']['m_bxslider_galleries_autoDirection '] = 'Sens du glissement du diaporama';
$GLOBALS['__l10n']['m_bxslider_galleries_autoHover'] = 'Pause au survol de la souris sur le diaporama';
$GLOBALS['__l10n']['m_bxslider_galleries_autoStart'] = 'Démarrage automatique';
$GLOBALS['__l10n']['m_bxslider_galleries_autoDirection'] = 'Sens de direction au lancement du diaporama';
$GLOBALS['__l10n']['m_bxslider_galleries_Page'] = 'Page';
$GLOBALS['__l10n']['m_bxslider_galleries_pager'] = 'Afficher n° des pages';
$GLOBALS['__l10n']['m_bxslider_galleries_pagerType'] = 'Afficher le numéro des pages (si option court validée = 1/4)';
$GLOBALS['__l10n']['m_bxslider_galleries_pagerLocation'] = 'Positionnement des numéros des pages';
$GLOBALS['__l10n']['m_bxslider_galleries_pagerShortSeparator'] = 'Caractères utilisés pour option «court» entre les numéros des pages (Ex: / = 1/4)';
$GLOBALS['__l10n']['m_bxslider_galleries_Multipledisplay'] = 'Affichage multiple';
$GLOBALS['__l10n']['m_bxslider_galleries_displaySlideQty'] = 'Nombre de diapositives à afficher à la fois';
$GLOBALS['__l10n']['m_bxslider_galleries_moveSlideQty'] = 'Nombre de diapositives à déplacer à la fois';
$GLOBALS['__l10n']['m_bxslider_galleries_TickerOption'] = 'Défilement permanent';
$GLOBALS['__l10n']['m_bxslider_galleries_ticker'] = 'Défilement permanent (télescripteur)';
$GLOBALS['__l10n']['m_bxslider_galleries_tickerSpeed'] = 'Vitesse du défilement - valeur comprise entre 1 et 5000';
$GLOBALS['__l10n']['m_bxslider_galleries_tickerDirection'] = 'Sens de direction du défilement';
$GLOBALS['__l10n']['m_bxslider_galleries_tickerHover'] = 'Défilement en pause au survol de la souris';


*/

