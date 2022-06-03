{script src="js/addons/geo_maps/maps.js"}
{script src="js/addons/geo_maps/code.js"}
{script src="js/addons/geo_maps/locate.js"}

{$provider = $settings.geo_maps.general.provider}

{if $provider == "yandex"}
    {script src="js/addons/geo_maps/provider/yandex/index.js"}
    {script src="js/addons/geo_maps/provider/yandex/maps.js"}
    {script src="js/addons/geo_maps/provider/yandex/code.js"}
    {script src="js/addons/geo_maps/provider/yandex/locate.js"}
{elseif $provider == "google"}
    {script src="js/addons/geo_maps/provider/google/index.js"}
    {script src="js/addons/geo_maps/provider/google/maps.js"}
    {script src="js/addons/geo_maps/provider/google/code.js"}
    {script src="js/addons/geo_maps/provider/google/locate.js"}
{/if}

{script src="js/addons/geo_maps/func.js"}

{$api_key = $settings.geo_maps[$provider]["`$settings.geo_maps.general.provider`_api_key"]}

<script>
    (function (_, $) {
        _.geo_maps = {
            provider: '{$settings.geo_maps.general.provider|escape:"javascript"}',
            api_key: '{$api_key|escape:"javascript"}',
            yandex_commercial: {if $settings.geo_maps.yandex.yandex_commercial == "Y"}true{else}false{/if},
            language: "{$smarty.const.CART_LANGUAGE}",
        };

        _.tr({
            geo_maps_google_search_bar_placeholder: '{__("search")|escape:"javascript"}',
            geo_maps_cannot_select_location: '{__("geo_maps.cannot_select_location")|escape:"javascript"}',
        });
    })(Tygh, Tygh.$);
</script>
