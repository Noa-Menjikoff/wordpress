<?php
/**
 * @package FDG osm map provider
*/


// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires pour gérer les polygones

require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');

add_shortcode('FDG_button_polygon', 'fdg_add_button_polygon'); // Ajoute des boutons pour gérer l'affichage des polygones 

// Ajour un bouton pour gérer les polygones
function fdg_add_button_polygon(){
    ob_start();
    ?>
    <div >
        <input type="radio" id="fdg_show_polygon_button" name="fdg_polygon_controller" checked>
            <label for="fdg_show_polygon_button">Afficher les polygones</label>
        </input>
        <input type="radio" id="fdg_hide_polygon_button" name="fdg_polygon_controller">
            <label for="fdg_hide_polygon_button">Masquer les polygones</label>
        </input>
    </div>
    <div id="controller_polygon"></div>
    <script>
        jQuery(document).ready(function($) {
            const polygon_buttons = document.querySelectorAll('input[name="fdg_polygon_controller"]');

            polygon_buttons.forEach((button) => {
                button.addEventListener('change', function() {
                    if (this.id === 'fdg_show_polygon_button' && this.checked) {
                        FDG_show_polygons();
                    } else if (this.id === 'fdg_hide_polygon_button' && this.checked) {
                        FDG_hide_polygons();
                    }
                });
            });

            function FDG_show_polygons() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_show_polygon'
                    },
                    success: function(response) {
                        $('#controller_polygon').html(response);
                        console.log("Polygones affichés");
                    }
                });
            }

            function FDG_hide_polygons() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fdg_hide_polygon'
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

add_action('wp_ajax_fdg_show_polygon', 'fdg_show_polygon'); // Pour les utilisateurs connectés
add_action('wp_ajax_fdg_hide_polygon', 'fdg_hide_polygon'); // Pour les utilisateurs connectés


function fdg_show_polygon(){
    fdg_draw_polygon(fdg_access_db());
    wp_die();
}

function fdg_hide_polygon(){
    fdg_remove_all_polygon();
    wp_die();
}
