{literal}
    ${data.image && data.image.image_path
    ? `<img src="${data.image.image_path}" width="30" height="30" alt="${data.image.alt}" class="object-picker__products-image"/>`
        : `<div class="no-image object-picker__products-image object-picker__products-image--no-image" style="width: 30px; height: 30px;"> <i class="glyph-image"></i></div>`
    }
{/literal}
<div class="object-picker__products-main">
    <div class="object-picker__products-name">
        <div class="object-picker__products-name-content">{$title_pre} {literal}${data.product}{/literal} {$title_post}</div>
    </div>
</div>