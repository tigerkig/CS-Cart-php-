{*
array  $id                 Storefront ID
array  $all_currencies     All currencies
*}

<div class="control-group">
    <label for="currencies_{$id}"
           class="control-label"
    >
        {__("currencies")}
    </label>
    <div class="controls" id="currencies_{$id}">
        {foreach $all_currencies as $currency}
            {$currency_storefront_ids = []}
            {if $currency.storefront_ids}
                {$currency_storefront_ids = explode(',', $currency.storefront_ids)}
            {/if}
            {if
                $currency_storefront_ids === []
                || in_array($id, $currency_storefront_ids)
            }
                <p>{$currency.description}</p>
            {/if}
        {/foreach}
        <p><a href="{fn_url("currencies.manage")}" target="_blank">{__("storefronts.manage_currency_availability")}</a></p>
    </div>
</div>
