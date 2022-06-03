
{* @var \Tygh\Addons\Organizations\Organization[] $organizations *}
{* @var array $search *}

{capture name="mainbox"}
    <form action="{""|fn_url}" method="post" name="manage_organizations_form" class="form-horizontal form-edit cm-ajax" id="manage_organizations_form">
        <input type="hidden" name="result_ids" value="pagination_contents" />

        {include file="common/pagination.tpl" search=$search}
        <input type="hidden" name="redirect_url" value="{$config.current_url}">

        {$return_current_url = $config.current_url|escape:url}
        {$c_url = $config.current_url|fn_query_remove:"sort_by":"sort_order"}

        {if $organizations}
            {capture name="organizations_table"}
                <div class="table-responsive-wrapper longtap-selection">
                    <table class="table table-middle table-responsive">
                        <thead
                                data-ca-bulkedit-default-object="true"
                                data-ca-bulkedit-component="defaultObject"
                        >
                            <tr>
                                <th>
                                    {include file="common/check_items.tpl"}

                                    <input type="checkbox"
                                           class="bulkedit-toggler hide"
                                           data-ca-bulkedit-disable="[data-ca-bulkedit-default-object=true]"
                                           data-ca-bulkedit-enable="[data-ca-bulkedit-expanded-object=true]"
                                    />
                                </th>
                                <th>{__("name")}</th>
                                <th>{__("owner")}</th>
                                <th></th>
                                <th>{__("status")}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $organizations as $organization}
                            <tr class="cm-row-item cm-row-status-{$organization->getStatus()|lower} cm-longtap-target"
                                 data-ca-longtap-action="setCheckBox"
                                 data-ca-longtap-target="input.cm-item"
                                 data-ca-id="{$organization->getOrganizationId()}"
                            >
                                <td>
                                    <a href="{"organizations.update?organization_id={$organization->getOrganizationId()}"|fn_url}">
                                        {$organization->getOrganizationId()}
                                    </a>
                                    <input type="checkbox" name="organization_ids[]" value="{$organization->getOrganizationId()}" class="cm-item cm-item-status-{$organization->getStatus()|lower} hide" />
                                </td>
                                <td>
                                    <a href="{"organizations.update?organization_id={$organization->getOrganizationId()}"|fn_url}">
                                        {$organization->getName()}
                                    </a>
                                </td>
                                <td>
                                    {if $organization->getOwnerUser()}
                                        <a href="{"profiles.update?user_id={$organization->getOwnerUser()->getUserId()}"|fn_url}">
                                            {$organization->getOwnerUser()->getName()}
                                        </a>
                                    {/if}
                                </td>
                                <td class="right nowrap">
                                    {capture name="tools_list"}
                                        <li>{btn type="list" text=__("edit") href="organizations.update?organization_id={$organization->getOrganizationId()}"}</li>
                                        <li>{btn type="list" text=__("delete") class="cm-confirm" href="organizations.delete?organization_id={$organization->getOrganizationId()}&redirect_url={$return_current_url}" method="POST"}</li>
                                    {/capture}
                                    <div class="hidden-tools">
                                        {dropdown content=$smarty.capture.tools_list}
                                    </div>
                                </td>
                                <td>
                                    {include file="common/select_popup.tpl"
                                        id=$organization->getOrganizationId()
                                        status=$organization->getStatus()
                                        hidden=false
                                        notify=false
                                        update_controller="organizations"
                                        popup_additional_class="dropleft"
                                    }
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {/capture}

            {include file="common/context_menu_wrapper.tpl"
                form="manage_organizations_form"
                object="organizations"
                items=$smarty.capture.organizations_table
            }
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
        {include file="common/pagination.tpl" search=$search}
    </form>
{/capture}

{capture name="sidebar"}
    {include file="addons/organizations/views/organizations/components/organizations_search_forms.tpl" dispatch="organizations.manage"}
{/capture}

{capture name="adv_buttons"}
    {hook name="organizations:manage_tools"}
        {include file="common/tools.tpl" tool_href="organizations.add" prefix="top" title=__("organizations.new_organization") hide_tools=true icon="icon-plus"}
    {/hook}
{/capture}

{include file="common/mainbox.tpl"
    title=__("organizations")
    content=$smarty.capture.mainbox
    content_id="manage_organizations"
    sidebar=$smarty.capture.sidebar
    adv_buttons=$smarty.capture.adv_buttons
}
