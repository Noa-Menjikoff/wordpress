<?php
/**
 * @package nico
*/

function import_scripts() {
    // Inclure Leaflet.js (la bibliothèque JavaScript utilisée pour OpenStreetMap)
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1');

    // Inclure le style CSS de Leaflet (pour la carte)
    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css', array(), '1.7.1');
}

// Action pour ajouter les scripts
add_action('wp_enqueue_scripts', 'import_scripts');
