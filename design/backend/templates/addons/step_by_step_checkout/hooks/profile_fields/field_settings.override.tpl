{if $has_billing_and_shipping_email_profile_field}
    {if $section === "ProfileFieldSections::CONTACT_INFORMATION"|enum
        && $field.profile_type === "ProfileTypes::CODE_USER"|enum
        && in_array($field.field_name, ["email", "phone"])
    }
        <input type="hidden" name="fields_data[{$field.field_id}][{$_show}]" value="N"/>
        <input type="checkbox" name="fields_data[{$field.field_id}][{$_show}]" value="Y" {if $field.$_show == "Y"}checked="checked"{/if} id="sw_req_{$area}_{$field.field_id}" class="cm-skipp-check-checkbox"/>
        <input type="hidden" name="fields_data[{$field.field_id}][{$_required}]" value="N"/>
        <span id="req_{$area}_{$field.field_id}">
            <input type="checkbox" name="fields_data[{$field.field_id}][{$_required}]" value="Y" {if $field.$_required == "Y"}checked="checked"{/if} {if $field.$_show == "N"}disabled="disabled"{/if} class="cm-skipp-check-checkbox"/>
        </span>
    {/if}

    {if $field.field_name == "email"
        && $field.profile_type == "ProfileTypes::CODE_USER"|enum
        && in_array($section, ["ProfileFieldSections::BILLING_ADDRESS"|enum, "ProfileFieldSections::SHIPPING_ADDRESS"|enum])
    }
        <input type="hidden" name="fields_data[{$field.field_id}][{$_show}]" value="N"/>
        <input type="radio" name="fields_data[email][{$_show}]" value="{$field.field_id}" {if $field.$_show == "Y"}checked="checked"{/if} id="sw_req_{$area}_{$field.field_id}" />
        <input type="hidden" name="fields_data[{$field.field_id}][{$_required}]" value="Y"/>
        <span id="req_{$area}_{$field.field_id}">
            <input type="checkbox" name="fields_data[{$field.field_id}][{$_required}]" value="Y" {if $field.$_required == "Y"}checked="checked"{/if} disabled="disabled"/>
        </span>

        {if $area === "ProfileFieldAreas::CHECKOUT"|enum}
            {include file="common/tooltip.tpl" tooltip={__("step_by_step_checkout.tooltip.email_can_not_be_disabled")}}
        {/if}
    {/if}
{else}
    {if $area === "ProfileFieldAreas::CHECKOUT"|enum
        && $section === "ProfileFieldSections::CONTACT_INFORMATION"|enum
        && $field.profile_type === "ProfileTypes::CODE_USER"|enum
        && $field.field_name === "email"
    }
        <input type="hidden" name="fields_data[{$field.field_id}][{$_show}]" value="Y"/>
        <input type="checkbox" name="fields_data[{$field.field_id}][{$_show}]" value="Y" {if $field.$_show == "Y"}checked="checked" disabled="disabled"{/if} class="cm-skipp-check-checkbox"/>

        <input type="hidden" name="fields_data[{$field.field_id}][{$_required}]" value="Y"/>
        <span>
            <input type="checkbox" name="fields_data[{$field.field_id}][{$_required}]" value="Y" {if $field.$_required == "Y"}checked="checked"  disabled="disabled"{/if} class="cm-skipp-check-checkbox"/>
        </span>
        {include file="common/tooltip.tpl" tooltip={__("step_by_step_checkout.tooltip.email_can_not_be_disabled")}}
    {/if}
{/if}