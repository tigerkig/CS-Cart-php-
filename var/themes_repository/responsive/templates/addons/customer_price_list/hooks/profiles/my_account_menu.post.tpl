{if $addons.customer_price_list.show_download_link_on_profile == "YesNo::YES"|enum && $auth.is_price_list_exists}
    <li class="ty-account-info__item ty-dropdown-box__item">
        <a href="{"customer_price_list.get"|fn_url}" rel="nofollow">{__("customer_price_list.account.download_price_list")}</a>
    </li>
{/if}