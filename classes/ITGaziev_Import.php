<?php
namespace ITGaziev\Classes;


class ITGaziev_Import
{
    static $prefix = 'itgaziev_';
    static $index;
    static $arItem = [];
    static $categories = [];
    static $parent_cat;
    static $product;
    static $settings = [];

    public static function ajaxLoadDebug($index) {
        self::$index = $index;

        $arData = json_decode(file_get_contents(ITGAZIEV_DIR . '/cache/arData.json'), true);
        self::$categories = $arData['CATEGORIES'];

        if (isset($arData['ITEMS'][self::$index ])) {
            self::$settings = ITGaziev_YML::getSettings();
            self::$arItem = $arData['ITEMS'][self::$index ];
            self::$parent_cat = self::$categories[self::$arItem['CATEGORY_ID']];
            self::$arItem['CATS'] = self::get_cat(self::$categories, self::$arItem['CATEGORY_ID']);

            self::$product = ITGaziev_DB::get_post_ext('product_' . self::$arItem['ID']);

            $message = (self::$product) ? self::updateProduct() : self::createProduct();

            echo $message;

        } else {
            echo self::errorMessage('Не найдено', 'Не загружено');
        }
        die();
    }

    public static function ajaxLoad()
    {
        self::$index = $_POST['index'];

        $arData = json_decode(file_get_contents(ITGAZIEV_DIR . '/cache/arData.json'), true);
        self::$categories = $arData['CATEGORIES'];

        if (isset($arData['ITEMS'][self::$index ])) {
            self::$settings = ITGaziev_YML::getSettings();
            self::$arItem = $arData['ITEMS'][self::$index ];
            self::$parent_cat = self::$categories[self::$arItem['CATEGORY_ID']];
            self::$arItem['CATS'] = self::get_cat(self::$categories, self::$parent_cat);

            self::$product = ITGaziev_DB::get_post_ext('product_' . self::$arItem['ID']);

            $message = (self::$product) ? self::updateProduct() : self::createProduct();

            echo $message;

        } else {
            echo self::errorMessage('Не найдено', 'Не загружено');
        }
        die();
    }

    public static function createProduct()
    {
        $category_field = self::$categories[self::$arItem['CATEGORY_ID']];
        $category = self::catCRU($category_field, self::$categories[$category_field['parent']]);

        $list_cat = [];
        foreach (self::$arItem['CATS'] as $cat) {
            $id_cat_ext = intval(ITGaziev_DB::get_term_ext('product_cat_' . $cat['id']));
            if($id_cat_ext) {
                $list_cat[] = $id_cat_ext;
            }
        }

        $post = [
            'post_author' => 1,
            'post_content' => self::$arItem['DESCRIPTION'],
            'post_status' => "publish",
            'post_title' => self::$arItem['NAME'],
            'post_parent' => intval($category),
            'post_type' => "product",
        ];

        $wp_error = '';
        self::$product = wp_insert_post($post, $wp_error);

        if (self::$product) {
            self::save_image_product();
            self::update_attribute();

            wp_set_object_terms(self::$product, 'simple', 'product_type');
            wp_set_object_terms(self::$product, $list_cat, 'product_cat');


            update_post_meta( self::$product, '_visibility', 'visible' );
            update_post_meta( self::$product, '_sku', 'SH-' . self::$arItem['ID']);

            if(self::$arItem['AVAILABLE']) { update_post_meta( self::$product, '_stock_status', 'instock'); }
            else { update_post_meta( self::$product, '_stock_status', 'outofstock'); }

            update_post_meta( self::$product, 'total_sales', '0');
            update_post_meta( self::$product, '_downloadable', 'no');
            update_post_meta( self::$product, '_virtual', 'no');
            update_post_meta( self::$product, '_regular_price', floatval(self::$arItem['PRICE']));
            update_post_meta( self::$product, 'price_opt', floatval(self::$arItem['PURCHASE_PRICE']));
            update_post_meta( self::$product, 'price_mrc', floatval(self::$arItem['PRICE']));
            update_post_meta( self::$product, 'external_id', self::$arItem['ID']);
            update_post_meta( self::$product, 'external_url', self::$arItem['URL']);
            update_post_meta( self::$product, '_price', floatval(self::$arItem['PRICE']));
            self::update_attribute();
            self::transaction_update();
            ITGaziev_DB::update_post_ext('product_' . self::$arItem['ID'], self::$product);
            //Message Success
            return self::successMessage('Создан', 'blue');
        } else {

            //Message Errors
            return self::errorMessage(self::$arItem['NAME'], 'Ошибка при создание');

        }

    }

