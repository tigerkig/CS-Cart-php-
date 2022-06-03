{if $item.name === "frontend_default_language" && $show_language_warning}
    <div class="text-warning">
        <strong>{__("warning")}!</strong>
        {if $is_default_storefront_affected}
            {__("seo.default_storefront_frontend_default_language_warning", ["[link]" => "addons.update?addon=seo"|fn_url])}
        {else}
            {__("seo.storefront_frontend_default_language_warning", ["[link]" => "addons.update?addon=seo"|fn_url])}
        {/if}
    </div>
{/if}