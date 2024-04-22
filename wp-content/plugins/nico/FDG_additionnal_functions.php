<?php
/**
 * @package nico
*/

// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires fonctionnelles

require_once(plugin_dir_path(__FILE__) . 'FDG_brouillon.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');

add_shortcode('FDG_map_Marqueur_Add', 'fdg_add_markers_manually'); // Ajouter des repères manuellement

// Ajouter un marqueur manuellement en rentrant les informations dans un formulaire
function fdg_add_markers_manually(){
    ob_start();
    ?>
    <div>
    <form id="FDG_form_add_marker">
            <label for="latitude">Latitude:</label>
            <input type="text" id="latitude" name="latitude"><br>

            <label for="longitude">Longitude:</label>
            <input type="text" id="longitude" name="longitude"><br>

            <label for="nom_club">Nom du club:</label>
            <input type="text" id="nom_club" name="nom_club"><br>

            <button type="button" id="fdg_add_button">Ajouter un club</button>
        </form>
    </div>
    <script>
        document.getElementById('add-marker-button').addEventListener('click', function() {
            var fdg_latitude = document.getElementById('latitude').value;
            var fdg_longitude = document.getElementById('longitude').value;
            var fdg_name = document.getElementById('marker-name').value; // Récupération des informations du marqueur
            L.marker([fdg_latitude, fdg_longitude]).addTo(map).bindPopup(fdg_name); // Ajouter un marqueur
            map.setView([fdg_latitude, fdg_longitude], 8); // Centrer la carte sur le nouveau marqueur
        });
    </script>
    <?php
    return ob_get_clean();
}


// Place un marqueur et un polygone pour chaque club passé en paramètre
function fdg_add_unique_marker_and_polygon($data){
    foreach($data as $result){
        $fdg_lat = $result->lat;
        $fdg_lng = $result->lng;
        $nomclub = $result->nomclub; // Récupération des informations du club
        $isochrone_decode = json_decode($result->isochrone10);
        $fdg_coordinates = $isochrone_decode->features[0]->geometry->coordinates[0]; // Récupération des coordonnées de l'isochrone
        $fdg_coordinates = array_map(function($coord) {    
            return array_reverse($coord); // Inversion des coordonnées pour correspondre au format de Leaflet
        }, $fdg_coordinates);
        echo "<script>
            L.marker([$fdg_lat, $fdg_lng]).addTo(map).bindPopup('$nomclub'); 
        </script>";
        
    }
       
}

// Supprime tous les polygones de la carte
function fdg_remove_all_polygon(){
    echo "<script>
        map.eachLayer(function (layer) {
            if (layer instanceof L.Polygon) {
                map.removeLayer(layer);
            }
        });
    </script>";
}

// Supprime tous les marqueurs de la carte
function fdg_remove_all_marker(){
    echo "<script>
        map.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });
    </script>";
}
