{if $oi.returns_info}
    {if !$return_statuses}{assign var="return_statuses" value=$smarty.const.STATUSES_RETURN|fn_get_simple_statuses}{/if}

    <p class="shift-top">
        <i title="{__("expand_sublist_of_items")}" id="on_ret_{$key}" class="hand cm-combination icon-caret-right"></i>
        <i title="{__("collapse_sublist_of_items")}" id="off_ret_{$key}" class="hand hidden cm-combination icon-caret-down"></i>
        <a id="sw_ret_{$key}" class="cm-combination">{__("returns_info")}</a>
    </p>
    <div class="table-responsive-wrapper">
        <table width="100%" class="table table-condensed table-no-bg table--relative table-responsive hidden" id="ret_{$key}">
        <thead>
        <tr>
            <th>&nbsp;{__("status")}</th>
            <th>{__("quantity")}</th>
        </tr>
        </thead>
        <tbody>
            {foreach from=$oi.returns_info item="amount" key="status" name="f_rinfo"}
            <tr>
                <td data-th="{__("status")}">{$return_statuses.$status|default:""}</td>
                <td data-th="{__("quantity")}">{$amount}</td>
            </tr>
            {/foreach}
        </tbody>
        </table>
    </div>
{/if}
