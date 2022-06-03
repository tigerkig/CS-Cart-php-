{if $usergroup.type === "UsergroupTypes::TYPE_CUSTOMER"|enum}
    <div class="control-group">
        <label class="control-label" for id="elm_min_spend_value">{__("client_tiers.minimum_spend_value")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
        <div class="controls">
            <input class="cm-numeric" type="text" id="elm_min_spend_value" value="{$usergroup.min_spend_value|default:"0"}" name="usergroup_data[min_spend_value]"/>
        </div>
        <div class="controls">
            <p class="muted description">{__("client_tiers.tooltip_minimum_spend_value")}</p>
        </div>
    </div>
{/if}