<div class="table-responsive-wrapper">
    <table width="100%" class="table table-middle table--relative table-responsive">
        <thead>
            <tr>
                <th>{__("storefront")}</th>
                <th>{__("usergroup")}</th>
                <th>{__("file")}</th>
                <th>{__("customer_price_list.updated_at")}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $list as $item}
                <tr>
                    <td>{$item.storefront}</td>
                    <td>{$item.usergroup}</td>
                    <td>
                        {if $item.file}
                            <a href="{"customer_price_list.get?storefront_id={$item.storefront_id}&usergroup_id={$item.usergroup_id}"|fn_url}" target="_blank">{$item.file}</a>
                        {else}
                            {__("customer_price_list.not_created_yet")}
                        {/if}
                    </td>
                    <td>
                        {if $item.updated_at}
                            {$item.updated_at|date_format:"{$settings.Appearance.date_format}, {$settings.Appearance.time_format}"}
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>