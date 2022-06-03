<td class="row-status">
    {if $user.organization_user && $user.organization_user->getOrganization()}
        <a href="{"organizations.update?organization_id=`$user.organization_user->getOrganizationId()`"|fn_url}">{$user.organization_user->getOrganization()->getName()}</a>
        <p class="muted">
            <small>{__("organizations.user_role_{$user.organization_user->getRole()|lower}")}</small>
        </p>
    {else}
        {__("none")}
    {/if}
</td>
