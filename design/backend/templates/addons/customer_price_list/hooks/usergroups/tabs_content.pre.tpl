{if $usergroup.type === "UsergroupTypes::TYPE_CUSTOMER"|enum}
    <div class="control-group">
        <label class="control-label" for="elm_enable_generating_price_list_{$id}">{__("customer_price_list.enable_generating_price_list")}</label>
        <div class="controls">
            <input type="hidden" value="{"YesNo::NO"|enum}" name="usergroup_data[is_price_list_enabled]" />
            <input type="checkbox"
                id="elm_enable_generating_price_list_{$id}"
                value="{"YesNo::YES"|enum}"
                name="usergroup_data[is_price_list_enabled]"
                {if $usergroup.is_price_list_enabled|default:{"YesNo::NO"|enum} === "YesNo::YES"|enum}checked{/if}
            />
        </div>
        <div class="controls">
            <p class="muted description">{__("customer_price_list.tooltip_enable_generating_price_list")}</p>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_price_list_priority_{$id}">{__("customer_price_list.price_list_priority")}</label>
        <div class="controls">
            <input type="text"
                name="usergroup_data[price_list_priority]"
                value="{$usergroup.price_list_priority|default:0}"
                id="elm_price_list_priority_{$id}"
                class="cm-numeric"
                data-m-dec="0"
            />
        </div>
        <div class="controls">
            <p class="muted description">{__("customer_price_list.tooltip_price_list_priority")}</p>
        </div>
    </div>
{/if}