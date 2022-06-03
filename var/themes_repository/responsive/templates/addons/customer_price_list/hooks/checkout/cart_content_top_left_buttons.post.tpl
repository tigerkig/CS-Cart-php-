{if $auth.user_id && $addons.customer_price_list.show_download_link_on_cart == "YesNo::YES"|enum}
    {include file="buttons/button.tpl" but_text=__("customer_price_list.cart.download_price_list") but_href="customer_price_list.cart" but_role="text" but_icon="ty-icon-download"}
{/if}