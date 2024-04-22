<?php
/**
 * @package nico
*/

// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires non fonctionnelles

require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_CSV_extension.php'); // Pour l'extension CSV
require_once(plugin_dir_path(__FILE__) . 'FDG_JSON_extension.php'); // Pour l'extension JSON
require_once(plugin_dir_path(__FILE__) . 'FDG_research_bar.php'); // Pour la barre de recherche
require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_polygon_controller.php'); // Pour les fonctions supplémentaires
require_once(plugin_dir_path(__FILE__) . 'FDG_marker_controller.php'); // Pour les fonctions supplémentaires



add_shortcode('FDG_departement_buttons', 'fdg_add_departement_buttons'); // Ajouter les boutons des départements

// Ajoute les boutons des départements
function fdg_add_departement_buttons(){
    ob_start();
    ?>
    <div>
        <input type="radio" id="all" name="fdg_departement_controller" checked>
            <label for="fdg_all_button">Tous les départements</label>
        </input>
        <input type="radio" id="18" name="fdg_departement_controller">
            <label for="18">Cher</label>
        </input>
        <input type="radio" id="28" name="fdg_departement_controller">
            <label for="28">Eure-et-Loir</label>
        </input>
        <input type="radio" id="36" name="fdg_departement_controller">
            <label for="36">Indre</label>
        </input>
        <input type="radio" id="37" name="fdg_departement_controller">
            <label for="37">Indre-et-Loire</label>
        </input>
        <input type="radio" id="41" name="fdg_departement_controller">
            <label for="41">Loir-et-Cher</label>
        </input>
        <input type="radio" id="45" name="fdg_departement_controller">
            <label for="45">Loiret</label>
        </input>
    </div>
    <div id="controller_departement"></div>
    <script>
        jQuery(document).ready(function($){
            const departement_buttons = document.querySelectorAll('input[name="fdg_departement_controller"]');
            departement_buttons.forEach((button)=>{
                button.addEventListener('change', function(){
                    if (this.id === 'all' && this.checked){
                        FDG_show_all_departements();
                    }else{
                        FDG_show_departement(this.id);
                    }
                });
            });

            function FDG_show_all_departements(){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_show_all_departements'
                    },
                    success: function(response){
                        $('#controller_departement').html(response);
                    }
                });
            }

            function FDG_show_departement(departement){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_show_departement',
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


add_action('wp_ajax_fdg_show_all_departements', 'fdg_show_all_departements'); // Pour les utilisateurs connectés
add_action('wp_ajax_fdg_show_departement', 'fdg_show_departement'); // Pour les utilisateurs connectés

// Affiche les clubs du département passé en paramètre
function fdg_show_departement(){
    fdg_remove_all_polygon();
    fdg_remove_all_marker();
    if (isset($_POST['departement'])){
        global $wpdb;
        $departement = $_POST['departement'];
        $departements = $wpdb->get_results($wpdb->prepare("SELECT * FROM fdgclubs where cp LIKE %s", $departement . '%')); // Récupérer les informations des clubs du département
        fdg_add_unique_marker_and_polygon($departements);
        fdg_draw_polygon($departements);
        $fdg_lat_array = array();
        $fdg_lng_array = array();
        if (!$departements) {
            echo "<script>console.log('Aucun club trouvé dans le département $departement.')</script>";
        }
        else{
            foreach($departements as $result){
                $fdg_lat = $result->lat;
                $fdg_lng = $result->lng;
                $fdg_lat_array[] = $fdg_lat;
                $fdg_lng_array[] = $fdg_lng;
            }
            $fdg_lat_avg = array_sum($fdg_lat_array) / count($fdg_lat_array);
            $fdg_lng_avg = array_sum($fdg_lng_array) / count($fdg_lng_array);
            echo "<script>map.setView([$fdg_lat_avg, $fdg_lng_avg], 9);</script>"; // Centrer la carte sur le département
        }
    } else {
        echo "<script>console.log('Erreur lors de la récupération des départements.')</script>";
    }
}

// Affiche tous les clubs des départements
function fdg_show_all_departements(){
    fdg_remove_all_polygon();
    fdg_remove_all_marker();
    fdg_draw_polygon(fdg_access_db());
    fdg_add_markers_automatically(fdg_access_db());
    echo "<script>map.setView([47.6, 1.6], 8);</script>"; // Centrer la carte sur la France
}