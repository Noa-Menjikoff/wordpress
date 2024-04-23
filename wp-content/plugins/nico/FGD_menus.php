<?php

function mon_plugin_creer_menu_admin() {
    // Utilisez add_menu_page() pour créer le menu principal
    add_menu_page(
        'Titre du Menu',    // Titre affiché dans la barre de navigation
        'CREAWEB',      // Texte affiché dans le menu
        'manage_options',   // Capacité requise pour accéder au menu
        'slug_du_menu',     // Slug unique pour identifier le menu
        'callback_de_page', // Fonction de rappel pour afficher le contenu de la page
        'dashicons-admin-generic', // Icône du menu (facultatif)
        30                   // Position du menu dans la barre de navigation (facultatif)
    );
}
// Hook pour déclencher la création du menu lors de l'initialisation de l'administration
add_action('admin_menu', 'mon_plugin_creer_menu_admin');

// Fonction pour créer les sous-menus
function mon_plugin_creer_sous_menus() {
    // Ajoutez un sous-menu pour "Cartes"
    add_submenu_page(
        'slug_du_menu',     // Slug du menu parent
        'Gérer les Cartes', // Titre affiché dans la barre de navigation
        'Cartes',           // Texte affiché dans le sous-menu
        'manage_options',   // Capacité requise pour accéder au sous-menu
        'slug_sous_menu_cartes', // Slug unique pour identifier le sous-menu
        'callback_sous_menu_cartes' // Fonction de rappel pour afficher le contenu de la page du sous-menu
    );

    // Ajoutez un sous-menu pour "Point"
    add_submenu_page(
        'slug_du_menu',     // Slug du menu parent
        'Gérer les Points', // Titre affiché dans la barre de navigation
        'Points',           // Texte affiché dans le sous-menu
        'manage_options',   // Capacité requise pour accéder au sous-menu
        'slug_sous_menu_points', // Slug unique pour identifier le sous-menu
        'callback_sous_menu_points' // Fonction de rappel pour afficher le contenu de la page du sous-menu
    );

    // Ajoutez un sous-menu pour "Isochrone"
    add_submenu_page(
        'slug_du_menu',     // Slug du menu parent
        'Gérer les Isochrones', // Titre affiché dans la barre de navigation
        'Isochrones',       // Texte affiché dans le sous-menu
        'manage_options',   // Capacité requise pour accéder au sous-menu
        'slug_sous_menu_isochrone', // Slug unique pour identifier le sous-menu
        'callback_sous_menu_isochrone' // Fonction de rappel pour afficher le contenu de la page du sous-menu
    );
}
// Hook pour déclencher la création des sous-menus lors de l'initialisation de l'administration
add_action('admin_menu', 'mon_plugin_creer_sous_menus');

// Fonctions de rappel pour afficher le contenu des sous-menus
function callback_sous_menu_cartes() {
    // Contenu de la page "Cartes"
    echo '<div class="wrap">';
    echo '<h2>Gérer les Cartes</h2>';
    echo '<p>Ceci est la page pour gérer les cartes.</p>';
    echo '</div>';
}

function callback_sous_menu_points() {
    // Contenu de la page "Point"
    echo '<div class="wrap">';
    echo '<h2>Gérer les Points</h2>';
    echo '<p>Ceci est la page pour gérer les points.</p>';
    echo '</div>';
}

function callback_sous_menu_isochrone() {
    // Contenu de la page "Isochrone"
    echo '<div class="wrap">';
    echo '<h2>Gérer les Isochrones</h2>';
    echo '<p>Ceci est la page pour gérer les isochrones.</p>';
    echo '</div>';
}
