{* @var \Tygh\Addons\Organizations\Organization $organization *}

{if $organization}
    {$id = $organization->getOrganizationId()}
{else}
    {$id = 0}
{/if}

{capture name="mainbox"}
<form action="{""|fn_url}" method="post" id="organization_form" class="form-horizontal form-edit">

    {if $organization->getOrganizationId()}
        <input type="hidden" name="organization_id" value="{$organization->getOrganizationId()}"/>
    {/if}

    {hook name="organizations:form_general_content"}
        {include file="common/subheader.tpl" title=__("general")}
        <div class="control-group">
            <label class="control-label cm-required cm-trim" for="elm_title">{__("name")}:</label>
            <div class="controls">
                <input type="text" name="organization_data[name]" id="elm_title" size="30"
                    value="{$organization->getName()}" class="input-large"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label cm-required" for="elm_owner">{__("owner")}:</label>
            <div class="controls">
                {include file="pickers/users/picker.tpl"
                    display="radio"
                    but_meta="btn"
                    view_mode="single_button"
                    data_id="elm_owner"
                    input_name="organization_data[owner]"
                    item_ids=$organization->getOwnerUser()->getUserId()
                    user_name=$organization->getOwnerUser()->getName()
                    user_info=[]
                }
            </div>
        </div>

        {include file="common/select_status.tpl" input_name="organization_data[status]" id="organization_data_{$id}" obj=$organization->toArray()}

        <div class="control-group">
            <label class="control-label cm-trim" for="elm_desciption">{__("description")}:</label>
            <div class="controls">
                <textarea id="elm_desciption" name="organization_data[description]" cols="55" rows="8"
                    class="input-textarea-long span10">{$organization->getDescription() nofilter}</textarea>
            </div>
        </div>

        {include file="views/profiles/components/profile_fields.tpl"
            section="{"ProfileFieldSections::CONTACT_INFORMATION"|enum}"
            default_data_name="organization_data"
            nothing_extra=true
            profile_data=$organization->toArray()
        }
    {/hook}

    {capture name="form_shipping_address"}
        {hook name="organizations:form_shipping_address"}
        {if $profile_fields["ProfileFieldSections::SHIPPING_ADDRESS"|enum]}
            {include file="views/profiles/components/profile_fields.tpl"
                section="{"ProfileFieldSections::SHIPPING_ADDRESS"|enum}"
                default_data_name="organization_data"
                nothing_extra=true
                profile_data=$organization->toArray()
            }
        {/if}
        {/hook}
    {/capture}

    {if $smarty.capture.form_shipping_address|trim}
        {include file="common/subheader.tpl" title=__("shipping_address")}
        {$smarty.capture.form_shipping_address nofilter}
    {/if}

    {capture name="form_billing_address"}
        {hook name="organizations:form_billing_address"}
        {if $profile_fields["ProfileFieldSections::BILLING_ADDRESS"|enum]}
            {include file="views/profiles/components/profile_fields.tpl"
                section="{"ProfileFieldSections::BILLING_ADDRESS"|enum}"
                default_data_name="organization_data"
                nothing_extra=true
                profile_data=$organization->toArray()
            }
        {/if}
        {/hook}
    {/capture}

    {if $smarty.capture.form_billing_address|trim}
        {include file="common/subheader.tpl" title=__("billing_address")}
        {$smarty.capture.form_billing_address nofilter}
    {/if}
</form>
{/capture}

{capture name="buttons"}
    {if $organization->getOrganizationId()}
        {include file="buttons/save_changes.tpl" but_meta="dropdown-toggle" but_role="submit-link" but_name="dispatch[organizations.update]" but_target_form="organization_form" save=$organization->getOrganizationId()}
    {else}
        {include file="buttons/button.tpl" but_text=__("create") but_meta="dropdown-toggle" but_role="submit-link" but_name="dispatch[organizations.update]" but_target_form="organization_form"}
    {/if}
{/capture}

{if $organization->getOrganizationId()}
    {assign var="title" value=__("organizations.edit_organization", ["[name]" => $organization->getName()])}
{else}
    {assign var="title" value=__("organizations.new_organization")}
{/if}

{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

