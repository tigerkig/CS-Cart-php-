{strip}

{if $capture_image}
    {capture name="image"}
{/if}

{if !$obj_id}
    {math equation="rand()" assign="obj_id"}
{/if}

{$image_data = $images|fn_image_to_display:$image_width:$image_height}
{$generate_image = $image_data.generate_image && !$external}
{$show_no_image = $show_no_image|default:true}
{$image_additional_attrs = $image_additional_attrs|default:[]}
{$image_link_additional_attrs = $image_link_additional_attrs|default:[]}

{if $image_data}
    {$image_additional_attrs["alt"] = $image_data.alt}
    {$image_additional_attrs["title"] = $image_data.alt}
    {$image_link_additional_attrs["title"] = $images.detailed.alt}
{/if}

{hook name="common:image"}
{if $show_detailed_link}
    <a id="det_img_link_{$obj_id}" {if $image_data.detailed_image_path && $image_id}data-ca-image-id="{$image_id}"{/if} class="{$link_class} {if $image_data.detailed_image_path}cm-previewer ty-previewer{/if}" data-ca-image-width="{$images.detailed.image_x}" data-ca-image-height="{$images.detailed.image_y}" {if $image_data.detailed_image_path}href="{$image_data.detailed_image_path}" {$image_link_additional_attrs|render_tag_attrs nofilter}{/if}>
{/if}
{if $image_data.image_path}
    {** products:product_image_object is deprecated **}
    {hook name="products:product_image_object"}
        <img class="ty-pict {$valign} {$class} {if $lazy_load}lazyOwl{/if} {if $generate_image}ty-spinner{/if} cm-image" {if $obj_id && !$no_ids}id="det_img_{$obj_id}"{/if} {if $generate_image}data-ca-image-path="{$image_data.image_path}"{/if} {if $lazy_load}data-{/if}src="{if $generate_image}{$images_dir}/icons/spacer.gif{else}{$image_data.image_path}{/if}" {if $image_onclick}onclick="{$image_onclick}"{/if} {$image_additional_attrs|render_tag_attrs nofilter}/>

        {if $show_detailed_link}
            <svg class="ty-pict__container" aria-hidden="true" width="{$image_data.width}" height="{$image_data.height}" viewBox="0 0 {$image_data.width} {$image_data.height}" style="max-height: 100%; max-width: 100%; position: absolute; top: 0; left: 50%; transform: translateX(-50%); z-index: -1;">
                <rect fill="transparent" width="{$image_data.width}" height="{$image_data.height}"></rect>
            </svg>
        {/if}
    {/hook}
{elseif $show_no_image}
    <span class="ty-no-image" style="height: {$image_height|default:$image_width}px; width: {$image_width|default:$image_height}px; "><i class="ty-no-image__icon ty-icon-image" title="{__("no_image")}"></i></span>
{/if}
{if $show_detailed_link}
    {if $images.detailed_id}
        <span class="ty-previewer__icon hidden-phone"></span>
    {/if}
</a>
{/if}
{/hook}

{if $capture_image}
    {/capture}
    {capture name="icon_image_path"}
        {$image_data.image_path}
    {/capture}
    {capture name="detailed_image_path"}
        {$image_data.detailed_image_path}
    {/capture}
{/if}

{/strip}
