{strip}

{assign var="image_data" value=$image|fn_image_to_display:$image_width:$image_height}
{$show_detailed_link = $show_detailed_link|default:true}

{if $show_detailed_link && ($image || $href)}
    <a class="{$link_css_class}" href="{$href|default:$image.image_path}" {if !$href}target="_blank"{/if}>
{/if}
{if $image_data.image_path}
    <img {if $image_id}id="image_{$image_id}"{/if} src="{$image_data.image_path}" width="{$image_data.width}" height="{$image_data.height}" alt="{$image_data.alt}" {if $image_data.generate_image}    class="spinner {$image_css_class}"    data-ca-image-path="{$image_data.image_path}"{else}    class="{$image_css_class}"{/if} title="{$image_data.alt}" />
{else}
    <div class="no-image {$no_image_css_class}" style="width: {$image_width|default:$image_height}px; height: {$image_height|default:$image_width}px;"><i class="glyph-image" title="{__("no_image")}"></i></div>
{/if}
{if $show_detailed_link && ($image || $href)}</a>{/if}

{/strip}