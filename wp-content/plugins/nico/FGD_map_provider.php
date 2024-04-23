<?php
/**
 * @package nico
*/

// Importer les scripts de la bibliothèque Leaflet et les styles CSS
require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_CSV_extension.php'); // Pour l'extension CSV
require_once(plugin_dir_path(__FILE__) . 'FGD_JSON_extension.php'); // Pour l'extension JSON
require_once(plugin_dir_path(__FILE__) . 'FGD_research_bar.php'); // Pour la barre de recherche
require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_polygon_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_marker_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_isochrone_calculation.php'); // Pour les fonctions supplémentaires

// Ajout des shortcode pour afficher ce que l'on souhaite
add_shortcode('FGD_map_Centre', 'fgd_map_provider'); // Afficher la carte centrée sur la région Centre et les marqueurs automatiques
add_shortcode('FGD_isochrone_calculation', 'interface_isochrone_calculation');

// Affiche la map centré sur la région Centre, coordonnées 47.5, 1.7 zoom 8
function fgd_map_provider(){
    ob_start();
    ?>
    
    <div id="fgd_map_container">
        <div id="map" style="height: 700px;"></div>
    </div>
    <script>
        var map = L.map('map').setView([47.6, 1.6], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        
    </script>
    <?php
    fgd_add_markers_automatically(fgd_access_clubs()); // Ajouter les marqueurs automatiquement
    fgd_draw_polygon(fgd_access_isochrones(),"300"); // Dessiner les polygones
    return ob_get_clean();
}

// Récupère les informations de la base de données dans fgd_BD/clubs.sql
function fgd_access_clubs(){
    global $wpdb;
    $tablename = $wpdb->prefix . 'clubs'; // Récupérer le nom de la table
    $results = $wpdb->get_results("SELECT * FROM $tablename"); // Récupérer les informations de la table clubs
    return $results;
}

// Récupère les informations de la base de données pour les isochrones
function fgd_access_isochrones(){
    global $wpdb;
    $tablename = $wpdb->prefix . 'isochrones'; 
    $results = $wpdb->get_results("SELECT * FROM $tablename"); // Récupérer les informations de la table cwoclubs
    return $results;
}

// Dessine un polygone sur la carte avec en son centre le marqueur du club
function fgd_draw_polygon($results, $temps) {
    foreach ($results as $result) {
        if ($result->temps == $temps && isset($result->isochrone)) {
            $isochrone_decode = json_decode($result->isochrone);
            if (isset($isochrone_decode->geometry->coordinates[0])) {
                $coordinates = $isochrone_decode->geometry->coordinates[0]; // Récupération des coordonnées de l'isochrone
                $coordinates = array_map(function($coord) {
                    return array_reverse($coord);
                }, $coordinates);
                $polygon = json_encode($coordinates); // Convertir les coordonnées en JSON
                echo "<script>L.polygon($polygon, {color: 'grey', fillOpacity: 0.2}).addTo(map);</script>"; // Dessiner le polygone
            }
        }
    }
}

// Ajouter les marqueurs automatiquement avec la bd phpmyadmin
function fgd_add_markers_automatically($results){
    foreach ($results as $result) {
        try{
        $lat = $result->lat;
        $lng = $result->lng;
        $nomclub = $result->nomclub; // Récupération des informations du club

        // Ajouter un marqueur
        echo "<script>L.marker([$lat, $lng]).addTo(map).bindPopup('$nomclub');</script>";
        }
        catch(Exception $e){
            echo "<script>console.log('Erreur lors de l'ajout du marqueur.')</script>";
        }
    }
}

