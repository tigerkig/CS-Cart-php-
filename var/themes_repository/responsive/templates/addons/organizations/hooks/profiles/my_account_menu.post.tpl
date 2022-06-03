{if $user_info.is_organization_owner}
    <li class="ty-account-info__item ty-dropdown-box__item">
        <a href="{"organizations.update"|fn_url}" rel="nofollow">{__("organizations.organization_details")}</a>
    </li>
    <li class="ty-account-info__item ty-dropdown-box__item">
        <a href="{"organization_users.manage"|fn_url}" rel="nofollow">{__("organizations.manage_users")}</a>
    </li>
{/if}