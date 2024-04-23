<?php
/**
 * @package FGD osm map provider
*/

// __________________________________________________________________________________________________________________________
// Code calcul d'isochrone non fonctionnel

require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php'); // Pour les fonctions supplémentaires

add_shortcode('FGD_isochrone_calculation', 'interface_isochrone_calculation');

// Ajouter une interface pour calculer un isochrone
function interface_isochrone_calculation($nomclub){
    ob_start();
    ?>
    <form id="FGD_form_isochrone" method="post">
        <input type="hidden" name="club_name" value="<?php echo esc_attr($nomclub); ?>">
        <input type="number" id="FGD_time" name="FGD_time" placeholder="Temps en minutes">
        <button type="submit" name="submit_isochrone">Calculer l'isochrone</button>
    </form>
    <div id="controller_isochrone"></div>
    <script>
        jQuery(document).ready(function($){
            $('#FGD_form_isochrone').submit(function(e){
                e.preventDefault();
                $FGD_time = this.FGD_time.value;
                $club_name = this.club_name.value;
                console.log($FGD_time);
                console.log($club_name);
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_isochrone_calculation',
                        FGD_time: $FGD_time,
                        club_name: $club_name
                    },
                    success: function(response){
                        $('#controller_isochrone').html(response);
                        $('#controller_isochrone').empty();
                        console.log('isochrone calculé');
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_fgd_isochrone_calculation', 'fgd_isochrone_calculation');

//Calculer l'isochrone
function fgd_isochrone_calculation(){

    // faut que tu files en paramètres le nom du club, que tu récupères normalement sur la barre de recherche
    if (isset($_POST['FGD_time'])) {
        global $wpdb;
        $tablename = $wpdb->prefix . 'clubs';
        $name = $_POST['club_name'];
        echo "<script>console.log('$name')</script>";
        $club = $wpdb->get_results("SELECT * FROM $tablename where nomclub = '$name'");
        if (empty($club)) {
            echo "<script>console.log('Club non trouvé')</script>";
            $type= gettype($name);
            echo "<script>console.log('$club')</script>";
        }
        else{
            echo "<script>console.log('Club trouvé')</script>";

        }
        $lat = $club[0]->lat;
        $lng = $club[0]->lng;
        $API_isochrone = "https://data.geopf.fr/navigation/isochrone?";
        $resource = "bdtopo-pgr";
        $profile = "car";
        $costType = "time";
        $costValue = $_POST['FGD_time'] * 60;
        echo "<script>console.log('$costValue')</script>";
        echo "<script>console.log('$lat')</script>";
        echo "<script>console.log('$lng')</script>";
        $CWO_url_du_seigneur = $API_isochrone."resource=".$resource."&profile=".$profile."&costType=".$costType."&costValue=".$costValue."&point=".$lng.",".$lat."&geometryFormat=geojson";
        echo "<script>console.log('$CWO_url_du_seigneur')</script>";
        $isochrone = file_get_contents($CWO_url_du_seigneur);
        $isochrone_decode = json_decode($isochrone);
        $coordinates = $isochrone_decode->geometry->coordinates[0]; // Récupération des coordonnées de l'isochrone
        $coordinates = array_map(function($coord) {
            return array_reverse($coord);
        }, $coordinates);
        $polygon = json_encode($coordinates); // Convertir les coordonnées en JSON
        fgd_remove_all_polygon();
        echo "<script>L.polygon($polygon, {color: 'red', fillOpacity: 0.2}).addTo(map);</script>"; // Dessiner le polygone
        echo "<script>map.setView([$lat, $lng], 15);</script>";

    }
    else{
        echo "<script>console.log('Erreur lors du calcul de l'isochrone')</script>";
    }
}