    public static function updateProduct()
    {
        $wc_product = wc_get_product(self::$product);
        $price = $wc_product->get_price();
        $regular_price = $wc_product->get_regular_price();


        if(self::$arItem['AVAILABLE']) { update_post_meta( self::$product, '_stock_status', 'instock'); }
        else { update_post_meta( self::$product, '_stock_status', 'outofstock'); }

        update_post_meta( self::$product, 'price_opt', floatval(self::$arItem['PURCHASE_PRICE']));
        update_post_meta( self::$product, 'price_mrc', floatval(self::$arItem['PRICE']));

        if(floatval($price) <  floatval(self::$arItem['PRICE']))
            update_post_meta( self::$product, '_price', floatval(self::$arItem['PRICE']));

        if(floatval($regular_price) <  floatval(self::$arItem['PRICE']))
            update_post_meta( self::$product, '_regular_price', floatval(self::$arItem['PRICE']));

        self::transaction_update();

        return self::successMessage('Обновлен', 'blue');
    }

    public static function update_attribute()
    {
        $attribute = [];

        foreach (self::$arItem['PARAM'] as $item) {

            $set = ITGaziev_YML::getParamSettings($item['NAME']);
            $slug = translit($item['NAME']);
            $external = 'attribute_' . $slug;
            $id = ITGaziev_DB::get_attribute_ext($external);
            if ($set == 'create') {
                if( strlen($slug) >= 28 ) {
                    $slug = substr($slug, 0, 28);
                    $slug = rtrim($slug, "!,.-");
                }
                if (!$id) {
                    $status = self::createAttribute($item['NAME'], $slug, $external);



                    if ($status) {
                        $attribute['pa_' . $slug] = array('name' => 'pa_' . $slug, 'value' => '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                        wp_set_object_terms(self::$product, array($item['VALUE']), 'pa_' . $slug, true);
                    }
                } else {
                    $attribute['pa_'.$slug] = array( 'name'=> 'pa_'.$slug, 'value'=> '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                    wp_set_object_terms(self::$product, array($item['VALUE']), 'pa_' . $slug,false);
                }
            }
            else if($set == 'sku') {  continue; }
            else if($set == 'skip') {  continue; }
            else if($set == 'weight') { update_post_meta( self::$product, '_weight', $item['VALUE']); continue; }
            else if($set == 'length') { update_post_meta( self::$product, '_length', $item['VALUE']); continue; }
            else if($set == 'width') { update_post_meta( self::$product, '_width', $item['VALUE']); continue; }
            else if($set == 'height') { update_post_meta( self::$product, '_height', $item['VALUE']); continue; }
            else {
                if(ITGaziev_DB::check_attribute_slug($set)) {
                    $attribute['pa_'.$set] = array( 'name'=> 'pa_'.$set, 'value'=> '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                    wp_set_object_terms(self::$product, array($item['VALUE']), 'pa_' . $slug);
                }
            }
        }

        if (!empty(self::$arItem['VENDOR'])) {
            $slug = 'brend';
            $name = 'Производитель';
            $external = 'attribute_' . $slug;
            if (!ITGaziev_DB::check_attribute_slug($slug)) {
                $status = self::createAttribute($name, $slug, $external);
                if($status)
                {
                    $attribute['pa_'.$slug] = array( 'name'=> 'pa_'.$slug, 'value'=> '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                    wp_set_object_terms(self::$product, array(self::$arItem['VENDOR']), 'pa_' . $slug);
                }
            }
            else
            {
                $attribute['pa_'.$slug] = array( 'name'=> 'pa_'.$slug, 'value'=> '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                wp_set_object_terms(self::$product, array(self::$arItem['VENDOR']), 'pa_' . $slug);
            }
        }

        if(!empty($arItem['VENDOR_CODE']))
        {
            $slug = 'brend-code';
            $name = 'Код производителя';
            $external = 'attribute_' . $slug;
            if(!ITGaziev_DB::check_attribute_slug($slug))
            {
                $status = self::createAttribute($name, $slug, $external);
                if($status)
                {
                    $attribute['pa_'.$slug] = array( 'name'=> 'pa_'.$slug, 'value'=> '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                    wp_set_object_terms(self::$product, array(self::$arItem['VENDOR_CODE']), 'pa_' . $slug);
                }
            }
            else
            {
                $attribute['pa_'.$slug] = array( 'name'=> 'pa_'.$slug, 'value'=> '', 'position' => 1, 'is_visible' => '1', 'is_variation' => '0', 'is_taxonomy' => '1');
                wp_set_object_terms(self::$product, array(self::$arItem['VENDOR_CODE']), 'pa_' . $slug);
            }
        }
        update_post_meta( self::$product, '_product_attributes', $attribute);

    }

    public static function createAttribute($attribute_name, $slug, $external)
    {
        $id = wc_create_attribute(
            array(
                'name'         => $attribute_name,
                'slug'         => $slug,
                'type'         => 'select',
                'order_by'     => 'menu_order',
                'has_archives' => false,
            )
        );
        if($id) {
            ITGaziev_DB::update_attribute_ext($id, $external);
            self::registerAttribute($slug, $attribute_name);
        }
        return $id;
    }

    public static function registerAttribute($slug, $name)
    {
        register_taxonomy(
            'pa_' . $slug,
            array( 'product' ),
            array(
                'labels'   => array(
                    'name'              => $name,
                    'singular_name'     => $name,
                    'menu_name'         => $name,
                ),
                 'hierarchical' => true,
                 'show_ui'      => false,
                 'query_var'    => true,
                 'rewrite'      => false,
                )
        );
    }

    public static function save_image_product()
    {
        $attachments = [];
        $path = $_SERVER['DOCUMENT_ROOT'].'/wp-content/uploads/';

        $dir = date( "Y/m" );
        if(!is_dir($path . $dir)){
            mkdir($path . $dir, 0755);
        }
        $real_path = $path . $dir . '/';

        foreach (self::$arItem['PICTURE'] as $image) {
            $name = self::$arItem['NAME'];
            $image_info = pathinfo($image);

            $ext = $image_info['extension'];
            $basename = translit($image_info['filename'] . '_' . time());
            $new_path = $real_path . $basename . '.' . $ext;

            $context = itgaziev_get_context();
            $base = file_get_contents($image, false, stream_context_create($context));
            file_put_contents($new_path, $base);

            if (file_exists($new_path)) {
                $image_path = pathinfo($new_path);
                $image_data = getimagesize($new_path);

                $attachment = array('post_mime_type' => $image_data['mime'],
                    'post_title' => self::$arItem['NAME'],
                    'post_status' => 'inherit',
                    'guid' => get_site_url() . '/wp-content/uploads/' . $dir . '/' . $image_path['basename']
                );
                $attachment_id = wp_insert_attachment($attachment, get_site_url() . '/wp-content/uploads/' . $dir . '/' . $image_path['basename']);
                $attachments[] = $attachment_id;
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $new_path);
                wp_update_attachment_metadata( $attachment_id, $attachment_data );
            }
        }

        if(self::$product && !empty($attachments)){
            add_post_meta(self::$product, '_thumbnail_id', $attachments[0]);
            unset($attachments[0]);
            if(!empty($attachments)) update_post_meta( self::$product, '_product_image_gallery', implode(',', $attachments));
        }

    }

    public static function transaction_update() {
        if(!self::$product) return false;

        global $wpdb;

        $wc_product = wc_get_product(self::$product);
        $wpdb->replace( 'wp_wc_product_meta_lookup', array(
            'product_id' => $wc_product->get_id(),
            'sku' => $wc_product->get_sku(),
            'virtual' => 0,
            'downloadable' => 0,
            'min_price' => $wc_product->get_price(),
            'max_price' => $wc_product->get_price(),
            'onsale' => $wc_product->is_on_sale(),
            //'stock_quantity' => $product->get_stock_quantity(),
            'stock_status' => $wc_product->get_stock_status(),
            'rating_count' => $wc_product->get_rating_count(),
            'average_rating' => $wc_product->get_average_rating(),
            'total_sales' => $wc_product->get_total_sales()
        ) );
    }

    public static function get_cat($cat, $current_cat)
    {
        $list_cat = array();
        $list_cat[] = $current_cat;
        $load = false;
        $parent = $current_cat['parent'];
        while (!$load) {
            if (isset($cat[$parent])) {
                $list_cat[] = $cat[$parent];
                $parent = $cat[$parent]['parent'];
            } else {
                $load = true;
            }
        }

        return $list_cat;
    }

    public static function catCRU($cat, $parent)
    {
        $id_cat = ITGaziev_DB::get_term_ext('product_cat_' . $cat['id']);
        if (!$id_cat) {
            $set_parent = 0;
            if ($cat['parent']) {
                $parent_term = ITGaziev_DB::get_term_ext('product_cat_' . $cat['parent']);
                if (!$parent_term && $parent['parent']) {
                    $parent_this = self::$categories[$parent['parent']];
                    $set_parent = self::catCRU($parent, $parent_this);
                }
            }

            $args = [
                'cat_ID'=> 0,
                'cat_name' => $cat['name'],
                'category_description' => '',
                'category_nicename' => translit($cat['name']),
                'category_parent' => $set_parent,
                'taxonomy' => 'product_cat'
            ];

            $id_cat = wp_insert_category($args);
            ITGaziev_DB::update_term_ext('product_cat_' . $cat['id'], $id_cat);
            return $id_cat;
        }

        return $id_cat;
    }

    public static function successMessage($message, $color = 'red')
    {
        $html = '<span style="display: block; font-size: 16px;"><a href="/wp-admin/post.php?post=%s&action=edit" target="blank">%s. %s</a> - <span style="color: %s;">%s</span></span>';

        return sprintf($html, self::$product, self::$index + 1, self::$arItem['NAME'], $color, $message);
    }

    public static function errorMessage($name, $message)
    {
        $html = '<span style="display: block; font-size: 16px;">%s. %s <span style="color: red;">%s</span></span>';

        return sprintf($html, self::$index + 1, $name, $message);
    }
}