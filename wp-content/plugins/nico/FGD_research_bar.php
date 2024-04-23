<?php
/**
 * @package nico
*/
// __________________________________________________________________________________________________________________________
// Code barre de recherche fonctionnelle

require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_isochrone_calculation.php'); // Pour les fonctions supplémentaires

add_shortcode('FGD_research_bar', 'fgd_add_research_bar'); // Ajouter une barre de recherche dynamique

// Ajouter une barre de recherche dynamique
function fgd_add_research_bar(){
    global $wpdb;
    ob_start();
    ?>
    <div id="fgd-research-container">
        <input type="text" id="fgd-search-input" placeholder="Rechercher un club...">
        <div id="fgd-suggestions"></div>
    </div>
    
    <script>
        jQuery(document).ready(function($) {
            // Fonction pour rechercher les clubs
            function FGD_search_clubs() { 
                var input = $('#fgd-search-input').val().trim();
                if (input.length >= 2) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'fgd_search_clubs',
                            search_term: input
                        },
                        success: function(response) {
                            $('#fgd-suggestions').html(response);
                        }
                    });
                } else {
                    $('#fgd-suggestions').empty();
                }
            }

            // Lorsque l'utilisateur commence à saisir dans la zone de recherche
            $('#fgd-search-input').on('input', function() {
                FGD_search_clubs();
            });

            // Lorsque l'utilisateur clique sur une suggestion
            $(document).on('click', '.fgd-club-suggestion', function() {
                var clubName = $(this).text();
                $('#fgd-search-input').val(clubName);
                $('#fgd-suggestions').empty();
                FGD_search_clubs(); 
            });
        });
    </script>
    <?php
    return ob_get_clean();
}


add_action('wp_ajax_fgd_search_clubs', 'fgd_search_clubs'); // Pour les utilisateurs connectés

// Rechercher si on doit pointer le club ou afficher des suggestions
function fgd_search_clubs() {
    if (isset($_POST['search_term'])) {
        global $wpdb;
        $search_term = $_POST['search_term']; // Récupérer le terme de recherche
        $nomclub = $wpdb->get_row($wpdb->prepare("SELECT * FROM fgd_clubs WHERE nomclub = %s", $search_term)); // Récupérer les informations du club
        if ($nomclub) { 
            fgd_show_clubs($nomclub);  // Afficher les informations du club
        }
        else{
            fgd_suggestions($search_term); // Afficher des suggestions  
        }
    }
    wp_die();
}

// Pointe le club sur la carte
function fgd_show_clubs($nomclub) {
    $lat = $nomclub->lat;
    $lng = $nomclub->lng;
    echo '<div class="fgd-club-suggestion">
        <h3>' . $nomclub->nomclub . '</h3>
        <p> Addresse : ' . $nomclub->adresse . '</p>
        <p> Ville : ' . $nomclub->ville . '</p>
        </div>';
    echo interface_isochrone_calculation($nomclub->nomclub);
    echo "<script>map.setView([$lat, $lng], 14);</script>";
}

// Afficher des suggestions
function fgd_suggestions($search_term){
    global $wpdb;
    
    $clubs = $wpdb->get_col($wpdb->prepare("SELECT nomclub FROM fgd_clubs WHERE nomclub LIKE UPPER(%s) limit 5" , '%' . $search_term . '%')); // Récupérer les suggestions
    if ($clubs) {
        foreach ($clubs as $club) {
            echo '<div class="fgd-club-suggestion">' . $club . '</div>';
        }
    } else {
        echo '<div>Aucun résultat trouvé.</div>';
    }
}