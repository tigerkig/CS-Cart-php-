<div class="ty-control-group">
    {if $cart.user_data.phone}
        {$phone=$cart.user_data.phone}
    {else}
        {if $cart.user_data.b_phone && !$cart.user_data.s_phone}
            {$phone=$cart.user_data.b_phone}
        {elseif !$cart.user_data.b_phone && $cart.user_data.s_phone}
            {$phone=$cart.user_data.s_phone}
        {elseif $cart.user_data.b_phone && $cart.user_data.s_phone}
            {if $settings.Checkout.address_position == "billing_first"}
                {$phone=$cart.user_data.b_phone}
            {else}
                {$phone=$cart.user_data.s_phone}
            {/if}
        {else}
            {$phone=""}
        {/if}
    {/if}
    <label for="customer_phone" class="ty-control-group__title cm-mask-phone-label">{__("phone")}</label>
    <input id="customer_phone" size="35" type="text" name="payment_info[customer_phone]" value="{$phone}" class="ty-input-text cm-autocomplete-off cm-mask-phone" />
</div>