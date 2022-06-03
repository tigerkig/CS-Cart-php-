{if $user_data.user_id && $user_data.user_id != $auth.user_id}
    <div class="ty-organization-users__actions">
        <div class="ty-organization-users__actions-right">
            {include file="buttons/button.tpl" but_meta="ty-btn__text cm-confirm cm-post" but_role="text" but_text=__("delete") but_href="organization_users.delete?user_id={$user_data.user_id}" but_icon="ty-icon-trashcan"}
        </div>
        <div class="ty-clear-both"></div>
    </div>
{/if}

<div class="ty-profile-field ty-account form-wrap">
    <form name="profile_form" enctype="multipart/form-data" action="{""|fn_url}" method="post">
        <input type="hidden" name="user_id" value="{$user_data.user_id}" />
        <input type="hidden" name="profile_id" value="{$user_data.profile_id}" />

        <div class="ty-control-group">
            <label for="elem_user_email" class="ty-control-group__title cm-required cm-email cm-trim">{__("email")}</label>
            <input type="text" id="elem_user_email" name="user_data[email]" size="32" maxlength="128" value="{$user_data.email}" class="ty-input-text cm-focus" />
        </div>

        {include file="views/profiles/components/profile_fields.tpl" section="C" title=__("contact_information")}

        {if $user_data.user_id && $user_data.user_id != $auth.user_id}
            <div class="ty-control-group">
                <label for="elem_user_status" class="ty-control-group__title cm-required cm-trim">{__("status")}</label>
                <select name="user_data[status]" id="elem_user_status" class="ty-input-text">
                    <option value="{"ObjectStatuses::ACTIVE"|enum}" {if $user_data.status == "ObjectStatuses::ACTIVE"|enum || empty($user_data.status)}selected{/if}>{__("active")}</option>
                    <option value="{"ObjectStatuses::DISABLED"|enum}" {if $user_data.status && $user_data.status != "ObjectStatuses::ACTIVE"|enum}selected{/if}>{__("disabled")}</option>
                </select>
            </div>
        {/if}

        <div class="ty-profile-field__buttons buttons-container">
            {include file="buttons/save.tpl" but_name="dispatch[organization_users.update]" but_meta="ty-btn__secondary" but_id="save_profile_but"}
        </div>
    </form>
</div>
{if $user_data.user_id}
    {capture name="mainbox_title"}{__("editing_profile")}{/capture}
{else}
    {capture name="mainbox_title"}{__("new_user_profile")}{/capture}
{/if}
