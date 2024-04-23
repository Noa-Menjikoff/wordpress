<?php
/**
 * @package FGD osm map provider
*/


// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires pour gérer les polygones

require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');

add_shortcode('FGD_button_polygon', 'fgd_add_button_polygon'); // Ajoute des boutons pour gérer l'affichage des polygones 

// Ajour un bouton pour gérer les polygones
function fgd_add_button_polygon(){
    ob_start();
    ?>
    <div >
        <input type="radio" id="fgd_show_polygon_button" name="fgd_polygon_controller" checked>
            <label for="fgd_show_polygon_button">Afficher les polygones</label>
        </input>
        <input type="radio" id="fgd_hide_polygon_button" name="fgd_polygon_controller">
            <label for="fgd_hide_polygon_button">Masquer les polygones</label>
        </input>
    </div>
    <div id="controller_polygon"></div>
    <script>
        jQuery(document).ready(function($) {
            const polygon_buttons = document.querySelectorAll('input[name="fgd_polygon_controller"]');

            polygon_buttons.forEach((button) => {
                button.addEventListener('change', function() {
                    if (this.id === 'fgd_show_polygon_button' && this.checked) {
                        FGD_show_polygons();
                    } else if (this.id === 'fgd_hide_polygon_button' && this.checked) {
                        FGD_hide_polygons();
                    }
                });
            });

            function FGD_show_polygons() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_show_polygon'
                    },
                    success: function(response) {
                        $('#controller_polygon').html(response);
                        console.log("Polygones affichés");
                    }
                });
            }

            function FGD_hide_polygons() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_hide_polygon'
                    },
                    success: function(response) {
                        console.log("Polygones masqués");
                        $('#controller_polygon').html(response);
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_fgd_show_polygon', 'fgd_show_polygon'); // Pour les utilisateurs connectés
add_action('wp_ajax_fgd_hide_polygon', 'fgd_hide_polygon'); // Pour les utilisateurs connectés


function fgd_show_polygon(){
    fgd_draw_polygon(fgd_access_isochrones(),"600");
    wp_die();
}

function fgd_hide_polygon(){
    fgd_remove_all_polygon();
    wp_die();
}
