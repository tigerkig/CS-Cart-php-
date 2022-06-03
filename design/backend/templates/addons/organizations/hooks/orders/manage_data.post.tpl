<td>
    {if $o.organization}
        <a href="{"organizations.update?organization_id=`$o.organization->getorganizationId()`"|fn_url}">{$o.organization->getName()}</a>
    {/if}
</td>