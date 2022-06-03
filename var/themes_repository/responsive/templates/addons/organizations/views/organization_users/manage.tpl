{include file="common/pagination.tpl"}

<div class="ty-organization-users__actions">
    <div class="ty-organization-users__actions-right">
        {include file="buttons/button.tpl" but_meta="ty-btn__text" but_role="text" but_text=__("add") but_href="organization_users.add" but_icon="ty-icon-plus"}
    </div>
    <div class="ty-clear-both"></div>
</div>

<table class="ty-table ty-organization-user-search">
    <thead>
    <tr>
        <th>{__("person_name")}</th>
        <th>{__("email")}</th>
        <th>{__("registered")}</th>
        <th>{__("organizations.last_sign_in")}</th>
        <th>{__("status")}</th>
    </tr>
    </thead>
    {foreach $users as $user}
        <tr>
            <td class="ty-organization-users-search__item">
                <a href="{"organization_users.update?user_id={$user.user_id}"|fn_url}">
                    {if $user.lastname || $user.firstname}
                        {$user.lastname} {$user.firstname}
                    {else}
                        {$user.email}
                    {/if}
                </a>
                {if $user.organization_user}
                    <p class="ty-muted">
                        <small>{__("organizations.user_role_{$user.organization_user->getRole()|lower}")}</small>
                    </p>
                {/if}
            </td>
            <td class="ty-organization-users-search__item">
                <a href="mailto:{$user.email|escape:url}">{$user.email}</a>
            </td>
            <td class="ty-organization-users-search__item">
                {$user.timestamp|date_format:"{$settings.Appearance.date_format}, {$settings.Appearance.time_format}"}
            </td>
            <td class="ty-organization-users-search__item">
                {if $user.last_login}
                    {$user.last_login|date_format:"{$settings.Appearance.date_format}, {$settings.Appearance.time_format}"}
                {else}
                    -
                {/if}
            </td>
            <td class="ty-organization-users-search__item">
                {if $user.status === "ObjectStatuses::ACTIVE"|enum}
                    {__("active")}
                {else}
                    {__("disabled")}
                {/if}
            </td>
        </tr>
        {foreachelse}
        <tr class="ty-table__no-items">
            <td colspan="5"><p class="ty-no-items">{__("no_users_found")}</p></td>
        </tr>
    {/foreach}
</table>

{include file="common/pagination.tpl"}

{capture name="mainbox_title"}{__("users")}{/capture}