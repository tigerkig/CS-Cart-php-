{capture name="mainbox"}
    {$c_dummy = "<i class=\"icon-dummy\"></i>"}
    {$c_icon  = "<i class=\"icon-`$search.sort_order_rev`\"></i>"}

    {if $sync_provider_list}
        <div class="table-responsive-wrapper">
            <table width="100%" class="table table-middle table--relative table-responsive">
                <thead>
                    <tr>
                        <th><a class="cm-ajax" href="{"`$c_url`&sort_by=name&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("name")}{if $search.sort_by == "name"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
                        <th>{__("last_sync")}</th>
                        <th>{__("status")}</th>
                        <th>{__("log_file")}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $sync_provider_list as $provider_id => $provider}
                        {include file="views/sync_data/components/sync_provider.tpl" last_sync_info=$last_sync_info.$provider_id}
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <p class="no-items">{__("no_items")}</p>
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("sync_data") content=$smarty.capture.mainbox show_all_storefront=false}
