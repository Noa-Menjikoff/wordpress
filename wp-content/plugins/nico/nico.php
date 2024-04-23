<?php
/*
Plugin Name: le plugin de Noa
Plugin URI: http://www.lapagedeNoa.com/
Description: La description du super plugins
Author: Noa Menjikoff
Version: 0.0.1
Author URI: http://www.lapagedelauteur.com/
*/

require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_CSV_extension.php'); // Pour l'extension CSV
require_once(plugin_dir_path(__FILE__) . 'FGD_JSON_extension.php'); // Pour l'extension JSON
require_once(plugin_dir_path(__FILE__) . 'FGD_research_bar.php'); // Pour la barre de recherche
require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_polygon_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_marker_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_menus.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_polygon_temp_controler.php');

add_shortcode('FGD_button_polygon', 'fgd_add_button_polygon'); // Ajoute des boutons pour gérer l'affichage des polygones 
add_shortcode('FGD_button_polygon_temps', 'fgd_add_button_polygon_temps'); // Ajoute des boutons pour gérer l'affichage des polygones 


add_shortcode('FGD_map_Centre', 'fgd_map_provider'); // Afficher la carte centrée sur la région Centre et les marqueurs automatiques
add_shortcode('FGD_research_bar', 'fgd_add_research_bar'); // Ajouter une barre de recherche dynamique
add_shortcode('FGD_departement_buttons', 'fgd_add_departement_buttons'); // Ajouter les boutons des départements
add_shortcode('FGD_button_polygon', 'fgd_add_button_polygon'); // Ajoute des boutons pour gérer l'affichage des polygones 
add_shortcode('FGD_button_marker', 'fgd_add_button_marker'); // Ajoute des boutons pour gérer l'affichage des marqueurs 
add_shortcode('FGD_map_Marqueur_Add', 'fgd_add_markers_manually'); // Ajouter des repères manuellement
add_shortcode('FGD_json_button', 'fgd_add_json_button'); // Ajouter un bouton pour importer un fichier JSON
add_shortcode('FGD_csv_button', 'fgd_add_csv_button'); // Ajouter un bouton pour importer un fichier CSV


// register_activation_hook(__FILE__, 'mon_plugin_activation');

// register_deactivation_hook(__FILE__, 'mon_plugin_desactivation');

// function mon_plugin_activation() {
//     global $wpdb; 

//     $table_name = $wpdb->prefix . 'clubs'; 
//     $sql = "CREATE TABLE IF NOT EXISTS $table_name (
//         id INT(11) NOT NULL AUTO_INCREMENT,
//         column1 VARCHAR(100) NOT NULL,
//         column2 INT(11) NOT NULL,
//         PRIMARY KEY (id)
//     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";

//     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
//     dbDelta($sql);
// }

// function mon_plugin_desactivation() {
//     global $wpdb; 

//     $table_name = $wpdb->prefix . 'clubs'; 

//     $wpdb->query("DROP TABLE IF EXISTS $table_name");
// }