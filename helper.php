<?php
function translit($s) {
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
    return $s; // возвращаем результат
}

//Attributes
function process_add_attribute($attribute) {
    global $wpdb;

    if (empty($attribute['attribute_type'])) {
        $attribute['attribute_type'] = 'select';
    }
    if (empty($attribute['attribute_orderby'])) {
        $attribute['attribute_orderby'] = 'menu_order';
    }
    if (empty($attribute['attribute_public'])) {
        $attribute['attribute_public'] = 0;
    }

    if (empty($attribute['attribute_name']) || empty($attribute['attribute_label'])) {
        return new WP_Error('error', __('Please, provide an attribute name and slug.', 'woocommerce'));
    }

    $wpdb->insert($wpdb->prefix.'woocommerce_attribute_taxonomies', $attribute);

    do_action('woocommerce_attribute_added', $wpdb->insert_id, $attribute);

    flush_rewrite_rules();
    delete_transient('wc_attribute_taxonomies');

    return true;
}

function valid_attribute_name( $attribute_name ) {
    if ( strlen( $attribute_name ) >= 28 ) {
        return new WP_Error( 'error', sprintf( __( 'Slug "%s" is too long (28 characters max). Shorten it, please.', 'woocommerce' ), sanitize_title( $attribute_name ) ) );
    } elseif ( wc_check_if_attribute_name_is_reserved( $attribute_name ) ) {
        return new WP_Error( 'error', sprintf( __( 'Slug "%s" is not allowed because it is a reserved term. Change it, please.', 'woocommerce' ), sanitize_title( $attribute_name ) ) );
    }
    return true;
}

function itgaziev_get_context()
{
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    return $arrContextOptions;
}

function console($what)
{
    echo "<pre>"; print_r($what); echo "</pre>";
}