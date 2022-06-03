{if $show_organization_fields}
    {include file="common/subheader.tpl" title=__("organizations.organization")}

    {include file="addons/organizations/components/organizations_fields.tpl"
        base_input_name="user_data[organization]"
        organization_profile_fields=$organization_profile_fields
        organization_data=$user_data.organization
    }
{/if}