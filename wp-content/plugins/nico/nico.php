<?php
/*
Plugin Name: le plugin de Noa
Plugin URI: http://www.lapagedeNoa.com/
Description: La description du super plugins
Author: Noa Menjikoff
Version: 0.0.1
Author URI: http://www.lapagedelauteur.com/
*/

require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_CSV_extension.php'); // Pour l'extension CSV
require_once(plugin_dir_path(__FILE__) . 'FDG_JSON_extension.php'); // Pour l'extension JSON
require_once(plugin_dir_path(__FILE__) . 'FDG_research_bar.php'); // Pour la barre de recherche
require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_polygon_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_marker_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_menus.php'); // Pour les fonctions supplémentaires



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