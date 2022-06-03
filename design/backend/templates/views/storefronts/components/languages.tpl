{*
array  $id                 Storefront ID
array  $all_languages      All languages
*}

<div class="control-group">
    <label for="languages_{$id}"
           class="control-label"
    >
        {__("languages")}
    </label>
    <div class="controls" id="languages_{$id}">
        {foreach $all_languages as $language}
            {$language_storefront_ids = []}
            {if $language.storefront_ids}
                {$language_storefront_ids = explode(',', $language.storefront_ids)}
            {/if}
            {if
                $language_storefront_ids === []
                || in_array($id, $language_storefront_ids)
            }
                <p>{$language.name}</p>
            {/if}
        {/foreach}
        <p><a href="{fn_url("languages.manage")}" target="_blank">{__("storefronts.manage_language_availability")}</a></p>
    </div>
</div>
