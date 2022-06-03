{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'store_locator'}

    {$store_count = $shipping.data.stores|count}
    {$shipping_id = $shipping.shipping_id}
    {$old_store_id = $select_store.$group_key.$shipping_id}

    {if $shipping.service_params.display}
        {$display_type = $shipping.service_params.display}
    {else}
        {$display_type = "ML"}
    {/if}

    {$store_locations = $shipping.data.stores}

    {if $display_type != 'L'}
        {script src="js/addons/store_locator/pickup.js"}
        {$map_container = "pickup_map_container_`$shipping_id`"}
    {/if}

    <label for="pickup_office_list"
           class="cm-required cm-multiple-radios hidden"
           data-ca-validator-error-message="{__("pickup_point_not_selected")}">
    </label>
    <div class="clearfix ty-checkout-select-store__map-full-div"
         id="pickup_office_list"
    >
        {* Map *}
        {hook name="checkout:store_locator_step_checkout_pickup_content"}
        {/hook}

        {if $display_type != 'M'}
            {if $display_type == 'L'}
                <div class="ty-checkout-select-store__list">
            {else}
                <div class="ty-checkout-select-store">
            {/if}
                    {foreach $shipping.data.stores as $store}
                        <div class="ty-one-store">
                            <input type="radio"
                                name="select_store[{$group_key}][{$shipping_id}]"
                                value="{$store.store_location_id}"
                                {if $old_store_id == $store.store_location_id || $store_count == 1}checked="checked"{/if}
                                id="store_{$group_key}_{$shipping_id}_{$store.store_location_id}"
                                class="ty-one-store__radio-{$group_key} ty-valign cm-sl-pickup-select-store"
                                data-ca-pickup-select-store="true"
                                data-ca-shipping-id="{$shipping_id}"
                                data-ca-group-key="{$group_key}"
                                data-ca-location-id="{$store.store_location_id}">
                            <div class="ty-one-store__label">
                            <label for="store_{$group_key}_{$shipping_id}_{$store.store_location_id}" class="ty-valign"  >
                                <p class="ty-one-store__name">{$store.name} {if $store.pickup_rate}({include file="common/price.tpl" value=$store.pickup_rate}){/if}</p>
                                <div class="ty-one-store__description">
                                {$store.city}{if $store.pickup_address}, {$store.pickup_address}{/if}</br>
                                {if $store.pickup_phone}{__("phone")}: {$store.pickup_phone}</br>{/if}
                                {if $store.pickup_time}{__("store_locator.work_time")}: {$store.pickup_time}</br>{/if}
                                {if $store.delivery_time}{__("delivery_time")}: {$store.delivery_time}</br>{/if}
                                {if $store.description}
                                    <a id="sw_store_description_{$store.store_location_id}" class="cm-combination ty-cart-content__detailed-link detailed-link">{__("description")}</a>
                                    <div id="store_description_{$store.store_location_id}" class="hidden">
                                    {$store.description nofilter}
                                    </div>
                                    </br>
                                {/if}
                                </div>
                            </label>

                        {if $display_type != 'L'}
                            {hook name="checkout:store_locator_step_checkout_view_on_map"}
                            {/hook}
                        {/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
        {else}

            {foreach from=$shipping.data.stores item=store}
                {if $old_store_id == $store.store_location_id || $store_count == 1}
                        <div class="ty-one-store__select-store">

                            <p class="ty-one-store__name">{$store.name} {if $store.pickup_rate}({include file="common/price.tpl" value=$store.pickup_rate}){/if}</p>
                            <div class="ty-one-store__description">
                            {$store.city}{if $store.pickup_address}, {$store.pickup_address}{/if}</br>
                            {if $store.pickup_phone}{__("phone")}: {$store.pickup_phone}</br>{/if}
                            {if $store.pickup_time}{__("store_locator.work_time")}: {$store.pickup_time}</br>{/if}
                            {if $store.description}
                                <a id="sw_store_description_{$store.store_location_id}" class="cm-combination ty-cart-content__detailed-link detailed-link">{__("description")}</a>
                                <div id="store_description_{$store.store_location_id}" class="hidden">
                                {$store.description nofilter}
                                </div>
                                </br>
                            {/if}
                            </div>

                            {if $store_count > 1}
                                <div data-ca-group-key="{$group_key}" class="ty-checkout-select-store__item-view">
                                    {include file="buttons/button.tpl"
                                        but_role="text"
                                        but_meta="cm-sl-pickup-show-all-on-map ty-btn__tertiary"
                                        but_text=__("view_all")
                                        but_extra="data-ca-target-map-id={$map_container}"
                                    }
                                </div>
                            {/if}
                        </div>
                {/if}
                <input type="radio"
                    class="ty-one-store__radio-{$group_key} hidden"
                    name="select_store[{$group_key}][{$shipping_id}]"
                    value="{$store.store_location_id}"
                    {if $old_store_id == $store.store_location_id || $store_count == 1}checked="checked"{/if}
                    id="store_{$group_key}_{$shipping_id}_{$store.store_location_id}"
                    data-ca-pickup-select-store="true"
                    data-ca-shipping-id="{$shipping_id}"
                    data-ca-group-key="{$group_key}"
                    data-ca-location-id="{$store.store_location_id}">
            {/foreach}

        {/if}
    </div>
{/if}
