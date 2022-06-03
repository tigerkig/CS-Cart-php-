{$link_target=$link_target|default:"auto"}
{if !($link_target === "auto"
    && ($runtime.controller == "products" || $runtime.controller == "companies")
    && $runtime.mode === "view"
    && !$product.average_rating)
}
    {$link_target = "url"}
{/if}

<span class="ty-nowrap ty-stars">
    {if $link}
        {if $link_target === "url"}
            <a href="{$link|fn_url}">
        {else}
            <a class="cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion">
        {/if}
    {/if}

    {section name="full_star" loop=$stars.full}
        <i class="ty-stars__icon ty-icon-star"></i>
    {/section}

    {if $stars.part}
        <i class="ty-stars__icon ty-icon-star-half"></i>
    {/if}

    {section name="full_star" loop=$stars.empty}
        <i class="ty-stars__icon ty-icon-star-empty"></i>
    {/section}

    {if $link}
        </a>
    {/if}
</span>
