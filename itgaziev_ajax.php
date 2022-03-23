<?php

use ITGaziev\Classes\ITGaziev_YML;

function itgaziev_save_settings()
{
    ITGaziev_YML::saveSettings();
    echo 'success';
    die();
}
add_action( 'wp_ajax_itgaziev_save_settings', 'itgaziev_save_settings' );

function itgaziev_save_url()
{
    $option_name = 'url_prices' ;
    $value    = $_POST['url'];

    if ( get_option( $option_name ) != $value ) {
        update_option( $option_name, $value );
    }
    else {
        $deprecated = '';
        $autoload = 'no';
        add_option( $option_name, $value, $deprecated, $autoload );
    }
}
add_action( 'wp_ajax_itgaziev_save_url', 'itgaziev_save_url' );

function itgaziev_save_and_update_prods()
{
    ITGaziev\Classes\ITGaziev_Import::ajaxLoad();
}
add_action( 'wp_ajax_itgaziev_save_and_update_prods', 'itgaziev_save_and_update_prods' );