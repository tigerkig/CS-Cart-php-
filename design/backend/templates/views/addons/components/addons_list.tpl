{if $addons_list}
    <div class="table-responsive-wrapper" id="addon_table">
        <table class="table table-addons table-middle cm-filter-table ty-table--sorter
            table-responsive table-responsive-w-titles"
            data-ca-sortable="true"
            data-ca-sort-list="[[1, 0]]"
            data-ca-input-id="elm_addon"
            data-ca-clear-id="elm_addon_clear"
            data-ca-empty-id="elm_addon_no_items"
            data-ca-filter-table-is-logical-and="true"
        >
            <thead>
                <tr>
                    <th width="1%" class="left mobile-hide">
                    </th>
                    <th class="cm-tablesorter" data-ca-sortable-column="true" width="3%">
                        <i class="icon-star-empty"
                            title="{__("favorites")}"
                        ></i>
                    </th>
                    <th class="sorter-false" width="8%"></th>
                    <th class="cm-tablesorter" data-ca-sortable-column="true" width="40%">{__("name")}</th>
                    <th class="cm-tablesorter" data-ca-sortable-column="true" width="3%" title="{__("installed_date")}">
                        <i class="icon-calendar muted"></i>
                    </th>
                    <th class="cm-tablesorter" data-ca-sortable-column="true" width="18%">{__("developer")}</th>
                    <th class="sorter-false" width="7%"></th>
                    <th class="cm-tablesorter right" data-ca-sortable-column="true" width="20%">{__("status")}</th>
                </tr>
            </thead>
        {foreach $addons_list as $key => $a}

            {* Get full add-on info *}
            {include file="views/addons/components/addons/addon_full_info.tpl"
                addon=$a
            }

            {$addon_filter_source_suffix = ($a.is_core_addon) ? "built_in" : "third_party"}

            <tr class="cm-row-status-{$a.status|lower} cm-row-item
                filter_status_{$a.status} filter_source_{$addon_filter_source_suffix} {$additional_class}"
                id="addon_{$key}"
                data-supplier="{$a.supplier}"
                data-category="{$a.category}"
            >
                <td class="left mobile-hide">
                </td>
                <td>
                    {include file="views/addons/components/addons/addon_favorite.tpl"
                        result_ids="addon_table"
                        detailed=false
                    }
                </td>
                <td>
                    {include file="views/addons/components/addons/addon_icon.tpl"
                        addon=$a
                        href=true
                        show_description=true
                    }
                </td>
                <td>
                    {include file="views/addons/components/manage/addon_description.tpl"}
                </td>
                <td>
                    {include file="views/addons/components/manage/addon_install_datetime.tpl"}
                </td>
                <td>
                    {include file="views/addons/components/addons/addon_supplier.tpl"}
                </td>
                <td class="nowrap">
                    {include file="views/addons/components/addons/addon_status.tpl"}
                </td>
                <td class="nowrap right">
                    {include file="views/addons/components/addons/addon_actions.tpl"}
                </td>
            <!--addon_{$key}--></tr>
        {/foreach}
        </table>
    <!--addon_table--></div>
{else}
    <p id="elm_addon_no_items" class="no-items {if $addon_list}hidden{/if}">{__("no_data")}</p>
{/if}
