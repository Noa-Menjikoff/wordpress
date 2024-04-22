<?php
/**
 * @package nico
*/

// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires pour gérer les marqueurs

require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');

add_shortcode('FDG_button_marker', 'fdg_add_button_marker'); // Ajoute des boutons pour gérer l'affichage des polygones 

// Ajour un bouton pour gérer les marqueurs
function fdg_add_button_marker(){
    ob_start();
    ?>
    <div >
        <input type="radio" id="fdg_show_marker_button" name="fdg_marker_controller" checked>
            <label for="fdg_show_marker_button">Afficher les marqueurs</label>
        </input>
        <input type="radio" id="fdg_hide_marker_button" name="fdg_marker_controller">
            <label for="fdg_hide_marker_button">Masquer les marqueurs</label>
        </input>
    </div>
    <div id="controller_marker"></div>
    <script>
        jQuery(document).ready(function($) {
            const marker_buttons = document.querySelectorAll('input[name="fdg_marker_controller"]');

            marker_buttons.forEach((button) => {
                button.addEventListener('change', function() {
                    if (this.id === 'fdg_show_marker_button' && this.checked) {
                        FDG_show_markers();
                    } else if (this.id === 'fdg_hide_marker_button' && this.checked) {
                        FDG_hide_markers();
                    }
                });
            });

            function FDG_show_markers() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_show_marker'
                    },
                    success: function(response) {
                        $('#controller_marker').html(response);
                        console.log("marqueurs affichés");
                    }
                });
            }

            function FDG_hide_markers() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_hide_marker'
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

add_action('wp_ajax_fdg_show_marker', 'fdg_show_marker'); // Pour les utilisateurs connectés
add_action('wp_ajax_fdg_hide_marker', 'fdg_hide_marker'); // Pour les utilisateurs connectés


function fdg_show_marker(){
    fdg_add_markers_automatically(fdg_access_db());
    wp_die();
}

function fdg_hide_marker(){
    fdg_remove_all_marker();
    wp_die();
}
