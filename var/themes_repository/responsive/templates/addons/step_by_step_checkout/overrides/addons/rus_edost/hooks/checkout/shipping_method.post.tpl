{script src="js/addons/rus_edost/func.js"}

{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == "edost"}

    {assign var="office_count" value=$shipping.data.office|count}

    {assign var="shipping_id" value=$shipping.shipping_id}
    {assign var="old_office_id" value=$select_office.$group_key.$shipping_id}

    {if !$old_office_id && $office_count > 1}
        {assign var="old_office_id" value=$shipping.data.office|key}
    {/if}

    <div class="ty-checkout-select-office">
        {if $shipping.data.city_pickpoint}
            {script src="//pickpoint.ru/select/postamat.js" charset="utf-8"}
            <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_id]" id="pickpoint_id" value="{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_id}" />
            <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_name]" id="pickpoint_name" value="{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_name}" />
            <input type="hidden" name="pickpointmap[{$group_key}][{$shipping.shipping_id}][pickpoint_address]" id="pickpoint_address" value="{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_address}" />

            <div class="ty-one-office__name">
                <div id="pickpoint_name_terminal">{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_name}</div>
                <div id="pickpoint_address_terminal">{$cart.pickpointmap.$group_key.$shipping_id.pickpoint_address}</div>
            </div>

            <a href="#" id="pickpoint_select_terminal" data-pickpoint-select-state="" data-pickpoint-select-city="{$shipping.data.city_pickpoint}">{__("select")}</a>
        {/if}

        {foreach from=$shipping.data.office item=office}
            <div class="ty-one-office">
                <input type="radio"
                       name="select_office[{$group_key}][{$shipping.shipping_id}]"
                       value="{$office.office_id}"
                       {if $old_office_id == $office.office_id || $office_count == 1}checked="checked"{/if}
                       id="office_{$group_key}_{$shipping.shipping_id}_{$office.office_id}"
                       class="ty-office-radio"
                       data-ca-pickup-select-office="true"
                       data-ca-shipping-id="{$shipping.shipping_id}"
                       data-ca-group-key="{$group_key}"
                       data-ca-location-id="{$office.office_id}"
                />
                <div class="ty-one-office__label">
                    <label for="office_{$group_key}_{$shipping.shipping_id}_{$office.office_id}" >
                        <p class="ty-one-office__name">{$office.name}</p>
                        <div class="ty-one-office__description">{$office.address} (<a target="_blank" href="http://www.edost.ru/office.php?c={$office.office_id}">{__("edost.header.office_map")}</a>)
                            <br />
                            {$office.tel}<br />
                            {$office.schedule}<br />
                        </div>
                    </label>
                </div>
            </div>
        {/foreach}
    </div>
{/if}
