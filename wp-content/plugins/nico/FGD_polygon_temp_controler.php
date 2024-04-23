<?php
/**
 * @package FGD osm map provider
*/


// __________________________________________________________________________________________________________________________
// Fonctions supplémentaires pour gérer les polygones

require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');


// Ajour un bouton pour gérer les polygones
function fgd_add_button_polygon_temps(){
    ob_start();
    ?>
    <div >
        <input type="radio" id="300" name="fgd_polygon_controller_temps" checked>
            <label for="5">5</label>
        </input>
        <input type="radio" id="600" name="fgd_polygon_controller_temps">
            <label for="10">10</label>
        </input>
        <input type="radio" id="1200" name="fgd_polygon_controller_temps">
            <label for="20">20</label>
        </input>
    </div>
    <div id="controller_temps_polygon"></div>
    <script>
        jQuery(document).ready(function($) {
            const polygon_button = document.querySelectorAll('input[name="fgd_polygon_controller_temps"]');

            polygon_button.forEach((button) => {
                button.addEventListener('change', function() {
                    FGD_change_polygons(this.id);
                });
            });

            function FGD_change_polygons($temps) {
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'fgd_change_polygon',
                        temps: $temps
                    },
                    success: function(response) {
                        $('#controller_polygon').html(response);
                        console.log("Polygones affichés");
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

add_action('wp_ajax_fgd_change_polygon', 'fgd_change_polygon'); // Pour les utilisateurs connectés

function fgd_change_polygon(){
    fgd_remove_all_polygon();
    fgd_draw_polygon(fgd_access_isochrones(),$_POST['temps']);
    wp_die();
}
