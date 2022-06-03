{*
    $user_data                      array                               User data
*}

{if $user_data.is_buyer || $user_data.user_id}
    
    {if $user_data.is_buyer === "YesNo::YES"|enum}
        <i class="icon-ok-sign muted" title="{__("product_reviews.verified_purchase")}"></i>
    {/if}

    {if $user_data.is_anon}
        <i class="icon-eye-close muted" title="{__("anonymous")}"></i>
    {/if}

{/if}
