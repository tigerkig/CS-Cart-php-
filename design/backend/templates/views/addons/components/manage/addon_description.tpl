{$href = "addons.update?addon={$a.addon}"|fn_url}
{if fn_allowed_for("MULTIVENDOR") && $selected_storefront_id}
    {$href = $href|fn_link_attach:"storefront_id={$selected_storefront_id}"}
{/if}

{* Get addon license required text *}
{include file="views/addons/components/addons/addon_license_required.tpl"}

<div class="addons-addon-description">
    <div>
        <a href="{$href}"
           class="row-status addons-addon-description__name addons-addon-description__name--{$a.status|lower} nowrap-responsive"
           title="{$addon_full_description}"
        >
            {$a.short_name}
        </a>

        {if $a.recently_installed}
            <i class="icon-circle
                addons-addon-description__new-addon addons-addon-description__new-addon--{$a.status|lower}"
                title="{__("new_addon")}"
            ></i>
        {/if}
    </div>
    <div class="addons-addon-description__description">
        <small class="muted addons-addon-description__description-small">
            {$a.description|strip_tags}
        </small>
    </div>
    <div>
        <small class="muted" title="{$addon_full_version_info}">
            {$a.version|default:0.1}
        </small>
        <small class="muted">•</small>
        {if $a.install_datetime}
            <small class="muted" title="{$install_datetime_full_info}">
                {$a.install_datetime|date_format:"`$settings.Appearance.date_format`"}
            </small>
        {else}
            <small class="muted">—</small>
        {/if}

    </div>

    {* Hidden text for search *}
    <div class="hidden">
        {if $a.is_long_name}
            {$a.name nofilter}
        {/if}
        {$a.addon nofilter}
    </div>
</div>