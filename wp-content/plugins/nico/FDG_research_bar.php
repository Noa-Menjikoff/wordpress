<?php
/**
 * @package nico
*/
// __________________________________________________________________________________________________________________________
// Code barre de recherche fonctionnelle

require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');

add_shortcode('FDG_research_bar', 'fdg_add_research_bar'); // Ajouter une barre de recherche dynamique

// Ajouter une barre de recherche dynamique
function fdg_add_research_bar(){
    global $wpdb;
    ob_start();
    ?>
    <div id="fdg-research-container">
        <input type="text" id="fdg-search-input" placeholder="Rechercher un club...">
        <div id="fdg-suggestions"></div>
    </div>
    
    <script>
        jQuery(document).ready(function($) {
            // Fonction pour rechercher les clubs
            function FDG_search_clubs() { 
                var input = $('#fdg-search-input').val().trim();
                if (input.length >= 2) {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'fdg_search_clubs',
                            search_term: input
                        },
                        success: function(response) {
                            $('#fdg-suggestions').html(response);
                        }
                    });
                } else {
                    $('#fdg-suggestions').empty();
                }
            }

            // Lorsque l'utilisateur commence à saisir dans la zone de recherche
            $('#fdg-search-input').on('input', function() {
                FDG_search_clubs();
            });

            // Lorsque l'utilisateur clique sur une suggestion
            $(document).on('click', '.fdg-club-suggestion', function() {
                var clubName = $(this).text();
                $('#fdg-search-input').val(clubName);
                $('#fdg-suggestions').empty();
                FDG_search_clubs(); 
            });
        });
    </script>
    <?php
    return ob_get_clean();
}


add_action('wp_ajax_fdg_search_clubs', 'fdg_search_clubs'); // Pour les utilisateurs connectés

// Rechercher si on doit pointer le club ou afficher des suggestions
function fdg_search_clubs() {
    if (isset($_POST['search_term'])) {
        global $wpdb;
        $search_term = $_POST['search_term']; // Récupérer le terme de recherche
        $nomclub = $wpdb->get_row($wpdb->prepare("SELECT * FROM fdg_clubs WHERE nomclub = %s", $search_term)); // Récupérer les informations du club
        if ($nomclub) { 
            fdg_show_clubs($nomclub);  // Afficher les informations du club
        }
        else{
            fdg_suggestions($search_term); // Afficher des suggestions  
        }
    }
    wp_die();
}

// Pointe le club sur la carte
function fdg_show_clubs($nomclub) {
    $lat = $nomclub->lat;
    $lng = $nomclub->lng;
    echo "<script>map.setView([$lat, $lng], 14);</script>";
}

// Afficher des suggestions
function fdg_suggestions($search_term){
    global $wpdb;
    $clubs = $wpdb->get_col($wpdb->prepare("SELECT nomclub FROM fdg_clubs WHERE nomclub LIKE UPPER(%s) limit 5" , '%' . $search_term . '%')); // Récupérer les suggestions
    if ($clubs) {
        foreach ($clubs as $club) {
            echo '<div class="fdg-club-suggestion">' . $club . '</div>';
        }
    } else {
        echo '<div>Aucun résultat trouvé.</div>';
    }
}