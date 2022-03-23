<?php
add_action( 'woocommerce_product_options_pricing', 'wc_fast_price_product_field' );
function wc_fast_price_product_field() {
    woocommerce_wp_text_input( array( 'id' => 'price_opt', 'class' => 'wc_input_price short', 'label' => __( 'Опт. Цена', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
    woocommerce_wp_text_input( array( 'id' => 'price_mrc', 'class' => 'wc_input_price short', 'label' => __( 'МРЦ', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ) );
    woocommerce_wp_text_input( array( 'id' => 'external_url', 'class' => 'wc_input_price short', 'label' => __( 'Ссылка на товар', 'woocommerce' ) ) );
    woocommerce_wp_checkbox( array( 'id' => 'is_new', 'class' => 'show_if_simple', 'label' => __( 'Это новый товар', 'woocommerce' ) ) );
}

add_action( 'save_post', 'wc_fast_price_save_product' );
function wc_fast_price_save_product( $product_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( isset( $_POST['price_opt'] ) ) {
        if ( is_numeric( $_POST['price_opt'] ) ) update_post_meta( $product_id, 'price_opt', $_POST['price_opt'] );
    }
    else delete_post_meta( $product_id, 'price_opt' );
    if ( isset( $_POST['price_mrc'] ) ) {
        if ( is_numeric( $_POST['price_mrc'] ) ) update_post_meta( $product_id, 'price_mrc', $_POST['price_mrc'] );
    }
    else delete_post_meta( $product_id, 'price_mrc' );
    if ( isset( $_POST['external_url'] ) ) {
        if ( is_numeric( $_POST['external_url'] ) ) update_post_meta( $product_id, 'external_url', $_POST['external_url'] );
    }
    else delete_post_meta( $product_id, 'external_url' );
    if ( isset( $_POST['is_new'] ) ) {
        if ( $_POST['is_new'] ) update_post_meta( $product_id, 'is_new', $_POST['is_new'] );
    }
    else delete_post_meta( $product_id, 'is_new' );
}

function add_acf_columns ( $columns ) {
    $columns['price_opt'] = 'Опт. Цена';
    $columns['price_mrc'] = 'МРЦ';
    return $columns;
}
add_filter ( 'manage_product_posts_columns', 'add_acf_columns' );

add_action( 'manage_posts_custom_column', 'itgaziev_products_column_population', 10, 2 );
function itgaziev_products_column_population( $column_name, $post_id ) {
    switch( $column_name ) {
        case 'price_opt':
            echo '<div id="price_opt-' . $post_id . '">' . get_post_meta( $post_id, 'price_opt', true ) . '</div>';
            break;
        case 'price_mrc':
            echo '<div id="price_mrc-' . $post_id . '">' . get_post_meta( $post_id, 'price_mrc', true ) . '</div>';
            break;
        case 'is_new':
            echo '<div id="is_new-' . $post_id . '">' . get_post_meta( $post_id, 'is_new', true ) . '</div>';
            break;
    }
}

function get_price_all($product_id)
{
    $mrc_price = get_post_meta( $product_id, 'price_mrc', true );
    $opt_price = get_post_meta( $product_id, 'price_opt', true );
    return array('price_mrc' => $mrc_price, 'price_opt' => $opt_price);
}