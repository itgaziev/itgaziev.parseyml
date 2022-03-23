<?php
namespace ITGaziev\Classes;
/**
 * Class ITGaziev_DB
 * @package ITGaziev\Classes
 */
class ITGaziev_DB
{
    public static function addField()
    {
        global $wpdb;

        $_query = "ALTER TABLE $wpdb->posts ADD COLUMN `external_id` VARCHAR(255) NULL AFTER `ID`";
        $wpdb->query( $_query );

        $_query = "ALTER TABLE $wpdb->terms ADD COLUMN `external_id` VARCHAR(255) NULL AFTER `term_id`";
        $wpdb->query( $_query );

        $_query = "ALTER TABLE $wpdb->users ADD COLUMN `external_id` VARCHAR(255) NULL AFTER `ID`";
        $wpdb->query( $_query );

        $_query = "ALTER TABLE {$wpdb->base_prefix}woocommerce_attribute_taxonomies ADD COLUMN `external_id` VARCHAR(255) NULL AFTER `attribute_id`";
        $wpdb->query( $_query );
    }

    public static function check_attribute_slug($slug)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT attribute_name 
                FROM {$wpdb->prefix}woocommerce_attribute_taxonomies 
                WHERE attribute_name = %s", $slug
        ));
        return $result;
    }

    public static function get_attribute_ext($external)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT attribute_name 
                    FROM {$wpdb->prefix}woocommerce_attribute_taxonomies 
                    WHERE external_id = %s", $external
        ));
        return $result;
    }

    public static function get_term_ext($external)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT term_id FROM $wpdb->terms 
                        WHERE external_id = %s", $external
        ));
        return $result;
    }

    public static function get_ext_by_term($term_id)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT external_id 
                    FROM $wpdb->terms 
                    WHERE term_id = %d", $term_id
        ));
        return $result;
    }

    public static function get_post_ext($external)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM $wpdb->posts 
                    WHERE external_id = %s", $external
        ));
        return $result;
    }

    public static function update_attribute_ext($attribute_id, $external)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "UPDATE {$wpdb->prefix}woocommerce_attribute_taxonomies 
                    SET external_id = %s WHERE attribute_id = %d",
            $external,
            $attribute_id
        ));
        return $result;
    }

    public static function update_term_ext($external, $term_id)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "UPDATE $wpdb->terms 
                    SET external_id = %s 
                    WHERE term_id = %d",
            $external,
            $term_id
        ));
        return $result;
    }

    public static function update_post_ext($external, $post_id)
    {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare(
            "UPDATE $wpdb->posts 
                    SET external_id = %s 
                    WHERE ID = %d",
            $external,
            $post_id
        ));
        return $result;
    }

    public static function get_attribute_prod()
    {
        global $wpdb;

        $attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC;",ARRAY_A  );
        //set_transient( 'wc_attribute_taxonomies', $attribute_taxonomies );

        $attribute = array_filter( $attribute_taxonomies  ) ;
        return $attribute;
    }
}