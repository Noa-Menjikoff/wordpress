<?php
/**
 * @package nico
*/

// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires pour gérer les marqueurs

require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');

add_shortcode('FGD_button_marker', 'fgd_add_button_marker'); // Ajoute des boutons pour gérer l'affichage des polygones 

// Ajour un bouton pour gérer les marqueurs
function fgd_add_button_marker(){
    ob_start();
    ?>
    <div >
        <input type="radio" id="fgd_show_marker_button" name="fgd_marker_controller" checked>
            <label for="fgd_show_marker_button">Afficher les marqueurs</label>
        </input>
        <input type="radio" id="fgd_hide_marker_button" name="fgd_marker_controller">
            <label for="fgd_hide_marker_button">Masquer les marqueurs</label>
        </input>
    </div>
    <div id="controller_marker"></div>
    <script>
        jQuery(document).ready(function($) {
            const marker_buttons = document.querySelectorAll('input[name="fgd_marker_controller"]');

            marker_buttons.forEach((button) => {
                button.addEventListener('change', function() {
                    if (this.id === 'fgd_show_marker_button' && this.checked) {
                        FGD_show_markers();
                    } else if (this.id === 'fgd_hide_marker_button' && this.checked) {
                        FGD_hide_markers();
                    }
                });
            });

            function FGD_show_markers() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_show_marker'
                    },
                    success: function(response) {
                        $('#controller_marker').html(response);
                        console.log("marqueurs affichés");
                    }
                });
            }

            function FGD_hide_markers() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_hide_marker'
                    },
                    success: function(response) {
                        console.log("Marqueurs masqués");
                        $('#controller_marker').html(response);
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_fgd_show_marker', 'fgd_show_marker'); // Pour les utilisateurs connectés
add_action('wp_ajax_fgd_hide_marker', 'fgd_hide_marker'); // Pour les utilisateurs connectés


function fgd_show_marker(){
    fgd_add_markers_automatically(fgd_access_db());
    wp_die();
}

function fgd_hide_marker(){
    fgd_remove_all_marker();
    wp_die();
}
