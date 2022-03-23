<?php
add_action( 'admin_menu', 'register_admin_page' );
function register_admin_page()
{
    add_menu_page( 'Товары Поставщиков', 'Товары Поставщиков', 'edit_others_posts', 'itgaziev', 'itgaziev_yml_settings', '', 6 );
    add_submenu_page( 'itgaziev', 'Импорт прайса', 'Импорт прайса', 'edit_others_posts', 'xml_import', 'itgaziev_yml_import');
}

function itgaziev_yml_settings()
{
    global $attribute, $yml, $arData, $arSettings;

    $yml = new ITGaziev\Classes\ITGaziev_YML();
    $db = new ITGaziev\Classes\ITGaziev_DB();

    if(isset($_GET['load_new_price'])) {
        $arData = $yml::loadYml();
        $arSettings = $yml::getSettings();
        $attribute = $db::get_attribute_prod();
        include ITGAZIEV_DIR . '/view/yml_settings.php';
    } else {
        $arData = (is_file(ITGAZIEV_DIR . '/cache/arData.json')) ?
            json_decode(file_get_contents(ITGAZIEV_DIR . '/cache/arData.json'), true) : [];

        $arSettings = (is_file(ITGAZIEV_DIR . '/cache/arSettings.json')) ?
            json_decode(file_get_contents(ITGAZIEV_DIR . '/cache/arSettings.json'), true) : [];

        $attribute = ITGaziev\Classes\ITGaziev_DB::get_attribute_prod();
        include ITGAZIEV_DIR . '/view/yml_settings.php';
    }
}

function itgaziev_yml_import()
{
    global $arData;

    $arData = (is_file(ITGAZIEV_DIR . '/cache/arData.json')) ?
        json_decode(file_get_contents(ITGAZIEV_DIR . '/cache/arData.json'), true) : [];
    include ITGAZIEV_DIR . '/view/import_product.php';
}