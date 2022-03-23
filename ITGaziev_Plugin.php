<?php
namespace ITGaziev;
use ITGaziev\Classes\ITGaziev_DB;

/**
 * Class ITGaziev_Plugin
 * @package ITGaziev
 */
class ITGaziev_Plugin
{
    public static function installPlugin()
    {
        ITGaziev_DB::addField();
    }

    public static function uninstallPlugin()
    {
        global $wpdb;

        $wpdb->query("ALTER TABLE $wpdb->posts DROP COLUMN `external_id`");
        $wpdb->query("ALTER TABLE $wpdb->terms DROP COLUMN `external_id`");
        $wpdb->query("ALTER TABLE $wpdb->users DROP COLUMN `external_id`");
        $wpdb->query("ALTER TABLE {$wpdb->base_prefix}woocommerce_attribute_taxonomies DROP COLUMN `external_id`");
    }

}