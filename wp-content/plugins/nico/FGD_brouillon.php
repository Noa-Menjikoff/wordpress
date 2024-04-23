<?php
/**
 * @package nico
*/

// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires non fonctionnelles

require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_CSV_extension.php'); // Pour l'extension CSV
require_once(plugin_dir_path(__FILE__) . 'FGD_JSON_extension.php'); // Pour l'extension JSON
require_once(plugin_dir_path(__FILE__) . 'FGD_research_bar.php'); // Pour la barre de recherche
require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_polygon_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FGD_marker_controller.php'); // Pour les fonctions supplémentaires



add_shortcode('FGD_departement_buttons', 'fgd_add_departement_buttons'); // Ajouter les boutons des départements

// Ajoute les boutons des départements
function fgd_add_departement_buttons(){
    ob_start();
    ?>
    <div>
        <input type="radio" id="all" name="fgd_departement_controller" checked>
            <label for="fgd_all_button">Tous les départements</label>
        </input>
        <input type="radio" id="18" name="fgd_departement_controller">
            <label for="18">Cher</label>
        </input>
        <input type="radio" id="28" name="fgd_departement_controller">
            <label for="28">Eure-et-Loir</label>
        </input>
        <input type="radio" id="36" name="fgd_departement_controller">
            <label for="36">Indre</label>
        </input>
        <input type="radio" id="37" name="fgd_departement_controller">
            <label for="37">Indre-et-Loire</label>
        </input>
        <input type="radio" id="41" name="fgd_departement_controller">
            <label for="41">Loir-et-Cher</label>
        </input>
        <input type="radio" id="45" name="fgd_departement_controller">
            <label for="45">Loiret</label>
        </input>
    </div>
    <div id="controller_departement"></div>
    <script>
        jQuery(document).ready(function($){
            const departement_buttons = document.querySelectorAll('input[name="fgd_departement_controller"]');
            departement_buttons.forEach((button)=>{
                button.addEventListener('change', function(){
                    if (this.id === 'all' && this.checked){
                        FGD_show_all_departements();
                    }else{
                        FGD_show_departement(this.id);
                    }
                });
            });

            function FGD_show_all_departements(){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_show_all_departements'
                    },
                    success: function(response){
                        $('#controller_departement').html(response);
                    }
                });
            }

            function FGD_show_departement(departement){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_show_departement',
                        departement: departement
                    },
                    success: function(response){
                        $('#controller_departement').html(response);
                    }
                });
            }
    })
    </script>
    <?php
    return ob_get_clean();
}


add_action('wp_ajax_fgd_show_all_departements', 'fgd_show_all_departements'); // Pour les utilisateurs connectés
add_action('wp_ajax_fgd_show_departement', 'fgd_show_departement'); // Pour les utilisateurs connectés

// Affiche les clubs du département passé en paramètre
function fgd_show_departement(){
    fgd_remove_all_polygon();
    fgd_remove_all_marker();
    if (isset($_POST['departement'])){
        global $wpdb;
        $departement = $_POST['departement'];
        $departements = $wpdb->get_results($wpdb->prepare("SELECT * FROM fgdclubs where cp LIKE %s", $departement . '%')); // Récupérer les informations des clubs du département
        fgd_add_unique_marker_and_polygon($departements);
        fgd_draw_polygon($departements);
        $fgd_lat_array = array();
        $fgd_lng_array = array();
        if (!$departements) {
            echo "<script>console.log('Aucun club trouvé dans le département $departement.')</script>";
        }
        else{
            foreach($departements as $result){
                $fgd_lat = $result->lat;
                $fgd_lng = $result->lng;
                $fgd_lat_array[] = $fgd_lat;
                $fgd_lng_array[] = $fgd_lng;
            }
            $fgd_lat_avg = array_sum($fgd_lat_array) / count($fgd_lat_array);
            $fgd_lng_avg = array_sum($fgd_lng_array) / count($fgd_lng_array);
            echo "<script>map.setView([$fgd_lat_avg, $fgd_lng_avg], 9);</script>"; // Centrer la carte sur le département
        }
    } else {
        echo "<script>console.log('Erreur lors de la récupération des départements.')</script>";
    }
}

// Affiche tous les clubs des départements
function fgd_show_all_departements(){
    fgd_remove_all_polygon();
    fgd_remove_all_marker();
    fgd_draw_polygon(fgd_access_db());
    fgd_add_markers_automatically(fgd_access_db());
    echo "<script>map.setView([47.6, 1.6], 8);</script>"; // Centrer la carte sur la France
}