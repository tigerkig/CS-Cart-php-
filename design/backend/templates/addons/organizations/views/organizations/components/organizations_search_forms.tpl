
<div class="sidebar-row">
    <h6>{__("search")}</h6>
    <form name="organization_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">
        {if $smarty.request.redirect_url}
            <input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
        {/if}

        {capture name="simple_search"}
        <div class="sidebar-field">
            <label for="elm_name">{__("name")}</label>
            <div class="break">
                <input type="text" name="conditions[search]" id="elm_name" value="{$search.conditions.search}" />
            </div>
        </div>
        {$user_info = []}
        {if $search.conditions.owner}
            {$user_info = $search.conditions.owner|fn_get_user_short_info}
        {/if}
        <div class="sidebar-field">
            <label for="elm_owner">{__("owner")}</label>
            <div class="break">
                 {include file="pickers/users/picker.tpl"
                    display="radio"
                    but_meta="btn"
                    extra_url=$extra_url
                    view_mode="single_button"
                    data_id="owner"
                    input_name="conditions[owner]"
                    user_info=$user_info
                }
            </div>
        </div>

        <div class="sidebar-field">
            <label for="elm_status">{__("status")}</label>
            <div class="break">
                <select name="conditions[status]" id="elm_status">
                    <option value="">{__("all")}</option>
                    {foreach $statuses as $status_code => $status}
                        <option value="{$status_code}" {if $search.conditions.status == $status_code}selected="selected"{/if}>{$status}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {/capture}
        {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search dispatch=$dispatch view_type="organizations" no_adv_link=true}
    </form>
</div>