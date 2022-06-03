{if $display_type == 'M'}
    {if $store_count > 1}
        <h3>{__("available")}: {$store_count}
            <div data-ca-group-key="{$group_key}" class="ty-checkout-select-store__item-view">
                {include file="buttons/button.tpl"
                but_role="text"
                but_meta="cm-sl-pickup-show-all-on-map ty-btn__tertiary"
                but_extra="data-ca-target-map-id={$map_container}"
                but_text=__("view_all")
                }
            </div>
        </h3>
    {/if}
    {$map_container_meta = "ty-checkout-select-store__map-full"}
{elseif $display_type == 'ML'}
    {if $store_count > 1}
        <div data-ca-group-key="{$group_key}" class="ty-checkout-select-store__item-view">
            {include file="buttons/button.tpl"
            but_role="text"
            but_meta="cm-sl-pickup-show-all-on-map ty-btn__tertiary"
            but_extra="data-ca-target-map-id={$map_container}"
            but_text=__("view_all")
            }
        </div>
    {/if}
    {$map_container_meta = "ty-checkout-select-store__map"}
{/if}

{if $map_container}
    {foreach from=$shipping.data.stores item=store}
        {capture name="marker_content"}
            <div style="padding-right: 10px">
                <strong>{$store.name}</strong>
                <p>
                    {if $store.city}{$store.city nofilter}, {/if}
                    {if $store.pickup_address}{$store.pickup_address nofilter}{/if}
                    {if $store.pickup_phone}<br/>{$store.pickup_phone nofilter}{/if}
                    {if $store.pickup_time}<br/>{$store.pickup_time nofilter}{/if}
                    {if $store.description}<br/>{$store.description nofilter}{/if}
                <p><a
                            data-ca-shipping-id="{$shipping_id}"
                            data-ca-group-key="{$group_key}"
                            data-ca-location-id="{$store.store_location_id}"
                            class="cm-sl-pickup-select-location ty-btn ty-btn__tertiary text-button">{__("select")}</a>
                </p>
                </p>
            </div>
        {/capture}

        {$is_store_selected = $old_store_id == $store.store_location_id || $store_count == 1}

        {if $is_store_selected}
            {$selected_store_lat = $store.latitude}
            {$selected_store_lng = $store.longitude}
        {/if}

        <div class="cm-sl-pickup-maps-marker-{$shipping_id} hidden"
             data-ca-geo-map-marker-lat="{$store.latitude}"
             data-ca-geo-map-marker-lng="{$store.longitude}"
                {if $is_store_selected}
                    data-ca-geo-map-marker-selected="true"
                {/if}
        >{$smarty.capture.marker_content nofilter}</div>

        {if $store.latitude && $store.longitude}
            {$initial_lat = $selected_store_lat|default:$store.latitude}
            {$initial_lng = $selected_store_lng|default:$store.longitude}
        {/if}

    {/foreach}
    <div class="{$map_container_meta} cm-geo-map-container" id="{$map_container}"
         data-ca-geo-map-initial-lat="{$initial_lat|default:$smarty.const.STORE_LOCATOR_DEFAULT_LATITUDE|doubleval}"
         data-ca-geo-map-initial-lng="{$initial_lng|default:$smarty.const.STORE_LOCATOR_DEFAULT_LONGITUDE|doubleval}"
         data-ca-geo-map-zoom="16"
         data-ca-geo-map-controls-enable-zoom="true"
         data-ca-geo-map-controls-enable-fullscreen="true"
         data-ca-geo-map-controls-enable-layers="true"
         data-ca-geo-map-controls-enable-ruler="true"
         data-ca-geo-map-behaviors-enable-drag="true"
         data-ca-geo-map-behaviors-enable-dbl-click-zoom="true"
         data-ca-geo-map-behaviors-enable-multi-touch="true"
         data-ca-geo-map-language="{$smarty.const.CART_LANGUAGE}"
         data-ca-geo-map-marker-selector=".cm-sl-pickup-maps-marker-{$shipping_id}"
    ></div>
{/if}