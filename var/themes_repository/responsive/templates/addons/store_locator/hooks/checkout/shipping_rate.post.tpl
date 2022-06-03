{if isset($shipping.pickup_rate_from)}
    {capture name="formatted_min_rate"}
        {include file="common/price.tpl" value=$shipping.pickup_rate_from class="ty-nowrap"}
    {/capture}
    {$rate = __("store_locator.shipping_price_from", ['[price]' => $smarty.capture.formatted_min_rate]) scope=parent}
{/if}
