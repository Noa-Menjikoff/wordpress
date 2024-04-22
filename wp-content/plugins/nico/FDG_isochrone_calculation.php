<?php
/**
 * @package FDG osm map provider
*/

// __________________________________________________________________________________________________________________________
// Code calcul d'isochrone non fonctionnel

require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php'); // Pour les fonctions supplémentaires

add_shortcode('FDG_isochrone_calculation', 'interface_isochrone_calculation');

// Ajouter une interface pour calculer un isochrone
function interface_isochrone_calculation(){
    ob_start();
    ?>
    <form id="FDG_form_isochrone" method="post">
        <input type="number" id="FDG_time" name="FDG_time" placeholder="Temps en minutes">
        <button type="submit" name="submit_isochrone">Calculer l'isochrone</button>
    </form>
    <div id="controller_isochrone"></div>
    <script>
        jQuery(document).ready(function($){
            $('#FDG_form_isochrone').submit(function(e){
                e.preventDefault();
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_isochrone_calculation',
                        FDG_time: this.FDG_time.value
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

add_action('wp_ajax_fdg_isochrone_calculation', 'isochrone_calculation');

//Calculer l'isochrone  
function isochrone_calculation(){
    if (isset($_POST['FDG_time'])) {
        global $wpdb;
        $clubs = fdg_access_isochrones();
        for($i = 0; $i < count($clubs); $i++){
            if ($i > 300 && $i <= 330){
                $id = $clubs[$i]->id;
                $lat = $clubs[$i]->lat;
                $lng = $clubs[$i]->lng;
                $isochrone = $clubs[$i]->isochrone;
                $API_isochrone = "https://data.geopf.fr/navigation/isochrone?";
                $resource = "bdtopo-pgr";
                $profile = "car";
                $costType = "time";
                $costValue = $clubs[$i]->temps;
                $lat = $clubs[$i]->lat;
                $lng = $clubs[$i]->lng;
                $FDG_url_du_seigneur = $API_isochrone."resource=".$resource."&profile=".$profile."&costType=".$costType."&costValue=".$costValue."&point=".$lng.",".$lat."&geometryFormat=geojson";
                $isochrone = file_get_contents($FDG_url_du_seigneur);
                $id = $clubs[$i]->id;
                $replace = $wpdb->prepare("INSERT OR REPLACE INTO fdg_isochrones (id, temps, lat, lng, isochrone) VALUES (%d, %s, %s, %s, %s)", $id-639, $costValue, $lat, $lng, $isochrone);
                $wpdb->query($replace);
                $verif = $wpdb->get_row($wpdb->prepare("SELECT * FROM fdg_isochrones WHERE id = %d", $id-639));
                if ($verif){
                    echo "<script>console.log('$isochrone');</script>";
                }
                else{
                    echo "<script>console.log('Erreur lors de l'insertion de l'isochrone')</script>";
                }
            }
        }   
    }
    else{
        echo "<script>console.log('Erreur lors du calcul de l'isochrone')</script>";
    }   
}
