{$base_input_name = $base_input_name|default:"organization_data"}

<div class="ty-control-group">
    <label for="elm_organization_name" class="ty-control-group__title  cm-required">{__("name")}:</label>
    <input type="text" id="elm_organization_name" name="{$base_input_name}[name]" class="ty-input-text" value="{$organization_data.name}"/>
</div>
<div class="ty-control-group">
    <label for="elm_organization_description" class="ty-control-group__title cm-required">{__("description")}:</label>
    <textarea id="elm_organization_description" name="{$base_input_name}[description]" class="ty-input-text">{$organization_data.description}</textarea>
</div>

{include file="views/profiles/components/profile_fields.tpl"
    section="{"ProfileFieldSections::CONTACT_INFORMATION"|enum}"
    default_data_name=$base_input_name
    nothing_extra=true
    profile_data=$organization_data
    profile_fields=$organization_profile_fields
}

{capture name="form_shipping_address"}
    {hook name="organizations:form_shipping_address"}
    {if $profile_fields["ProfileFieldSections::SHIPPING_ADDRESS"|enum]}
        {include file="views/profiles/components/profile_fields.tpl"
            section="{"ProfileFieldSections::SHIPPING_ADDRESS"|enum}"
            default_data_name=$base_input_name
            nothing_extra=true
            profile_data=$organization_data
            profile_fields=$organization_profile_fields
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
            default_data_name=$base_input_name
            nothing_extra=true
            profile_data=$organization_data
            profile_fields=$organization_profile_fields
        }
    {/if}
    {/hook}
{/capture}

{if $smarty.capture.form_billing_address|trim}
    {include file="common/subheader.tpl" title=__("billing_address")}
    {$smarty.capture.form_billing_address nofilter}
{/if}