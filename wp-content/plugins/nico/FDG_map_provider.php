
<?php
/**
 * @package nico
*/
// Importer les scripts de la bibliothèque Leaflet et les styles CSS
require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_CSV_extension.php'); // Pour l'extension CSV
require_once(plugin_dir_path(__FILE__) . 'FDG_JSON_extension.php'); // Pour l'extension JSON
require_once(plugin_dir_path(__FILE__) . 'FDG_research_bar.php'); // Pour la barre de recherche
require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_polygon_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_marker_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_isochrone_calculation.php'); // Pour les fonctions supplémentaires

// Ajout des shortcode pour afficher ce que l'on souhaite
add_shortcode('FDG_map_Centre', 'fdg_map_provider'); // Afficher la carte centrée sur la région Centre et les marqueurs automatiques
add_shortcode('FDG_isochrone_calculation', 'interface_isochrone_calculation');


// Affiche la map centré sur la région Centre, coordonnées 47.5, 1.7 zoom 8
function fdg_map_provider(){
    ob_start();
    ?>
    <div id="fdg_map_container">
        <div id="map" style="height: 700px;"></div>
    </div>
    <script>
        var map = L.map('map').setView([47.6, 1.6], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);
    </script>
    <button><a href="https://www.geoportail.gouv.fr/carte">Calculer un isochrone</a></button>
    <?php
    fdg_add_markers_automatically(fdg_access_db()); // Ajouter les marqueurs automatiquement
    fdg_draw_polygon(fdg_access_db()); // Dessiner les polygones
    return ob_get_clean();
}


// Récupère les informations de la base de données dans fdg_BD/clubs.sql
function fdg_access_db(){
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM fdg_clubs"); // Récupérer les informations de la table fdgclubs
    return $results;
}

// Dessine un polygone sur la carte avec en son centre le marqueur du club
function fdg_draw_polygon($results) {
    foreach ($results as $result) {
        try{
        $isochrone_decode = json_decode($result->isochrone10); 
        $coordinates = $isochrone_decode->features[0]->geometry->coordinates[0]; // Récupération des coordonnées de l'isochrone
        $coordinates = array_map(function($coord) {
            return array_reverse($coord); // Inversion des coordonnées pour correspondre au format de Leaflet
        }, $coordinates);
        $polygonPoints = json_encode($coordinates);
        echo "<script>
        var polygon = L.polygon($polygonPoints, {color: 'grey', fillOpacity: 0.2}).addTo(map);
        </script>";
    }
    catch(Exception $e){
        echo "<script>console.log('Erreur lors de la création du polygone.')</script>";
    }
    }
}

// Ajouter les marqueurs automatiquement avec la bd phpmyadmin
function fdg_add_markers_automatically($results){
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