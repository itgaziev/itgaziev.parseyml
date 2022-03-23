<?php


namespace ITGaziev\Classes;


class ITGaziev_YML
{
    protected static $arSettings = [];

    function __construct()
    {
        if (is_file(ITGAZIEV_DIR . '/cache/arSettings.json')) {
            self::$arSettings = json_decode(file_get_contents(ITGAZIEV_DIR . '/cache/arSettings.json'), true);
        }
    }

    public static function loadYml()
    {
        $url = get_option('url_prices');
        $arData = [];

        if ($url) {
            $arrContextOptions= [ "ssl"=> [ "verify_peer"=>false, "verify_peer_name"=>false, ]];

            $file = file_get_contents($url, false, stream_context_create($arrContextOptions));

            file_put_contents(ITGAZIEV_DIR . '/cache/import.xml', $file);

            $data = simplexml_load_file(ITGAZIEV_DIR . '/cache/import.xml');

            if ($data) {
                foreach ($data->shop->offers->offer as $row) {
                    $array_row = (array) $row;
                    $arItem = [];
                    $arItem['ID'] = intval($row['id']);
                    $arItem['AVAILABLE'] = strval($row['available']) == 'false' ? 0 : 1;
                    $arItem['NAME'] = strval($row->model);
                    $arItem['URL'] = strval($row->url);
                    $arItem['PRICE'] = strval($row->price);
                    $arItem['PURCHASE_PRICE'] = strval($array_row['price-opt']);
                    $arItem['CURRENCY_ID'] = strval($row->currencyId);
                    $arItem['CATEGORY_ID'] = strval($row->categoryId);
                    $arItem['VENDOR'] = strval($row->vendor);
                    $arItem['VENDOR_CODE'] = strval($row->vendorCode);
                    $arItem['DESCRIPTION'] = strval($row->description);

                    foreach($row->picture as $pict)
                    {
                        $arItem['PICTURE'][] = strval($pict);
                    }
                    $params = array();
                    foreach($row->param as $i => $p)
                    {
                        if(empty($p['name']) || empty($p['value'])) continue;
                        $arData['PARAMS'][strval($p['name'])] = strval($p['name']);
                        $params[] = array('NAME' => strval($p['name']), 'VALUE' => strval($p['value']));
                    }
                    $arItem['PARAM'] = $params;
                    $arData['ITEMS'][] = $arItem;
                }

                foreach ($data->shop->categories->category as $row) {
                    $cat = array('id' => intval($row['id']), 'parent' => intval($row['parentId']), 'name' => strval($row));
                    $arData['CATEGORIES'][intval($row['id'])] = $cat;
                }

                file_put_contents(ITGAZIEV_DIR . '/cache/arData.json', json_encode($arData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        return $arData;
    }
    public static function getSettings()
    {
        return self::$arSettings;
    }

    public static function getParamSettings($name)
    {
        if (empty(self::$arSettings)) return false;

        foreach (self::$arSettings as $param) {
            if($param['name'] == $name) return $param['param'];
        }

        return false;
    }

    public static function saveUrl()
    {
        $option_name = 'url_prices' ;
        $values    = $_POST['url'];

        if (get_option( $option_name ) != $values) {
            update_option($option_name, $values);
        } else {
            $deprecated = '';
            $autoload = 'no';
            add_option($option_name, $values, $deprecated, $autoload);
        }
    }

    public static function saveSettings()
    {
        $values    = $_POST['data'];
        file_put_contents(ITGAZIEV_DIR . '/cache/arSettings.json', json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}