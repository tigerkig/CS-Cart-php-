<label for="sdek_offices"
       class="cm-required cm-multiple-radios hidden"
       data-ca-validator-error-message="{__("pickup_point_not_selected")}"></label>
<div class="ty-sdek-checkout-select-office" id="sdek_offices">
{$count = 1}
{$sdek_map_id = $sdek_map_id|default:"sdek_map_`$group_key`"}

{foreach from=$sdek_offices item=office name=item}
    {if $count == 1}
    <div class="clearfix">
    {/if}
    <div class="ty-sdek-office{if $old_office_id == $office.Code || $office_count == 1} ty-sdek-office__selected{/if}" id="sdek_office">
        <input type="radio"
            name="select_office[{$group_key}][{$shipping_id}]"
            value="{$office.Code}"
            {if $old_office_id == $office.Code || $office_count == 1}checked="checked"{/if}
            id="office_{$group_key}_{$shipping_id}_{$office.Code}"
            class="cm-sdek-select-store ty-sdek-office__radio-{$group_key} ty-valign"
            data-ca-pickup-select-office="true"
            data-ca-shipping-id="{$shipping_id}"
            data-ca-group-key="{$group_key}"
            data-ca-location-id="{$office.Code}">
        <div class="ty-sdek-store__label">
            <a
                data-ca-scroll="{$sdek_map_id}"
                data-ca-latitude="{$office.coordY}"
                data-ca-longitude="{$office.coordX}"
                data-ca-target-map-id="{$sdek_map_id}"
                class="cm-sdek-view-location ty-sdek-icon-location"></a>
            <label for="office_{$group_key}_{$shipping_id}_{$office.Code}" class="ty-one-office__name">
                {$office.Name}<i class="ty-sdek-name-office-{$group_key}-{$office.Code} ty-icon-ok {if $old_office_id != $office.Code}ty-sdek-office-point-disabled{/if}"></i>
                <div>
                    {$office.City}, {$office.Address}
                    <br/>
                    {if $office.Phone}{__("phone")}: {$office.Phone}</br>{/if}
                    {$office.WorkTime}<br />
                    {$office.Note}
                </div>
            </label>
        </div>
    </div>
    {$count = $count + 1}
    {if $count > 3}
        </div>
        {$count = 1}
    {/if}
{/foreach}
<!--sdek_offices--></div>
