<div class="ty-sdek-select-store__map-wrapper">
    <div class="ty-sdek-select-store__map cm-geo-map-container" id="{$sdek_map_container}"
         data-ca-geo-map-initial-lat="{$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval}"
         data-ca-geo-map-initial-lng="{$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval}"
         data-ca-geo-map-zoom="16"
         data-ca-geo-map-controls-enable-zoom="true"
         data-ca-geo-map-controls-enable-fullscreen="true"
         data-ca-geo-map-controls-enable-layers="true"
         data-ca-geo-map-controls-enable-ruler="true"
         data-ca-geo-map-behaviors-enable-drag="true"
         data-ca-geo-map-behaviors-enable-dbl-click-zoom="true"
         data-ca-geo-map-behaviors-enable-multi-touch="true"
         data-ca-geo-map-language="{$smarty.const.CART_LANGUAGE}"
         data-ca-geo-map-marker-selector=".cm-rus-sdek-map-marker-{$shipping_id}"
    ></div>
</div>

<div class="ty-mtb-s ty-uppercase clearfix">
    <a class="cm-sdek-show-all-on-map" data-ca-target-map-id="{$sdek_map_container}">{__("addons.rus_sdek.show_all_on_map")}</a>
</div>