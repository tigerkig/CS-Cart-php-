{if $user_data.user_type === "UserTypes::CUSTOMER"|enum}
    <div class="control-group">
        <label class="control-label">{__("organizations.organization")}</label>
        <div class="controls">
            {include file="addons/organizations/views/organizations/components/picker/picker.tpl"
                item_ids=[$user_data.organization_id]
                show_empty_variant=true
                input_name="user_data[new_organization_id]"
            }
            {if $user_data.organization_user}
                <p class="muted">
                    <small>{__("organizations.user_role_{$user_data.organization_user->getRole()|lower}")}</small>
                </p>
            {/if}
        </div>
    </div>
{/if}
