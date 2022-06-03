{literal}
    ${data.image && data.image.image_path
    ? `<img src="${data.image.image_path}" width="${data.image.width}" height="${data.image.height}" alt="${data.image.alt}" class="object-picker__products-image"/>`
        : `<div class="no-image object-picker__products-image object-picker__products-image--no-image" style="width: ${data.image_width}px; height: ${data.image_height}px;"> <i class="glyph-image"></i></div>`
    }
{/literal}
<div class="object-picker__products-main">
    <div class="object-picker__products-name">
        {if $type == "result"}
            <div class="object-picker__name-content">{$title_pre} {literal}${data.product}{/literal} {$title_post}</div>
        {else}
            <a href="{literal}${data.url}{/literal}" class="object-picker__products-name-content object-picker__products-name-content--link">{$title_pre} {literal}${data.product}{/literal} {$title_post}</a>
        {/if}
    </div>
    <div class="object-picker__products-labels">
        {hook name="products:object_picker_products_additional_info"}
            <div class="object-picker__products-product-code">
                <span class="object-picker__products-product-code-label">{literal}${data.product_code}{/literal}</span>
            </div>
        {/hook}
    </div>
</div>
<div class="object-picker__products-price">
    {literal}${data.price_formatted}{/literal}
</div>