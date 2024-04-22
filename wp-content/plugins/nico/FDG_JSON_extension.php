<?php
/**
 * @package nico
*/
// __________________________________________________________________________________________________________________________
// Code JSON fonctionnel 

require_once(plugin_dir_path(__FILE__) . 'FDG_functions.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_map_provider.php');
require_once(plugin_dir_path(__FILE__) . 'FDG_additionnal_functions.php'); // Pour les fonctions supplémentaires


add_shortcode('FDG_json_button', 'fdg_add_json_button'); // Ajouter un bouton pour importer un fichier JSON

// Ajouter un bouton pour importer un fichier JSON
function fdg_add_json_button(){
    ob_start();
    ?>
        <form id="FDG_form_json" method="post" enctype="multipart/form-data">
            <input type="file" name="json_file" accept=".json" title="IMPORTANT: Le fichier doit respecter pour chaque données: id, nomclub, numéro, nomsalle, adresse, cp, ville, pays, correspondant, mail_assistant, lat, lng, isochrone10">
            <button type="submit" name="submit_json">Importer JSON</button>
        </form>
    <?php
    return ob_get_clean();
}

// Vérifier si un fichier JSON a été soumis
if (isset($_POST['submit_json'])) {
    if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
        $fdg_fichier = $_FILES['json_file']['tmp_name'];
        $handle = fopen($fdg_fichier, "r"); // r pour read = lecture du fichier JSON pour vérification des données
        if ($handle !== false) {
            $data = fread($handle, filesize($fdg_fichier)); // Lire et analyser les lignes suivantes
            fclose($handle);
            $fdg_donnees = json_decode($data, true); // Convertir les données JSON en tableau associatif
            foreach($fdg_donnees as $d){
                if($d['type'] == 'table'){
                    fdg_analyse_import_json($d['data']); // Analyser les données du fichier JSON et les insérer dans la base de données
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
function fdg_analyse_import_json($fdg_donnees){
    global $wpdb;
    foreach($fdg_donnees as $data){
        $nom_du_club = $data['nomclub'];
        $numero = $data['numero'];
        $nom_de_la_salle = $data['nomsalle'];
        $adresse = $data['adresse'];
        $code_postal = $data['cp'];
        $ville = $data['ville'];
        $pays = $data['pays'];
        $correspondant = $data['correspondant'];
        $mail_correspondant = $data['mail_correspondant'];
        $latitude = $data['lat'];
        $longitude = $data['lng'];
        $isochrone = $data['isochrone10']; // Récupération des informations du club
        $id = $wpdb->get_var("SELECT MAX(id) FROM fdgclubs"); // Récupérer l'id maximum de la table fdgclubs
        if ($id == null) {
            $id = 0; // Si la table est vide, l'id est initialisé à 0
        }
        $clubExists = $wpdb->get_var("SELECT COUNT(*) FROM fdgclubs WHERE nomclub = %s", $nom_du_club); // Vérifier si le club existe déjà dans la base de données
        if ($clubExists == 0) {
            $insert_query = $wpdb->prepare("INSERT INTO fdgclubs (id, nomclub, numero, nomsalle, adresse, cp, ville, pays, correspondant, mail_correspondant, lat, lng, isochrone10) 
            VALUES (%d,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", $id+1, $nom_du_club, $numero, $nom_de_la_salle, $adresse, $code_postal, $ville, $pays, $correspondant, $mail_correspondant, $latitude, $longitude, $isochrone);
            $wpdb->query($insert_query); // Insérer les informations du club dans la table fdgclubs
            $res = $wpdb->get_results("SELECT * FROM fdgclubs WHERE nomclub = '$nom_du_club' limit 1"); // On vérifie si le club a bien été inséré
            if($res){
                echo "<script>console.log('Données sur $nom_du_club ajoutées avec succès.')</script>";
            }else{
                echo "<script>console.log('Données impossible à insérer sur $nom_du_club.')</script>";
            }
        }else{
            echo "<script>console.log('Données sur $nom_du_club déjà existante.')</script>";
        }
    }
}