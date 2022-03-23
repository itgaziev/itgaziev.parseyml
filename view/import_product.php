<style>
    progress {
        background-color: #00FEFD;
        border-radius: 10px;
        border: 0;
        height: 20px;
        width: 100%;
    }
    .position-info
    {
        display: block;
        font-size: 18px;
        font-style: italic;
        font-weight: bold;
        text-align: center;
    }
    .start-import
    {
        display: block;
        width: 100%;
        margin-top: 10px;
        margin-bottom: 20px;
        text-align: center;
    }
    .import-product {
        font-size: 18px !important;
        width: 200px !important;
        display: inline-block;
    }
</style>
<?
set_time_limit ( 0 );
global $attribute, $arData, $arSettings;

use ITGaziev\Classes\ITGaziev_Import;
use ITGaziev\Classes\ITGaziev_YML;

$yml = new ITGaziev_YML();
//ITGaziev_Import::ajaxLoad(0);
//XMLWoo::AjaxLoadTest(0);
?>
<div class="wrap">
    <h1 class="wp-heading-inline" style="padding-bottom: 20px;">Импорт прайса</h1>
    <? if(!empty($arData['ITEMS'])) : ?>
        <p class="position-info">Кол.: <? echo count($arData['ITEMS']); ?> / Загружено <span id="loaded">0</span></p>
        <progress id="progress" value="0" max="<? echo count($arData['ITEMS']); ?>"></progress>
        <p class="start-import"><a href="javascript:;" class="page-title-action import-product">Импорт</a></p>
        <p class="short-info">
        </p>
    <? else: ?>
        <p>Сначала загрузите прайс и настройте</p>
    <? endif; ?>


</div>
<script>
    jQuery(function($){
        var count = <? if(empty($arData['ITEMS'])) echo 0; else echo (count($arData['ITEMS']) - 1); ?>;
        var item = 0;

        $(document).on('click', '.import-product', function(){
            StartImport();
            return;
        });

        function StartImport()
        {
            var json = {
                'action' : 'itgaziev_save_and_update_prods',
                'index' :  item
            };
            console.log(count);
            jQuery.post(ajaxurl, json, function(response) {
                $('.short-info').append(response);
                $('#progress').val(item + 1);
                $('#loaded').html(item + 1)
                item++;
                if(item <= count)
                {
                    setTimeout(StartImport, 5);
                }
            });
        }
    });
</script>