<?php
/**
 * @package nico
*/

// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires fonctionnelles

require_once(plugin_dir_path(__FILE__) . 'FGD_brouillon.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');

add_shortcode('FGD_map_Marqueur_Add', 'fgd_add_markers_manually'); // Ajouter des repères manuellement

// Ajouter un marqueur manuellement en rentrant les informations dans un formulaire
function fgd_add_markers_manually(){
    ob_start();
    ?>
    <div>
    <form id="FGD_form_add_marker">
            <label for="latitude">Latitude:</label>
            <input type="text" id="latitude" name="latitude"><br>

            <label for="longitude">Longitude:</label>
            <input type="text" id="longitude" name="longitude"><br>

            <label for="nom_club">Nom du club:</label>
            <input type="text" id="nom_club" name="nom_club"><br>

            <button type="button" id="fgd_add_button">Ajouter un club</button>
        </form>
    </div>
    <script>
        document.getElementById('add-marker-button').addEventListener('click', function() {
            var fgd_latitude = document.getElementById('latitude').value;
            var fgd_longitude = document.getElementById('longitude').value;
            var fgd_name = document.getElementById('marker-name').value; // Récupération des informations du marqueur
            L.marker([fgd_latitude, fgd_longitude]).addTo(map).bindPopup(fgd_name); // Ajouter un marqueur
            map.setView([fgd_latitude, fgd_longitude], 8); // Centrer la carte sur le nouveau marqueur
        });
    </script>
    <?php
    return ob_get_clean();
}


// Place un marqueur et un polygone pour chaque club passé en paramètre
function fgd_add_unique_marker_and_polygon($data){
    foreach($data as $result){
        $fgd_lat = $result->lat;
        $fgd_lng = $result->lng;
        $nomclub = $result->nomclub; // Récupération des informations du club
        $isochrone_decode = json_decode($result->isochrone10);
        $fgd_coordinates = $isochrone_decode->features[0]->geometry->coordinates[0]; // Récupération des coordonnées de l'isochrone
        $fgd_coordinates = array_map(function($coord) {    
            return array_reverse($coord); // Inversion des coordonnées pour correspondre au format de Leaflet
        }, $fgd_coordinates);
        echo "<script>
            L.marker([$fgd_lat, $fgd_lng]).addTo(map).bindPopup('$nomclub'); 
        </script>";
        
    }
       
}

// Supprime tous les polygones de la carte
function fgd_remove_all_polygon(){
    echo "<script>
        map.eachLayer(function (layer) {
            if (layer instanceof L.Polygon) {
                map.removeLayer(layer);
            }
        });
    </script>";
}

// Supprime tous les marqueurs de la carte
function fgd_remove_all_marker(){
    echo "<script>
        map.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });
    </script>";
}
