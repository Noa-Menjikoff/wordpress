<?php
/**
 * @package nico
*/
// __________________________________________________________________________________________________________________________
// Code CSV fonctionnel 

require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php'); // Pour les fonctions supplémentaires


add_shortcode('FDG_csv_button', 'fdg_add_csv_button'); // Ajouter un bouton pour importer un fichier CSV

// Ajouter un bouton pour importer un fichier CSV
function fdg_add_csv_button(){
    ob_start();
    ?>
        <form id="FDG_form_csv" method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" title="le fichier doit respecter pour chaque données: id, nom de club, numéro, nom de la salle, adresse, code postal, ville, pays, correspondant, mail de ce dernier, latitude, longitude, et l'isochrone à 10min">
            <button type="submit" name="submit_csv">Importer CSV</button>
        </form>
    <?php
    return ob_get_clean();
}

// Vérifier si un fichier CSV a été soumis
if (isset($_POST['submit_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $fdg_fichier = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($fdg_fichier, "r"); // r pour read = lecture du fichier CSV pour vérification des données
        if ($handle !== false) {
            fgetcsv($handle); // Lire et ignorer la première ligne
            // Lire et analyser les lignes suivantes
            while (($fdg_donnees = fgetcsv($handle, 0, ',')) !== false) {
                fdg_analyse_import_csv($fdg_donnees);
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
function fdg_analyse_import_csv($fdg_donnees){
    global $wpdb;
    for($i =0; $i < count($fdg_donnees); $i+=13){
        $nom_du_club = $fdg_donnees[$i+1];
        $numero = $fdg_donnees[$i+2];
        $nom_de_la_salle = $fdg_donnees[$i+3];
        $adresse = $fdg_donnees[$i+4];
        $code_postal = $fdg_donnees[$i+5];
        $ville = $fdg_donnees[$i+6];
        $pays = $fdg_donnees[$i+7];
        $correspondant = $fdg_donnees[$i+8];
        $mail_correspondant = $fdg_donnees[$i+9];
        $latitude = $fdg_donnees[$i+10];
        $longitude = $fdg_donnees[$i+11];
        $isochrone = $fdg_donnees[$i+12];   // Récupération des informations du club
        $id = $wpdb->get_var("SELECT MAX(id) FROM fdgclubs"); // Récupérer l'id maximum de la table fdgclubs
        if ($id == null) {
            $id = 0; // Si la table est vide, initialiser l'id à 0
        } 
        $clubExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM fdgclubs WHERE nomclub = %s", $nom_du_club));  // Vérifier si le club existe déjà dans la base de données
        if ($clubExists == 0) {
            // Insérer les informations du club dans la table fdgclubs
            $insert_query = $wpdb->prepare("INSERT INTO fdgclubs (id, nomclub, numero, nomsalle, adresse, cp, ville, pays, correspondant, mail_correspondant, lat, lng, isochrone10) VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", $id+1, $nom_du_club, $numero, $nom_de_la_salle, $adresse, $code_postal, $ville, $pays, $correspondant, $mail_correspondant, $latitude, $longitude, $isochrone);
            $wpdb->query($insert_query); // Exécuter la requête
            $res = $wpdb->get_results("SELECT * FROM fdgclubs WHERE nomclub = '$nom_du_club' limit 1"); // On regarde si le club inséré l'a été correctement
            if($res){
                echo "<script>console.log('Ajout des données sur $nom_du_club.');</script>";
            }else{
                echo "<script>console.log('Données impossible à insérer sur $nom_du_club.')</script>";
                $i += count($fdg_donnees); // On passe à la ligne suivante
            }
        }else{
            echo "<script>console.log('Données sur $nom_du_club déjà existante.')</script>";
        }
    }
}