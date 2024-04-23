<?php
/**
 * @package FGD osm map provider
*/

// __________________________________________________________________________________________________________________________
// Code CSV fonctionnel 

require_once(plugin_dir_path(__FILE__) . 'FGD_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FGD_additionnal_functions.php'); // Pour les fonctions supplémentaires

// Ajouter un bouton pour importer un fichier CSV
function fgd_add_csv_button(){
    ob_start();
    ?>
        <form id="FGD_form_csv" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" title="le fichier doit respecter pour chaque données: id, nom de club, numéro, nom de la salle, adresse, code postal, ville, pays, correspondant, mail de ce dernier, latitude, longitude, et l'isochrone à 10min">
            <button type="submit" name="submit_csv">Importer CSV</button>
        </form>
    <?php
    return ob_get_clean();
}

// Vérifier si un fichier CSV a été soumis
if (isset($_POST['submit_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $fgd_fichier = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($fgd_fichier, "r"); // r pour read = lecture du fichier CSV pour vérification des données
        if ($handle !== false) {
            fgetcsv($handle); // Lire et ignorer la première ligne
            // Lire et analyser les lignes suivantes
            while (($fgd_donnees = fgetcsv($handle, 0, ',')) !== false) {
                fgd_analyse_import_csv($fgd_donnees);
            }
            fclose($handle);
        }
        else {
            echo "<script>console.log(Erreur : Impossible d'ouvrir le fichier CSV.)</scirpt>";
        }
    } else {
        echo "<script>console.log(Erreur lors du téléchargement du fichier CSV.)</script>";
    }
}

// Analyser les données du fichier CSV et les insérer dans la base de données
function fgd_analyse_import_csv($fgd_donnees){
    global $wpdb;
    $tablename = $wpdb->prefix . 'clubs';
    $tablenamebis = $wpdb->prefix . 'isochrones';
    for($i =0; $i < count($fgd_donnees); $i+=10){
        $nom_du_club = $fgd_donnees[$i];
        $numero = $fgd_donnees[$i+1];
        $nom_de_la_salle = $fgd_donnees[$i+2];
        $adresse = $fgd_donnees[$i+3];
        $cp = $fgd_donnees[$i+4];
        $ville = $fgd_donnees[$i+5];
        $pays = $fgd_donnees[$i+6];
        $correspondant = $fgd_donnees[$i+7];
        $mail_correspondant = $fgd_donnees[$i+8];
        
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
            } else {
                echo "<script>console.log('Données sur l\'isochrone déjà existantes.')</script>";
            } 
            $insert_query = $wpdb->prepare("INSERT INTO $tablename (id, nomclub, numero, nomsalle, adresse, cp, ville, pays, correspondant, mail_correspondant, lat, lng) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", $id+1, $nom_du_club, $numero, $nom_de_la_salle, $adresse, $cp, $ville, $pays, $correspondant, $mail_correspondant, $lat, $lng);
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