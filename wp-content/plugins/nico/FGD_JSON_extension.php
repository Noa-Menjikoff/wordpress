<?php
/**
 * @package FGD osm map provider
*/

// __________________________________________________________________________________________________________________________
// Code JSON fonctionnel 

require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php'); // Pour les fonctions supplémentaires


// Ajouter un bouton pour importer un fichier JSON
function fgd_add_json_button(){
    ob_start();
    ?>
        <form id="FGD_form_json" method="post" enctype="multipart/form-data">
            <input type="file" name="json_file" accept=".json" title="IMPORTANT: Le fichier doit respecter pour chaque données: nomclub, numéro, nomsalle, adresse, cp, ville, pays, correspondant, mail_assistant">
            <button type="submit" name="submit_json">Importer JSON</button>
        </form>
    <?php
    return ob_get_clean();
}

// Vérifier si un fichier JSON a été soumis
if (isset($_POST['submit_json'])) {
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        $fgd_fichier = $_FILES['json_file']['tmp_name'];
        $handle = fopen($fgd_fichier, "r"); // r pour read = lecture du fichier JSON pour vérification des données
        if ($handle !== false) {
            $data = fread($handle, filesize($fgd_fichier)); // Lire et analyser les lignes suivantes
            fclose($handle);
            $fgd_donnees = json_decode($data, true); // Convertir les données JSON en tableau associatif
            foreach($fgd_donnees as $d){
                if($d['type'] == 'table'){
                    fgd_analyse_import_json($d['data']); // Analyser les données du fichier JSON et les insérer dans la base de données
                }
            }   
        } else {
            echo "<script>console.log(Erreur : Impossible d'ouvrir le fichier JSON.)</scirpt>";
        }
    } else {
        echo "<script>console.log(Erreur lors du téléchargement du fichier JSON.)</script>";
    }
}


// Analyser les données du fichier JSON et les insérer dans la base de données
function fgd_analyse_import_json($fgd_donnees){
    global $wpdb;
    $tablename = $wpdb->prefix . 'clubs';
    $tablenamebis = $wpdb->prefix . 'isochrones';  
    foreach($fgd_donnees as $data){
        $nom_du_club = $data['nomclub'];
        $numero = $data['numero'];
        $nom_de_la_salle = $data['nomsalle'];
        $adresse = $data['adresse'];
        $cp = $data['cp'];
        $ville = $data['ville'];
        $pays = $data['pays'];
        $correspondant = $data['correspondant'];
        $mail_correspondant = $data['mail_correspondant']; // Récupération des informations du club
        
        $id = $wpdb->get_var("SELECT MAX(id) FROM $tablename"); // Récupérer l'id maximum de la table fgdclubs
        if ($id == null) {
            $id = 0; // Si la table est vide, l'id est initialisé à 0
        }

        $clubExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tablename WHERE nomclub = %s", $nom_du_club));  // Vérifier si le club existe déjà dans la base de données
        if ($clubExists == 0) {
            
            $coordonnees = fgd_get_coordinates($adresse, $cp);
            $lat = $coordonnees[0];
            $lng = $coordonnees[1];

            $isochroneExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tablenamebis WHERE lat = %s AND lng = %s", $lat, $lng));  // Vérifier si l'isochrone existe déjà dans la base de données
            if ($isochroneExists == 0) {
                $isochrone5 = isochrone_calculation_by_time($lat, $lng, "300");
                $isochrone10 = isochrone_calculation_by_time($lat, $lng, "600");
                $isochrone20 = isochrone_calculation_by_time($lat, $lng, "1200");

                $insert_querybis5 = $wpdb->prepare("INSERT INTO $tablenamebis (lat, lng, temps, isochrone) VALUES (%s,%s,%s,%s)", $lat, $lng, "300", $isochrone5);
                $insert_querybis10 = $wpdb->prepare("INSERT INTO $tablenamebis (lat, lng, temps, isochrone) VALUES (%s,%s,%s,%s)", $lat, $lng, "600", $isochrone10);
                $insert_querybis20 = $wpdb->prepare("INSERT INTO $tablenamebis (lat, lng, temps, isochrone) VALUES (%s,%s,%s,%s)", $lat, $lng, "1200", $isochrone20);

                $wpdb->query($insert_querybis5); // Insérer les informations de l'isochrone dans la table fgdisochrones
                $wpdb->query($insert_querybis10); // Insérer les informations de l'isochrone dans la table fgdisochrones
                $wpdb->query($insert_querybis20); // Insérer les informations de l'isochrone dans la table fgdisochrones
            }
            $insert_query = $wpdb->prepare("INSERT INTO $tablename (id, nomclub, numero, nomsalle, adresse, cp, ville, pays, correspondant, mail_correspondant, lat, lng) VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", $id+1, $nom_du_club, $numero, $nom_de_la_salle, $adresse, $cp, $ville, $pays, $correspondant, $mail_correspondant, $lat, $lng);
            $wpdb->query($insert_query); // Insérer les informations du club dans la table fgdclubs
            
            $res = $wpdb->get_results("SELECT * FROM $tablename WHERE nomclub = '$nom_du_club' limit 1"); // On vérifie si le club a bien été inséré
            $res_isochrone = $wpdb->get_results("SELECT * FROM $tablenamebis WHERE lat = $lat AND lng = $lng"); // On vérifie si l'isochrone a bien été inséré
            if($res and $res_isochrone){
                echo "<script>console.log('Données sur $nom_du_club ajoutées avec succès.')</script>";
            }else{
                echo "<script>console.log('Données impossible à insérer sur $nom_du_club.')</script>";
                
            }
        }else{
            echo "<script>console.log('Données sur $nom_du_club déjà existante.')</script>";
        }
    }
}