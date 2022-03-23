<?
set_time_limit ( 0 );
global $attribute, $arData, $arSettings;
use ITGaziev\Classes\ITGaziev_YML;

$yml = new ITGaziev_YML();
?>

<div class="wrap">
    <h1 class="wp-heading-inline" style="padding-bottom: 20px;">Товары поставщиков</h1>
    <? if(!empty($arData['PARAMS'])): ?><a href="javascript:;" class="page-title-action import-product">Сохранить</a><? endif; ?>
    <div class="row" style="display: flex; align-items: center; margin-bottom: 20px;">
        <div class="col-xs-3" style="padding-right: 20px;">
            <label for="url_price">Ссылка на прайс</label>
        </div>
        <div class="col-xs-9">
            <input name="url_price" type="text" id="url_price" value="<?=get_option('url_prices')?>" class="regular-text ltr">
        </div>
        <div class="col-xs-9">
            <a href="javascript:;" class="page-title-action update-option" style="position: relative; top: 0px;">Загрузить</a>
        </div>
    </div>
    <? if(!empty($arData['PARAMS'])): ?>
        <table class="wp-list-table widefat fixed striped pages">
            <thead>
            <tr>
                <th scope="col" style="width: 5%;">#</th>
                <th scope="col" style="width: 40%;">Название</th>
                <th scope="col" style="width: 40%;">Пременить к</th>
            </tr>
            </thead>
            <tbody>
            <?
            foreach($arData['PARAMS'] as $item):
                ?>
                <tr class="iedit author-self level-0 post-7 type-page status-publish hentry">
                    <th scope="row" class="check-column"></th>
                    <td class="title column-title has-row-actions column-primary page-title">
                        <?=$item?>
                    </td>
                    <td class="title column-title has-row-actions column-primary page-title">
                        <select class="attribute_sel" name="<?=$item?>">
                            <option value="create" <? if($yml::getParamSettings($item) == 'create') { echo 'selected'; } ?>>Создать</option>
                            <option value="skip" <? if($yml::getParamSettings($item) == 'skip') { echo 'selected'; } ?>>Пропустить</option>
                            <option value="sku" <? if($yml::getParamSettings($item) == 'sku') { echo 'selected'; } ?>>Артикул</option>
                            <option value="weight" <? if($yml::getParamSettings($item) == 'weight') { echo 'selected'; } ?>>Вес</option>
                            <option value="length" <? if($yml::getParamSettings($item) == 'length') { echo 'selected'; } ?>>Длина</option>
                            <option value="width" <? if($yml::getParamSettings($item) == 'width') { echo 'selected'; } ?>>Ширина</option>
                            <option value="height" <? if($yml::getParamSettings($item) == 'height') { echo 'selected'; } ?>>Высота</option>
                            <? foreach($attribute as $attr): ?>
                                <option value="<?=$attr['attribute_name']?>"
                                        <? if($attr['attribute_label'] == $item) { ?>selected<??>
                                        <? } else if($yml::getParamSettings($item) == $attr['attribute_name']){ ?>selected<? } ?>
                                ><?=$attr['attribute_label']?></option>
                            <? endforeach; ?>
                        </select>
                    </td>
                </tr>
            <?
            endforeach;
            ?>
            </tbody>
        </table>
    <? else: ?>
        <p>Укажите ссылку на прайс</p>
    <? endif; ?>
</div>
<script>
    jQuery(function($){
        var pathname = window.location.pathname; // Returns path only (/path/example.html)
        var url      = window.location.href;     // Returns full URL (https://example.com/path/example.html)
        var origin   = window.location.origin;   // Returns base URL (https://example.com)
        $(document).on('click', '.update-option', function(){
            var url = $('#url_price').val();
            var json = {
                'action' : 'itgaziev_save_url',
                'url' :  url
            };
            jQuery.post(ajaxurl, json, function(response) {
                window.location.href =  origin + pathname + '?page=itgaziev&load_new_price';
            });
        });

        $(document).on('click', '.import-product', function(){
            var data = [];
            $('.attribute_sel').each(function(){
                var item = {name: $(this).attr('name'), param : $(this).val() };
                data.push(item);
            });
            var json = {
                'action' : 'itgaziev_save_settings',
                'data' :  data
            };
            jQuery.post(ajaxurl, json, function(response) {
                window.location.href =  origin + pathname + '?page=itgaziev';
            });
            return;

        });
    });
</script>