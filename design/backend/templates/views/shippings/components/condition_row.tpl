<tbody>
    <tr class="table-rate__row">
        <td width="55%">  
            <div class="control-group shipping-rate-range">      
                <div class="shipping-rate-range__content">
                    <div class="control-group  shipping-rate-range__start-{literal}${data.destinationId}{/literal}-{literal}${data.type}{/literal}-{literal}${data.index}{/literal}">
                        <label class="hidden shipping-rate-range-start-label" for="start_range_{literal}${data.destinationId}{/literal}_{literal}${data.type}{/literal}_{literal}${data.index}{/literal}"></label>
                        <input type="text"
                            id="start_range_{literal}${data.destinationId}{/literal}_{literal}${data.type}{/literal}_{literal}${data.index}{/literal}"
                            name="shipping_data[rates][{literal}${data.destinationId}{/literal}][rate_value][{literal}${data.type}{/literal}][{literal}${data.index}{/literal}][range_from_value]"
                            data-ca-type="start"
                            data-p-sign="{literal}${data.currencySymbolPlacement}{/literal}"
                            data-a-sign="{literal}${data.unit}{/literal}" 
                            data-a-dec="." 
                            data-a-sep=","
                            data-m-dec="{literal}${data.type == '{/literal}{"ShippingRateTypes::WEIGHT"|enum}{literal}' ? 3 : `${data.type == '{/literal}{"ShippingRateTypes::COST"|enum}{literal}' ? 2 : 0}`}{/literal}"
                            value="{literal}${data.rateValue ? data.rateValue.range_from_value : ''}{/literal}" 
                            class="input-hidden cm-numeric shipping-rate__input-large shipping-rate-start-range"
                            placeholder="{literal}${data.placeholderFrom}{/literal}"     
                        />
                    </div>
                    
                    <div class="shipping-rate-range__content-delimiter">&ndash;</div>
                    
                    <div class="control-group shipping-rate-range__end-{literal}${data.destinationId}{/literal}-{literal}${data.type}{/literal}-{literal}${data.index}{/literal}">
                        <label class="hidden shipping-rate-range-end-label" for="end_range_{literal}${data.destinationId}{/literal}_{literal}${data.type}{/literal}_{literal}${data.index}{/literal}"></label>
                        <input type="text" 
                            id="end_range_{literal}${data.destinationId}{/literal}_{literal}${data.type}{/literal}_{literal}${data.index}{/literal}"
                            name="shipping_data[rates][{literal}${data.destinationId}{/literal}][rate_value][{literal}${data.type}{/literal}][{literal}${data.index}{/literal}][range_to_value]" 
                            data-ca-type="end"
                            data-a-sign="{literal}${data.unit}{/literal}"
                            data-p-sign="{literal}${data.currencySymbolPlacement}{/literal}"
                            data-a-dec="." 
                            data-a-sep=","
                            data-m-dec="{literal}${data.type == '{/literal}{"ShippingRateTypes::WEIGHT"|enum}{literal}' ? 3 : `${data.type == '{/literal}{"ShippingRateTypes::COST"|enum}{literal}' ? 2 : 0}`}{/literal}"
                            value="{literal}${data.rateValue ? data.rateValue.range_to_value : ''}{/literal}" 
                            class="input-hidden cm-numeric shipping-rate__input-large shipping-rate-end-range"
                            placeholder="{literal}${data.placeholderTo}{/literal}"
                        />
                    </div>
                </div>
                
                <label class="hidden shipping-rate-range-label" for="shipping_rate_range_{literal}${data.destinationId}{/literal}_{literal}${data.type}{/literal}_{literal}${data.index}{/literal}"></label> 
                <input type="text" class="hidden" id="shipping_rate_range_{literal}${data.destinationId}{/literal}_{literal}${data.type}{/literal}_{literal}${data.index}{/literal}"/>
            </div>
        </td>
        <td>
            <div class="input-append shipping-rate__input-append shipping-rate__input-append--per-unit">
                <input type="text" 
                    name="shipping_data[rates][{literal}${data.destinationId}{/literal}][rate_value][{literal}${data.type}{/literal}][{literal}${data.index}{/literal}][value]"
                    value="{literal}${data.rateValue ? data.rateValue.value : ''}{/literal}" 
                    class="shipping-rate__surcharge-discount cm-numeric shipping-rate__input-append--large input-hidden" 
                    placeholder="{__("shipping_surcharge_discount", ["[object]" => $currencies.$primary_currency.symbol|strip_tags])}"
                    data-destination-id="{literal}${data.destinationId}{/literal}"
                    data-a-sign="{$currencies.$primary_currency.symbol|strip_tags nofilter}" 
                    data-a-dec="." 
                    data-a-sep=","
                    {if $currencies.$primary_currency.after == "Y"}data-p-sign="s"{/if} 
                />

                {if $allow_save}
                    {literal}
                        ${data.type == "W" || data.type == "I"
                        ? `
                            <div class="btn-group shipping-rate_${data.index}_per-unit">
                                <button class="btn btn-default dropdown-toggle button-hidden" data-toggle="dropdown">
                                    <span class="text"></span>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu shipping-rate__input-append-list">
                                    <li>
                                        <input type="hidden"
                                            name="shipping_data[rates][${data.destinationId}][rate_value][${data.type}][${data.index}][per_unit]"
                                            value="N"
                                        />
                                        <input type="checkbox"
                                            id="shipping_rate_${data.destinationId}_per_unit_${data.index}" 
                                            name="shipping_data[rates][${data.destinationId}][rate_value][${data.type}][${data.index}][per_unit]" 
                                            value="Y"
                                            class="cm-item"
                                        />
                                        ${data.perUnit}                        
                                    </li>
                                </ul>
                            </div>
                        `: ``}
                    {/literal}
                {else}
                    <div class="shipping-rate_{$data.index}_per-unit">
                        <span class="text"></span>
                    </div>
                {/if}
            </div>    
        </td>
        <td>
            {include file="buttons/remove_item.tpl" only_delete="Y" but_class="cm-delete-row" simple="true"}
        </td>
    </tr>
</tbody>