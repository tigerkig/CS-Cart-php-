{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'sdek' && $shipping.data.offices|count >= 1}
    {$office_count = $shipping.data.offices|count}
    {$shipping_id = $shipping.shipping_id}
    {$old_office_id = $select_office.$group_key.$shipping_id}
    {$sdek_map_container = "sdek_map_`$shipping_id`"}
    {script src="js/addons/rus_sdek/map.js"}

    {* Map *}
    {hook name="checkout:rus_sdek_step_checkout_pickup_content"}
    {/hook}

    {foreach $shipping.data.offices as $store}
        {capture name="marker_content"}
            <div style="padding-right: 10px">
                <strong>{$store.Name}</strong>
                <p>
                    {if $store.City}{$store.City nofilter}, {/if}
                    {if $store.FullAddress}{$store.FullAddress nofilter}{/if}
                    {if $store.Phone}<br/>{$store.Phone nofilter}{/if}
                    {if $store.WorkTime}<br/>{$store.WorkTime nofilter}{/if}
                    {if $store.description}<br/>{$store.description nofilter}{/if}
                    <p><a
                        data-ca-shipping-id="{$shipping_id}"
                        data-ca-group-key="{$group_key}"
                        data-ca-location-id="{$store.Code}"
                        data-ca-target-map-id="{$sdek_map_container}"
                        class="cm-sdek-select-location ty-btn ty-btn__tertiary text-button">{__("select")}</a>
                    </p>
                </p>
            </div>
        {/capture}

        <div class="cm-rus-sdek-map-marker-{$shipping_id} hidden"
             data-ca-geo-map-marker-lat="{$store.coordY}"
             data-ca-geo-map-marker-lng="{$store.coordX}"
                {if $old_office_id == $store.Code || $store_count == 1}
                    data-ca-geo-map-marker-selected="true"
                {/if}
        >{$smarty.capture.marker_content nofilter}</div>
    {/foreach}

    <div class="ty-sdek-office-search ty-sdek-office-search-disabled">
        <input id="sdek_office_search" type="text" title="{__("addons.rus_sdek.search_string")}" class="ty-input-text-medium cm-hint ty-search-office">
    </div>

    {include
        file="addons/rus_sdek/views/sdek/sdek_offices.tpl"
        group_key=$group_key
        sdek_map_id=$sdek_map_container
        shipping_id=$shipping_id
        sdek_offices=$shipping.data.sdek_offices
    }

    {if $office_count > 6}
        <div class="ty-mtb-s ty-uppercase clearfix">
            <a
                class="cm-show-all-point cm-ajax"
                href="{"sdek.sdek_offices?group_key=`$group_key`&shipping_id=`$shipping_id`&old_office_id=`$old_office_id`"|fn_url}"
                id="sdek_show_all"
                data-ca-scroll="{$sdek_map_container}"
                data-ca-group-key="{$group_key}"
                data-ca-shipping-id="{$shipping_id}"
            >{__("addons.rus_sdek.show_all")}</a>
        </div>
    {/if}
{/if}

{script src="js/addons/rus_sdek/sdek_search.js"}
