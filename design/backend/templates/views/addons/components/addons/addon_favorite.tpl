{if $a.is_favorite === "YesNo::YES"|enum}
    {$new_favorite_status = "YesNo::NO"|enum}
{else}
    {$new_favorite_status = "YesNo::YES"|enum}
{/if}
<form action="{"addons.set_favorite"|fn_url}"
    method="post"
    name="addons_set_favorite"
    class="form-edit form-horizontal cm-ajax form--no-margin"
    enctype="multipart/form-data"
>
    <input type="hidden" name="result_ids" value="{$result_ids}"/>
    <input type="hidden" name="addon" value="{$a.addon}"/>
    <input type="hidden" name="favorite" value="{$new_favorite_status}"/>
    <input type="hidden" name="detailed" value="{$detailed}"/>

    <button type="submit" class="btn btn-text btn-mini">
        <i class="icon-star-empty {if $a.is_favorite === "YesNo::YES"|enum}hidden{/if}"
            title="{__("add_addon_to_favorites")}"
        ></i>
        <i class="icon-star {if $a.is_favorite !== "YesNo::YES"|enum}hidden{/if}"
            title="{__("remove_addon_from_favorites")}"
        ></i>
    </button>

    {* Hiddent text for sort *}
    <span class="hidden">
        {if $a.is_favorite === "YesNo::YES"|enum}
            {__("favorites")}
        {/if}
    </span>
</form>