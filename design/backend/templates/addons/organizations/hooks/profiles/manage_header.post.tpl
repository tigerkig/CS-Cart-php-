<th>
    <a class="cm-ajax" href="{"`$c_url`&sort_by=organization&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>
        {__("organizations.organization")}{if $search.sort_by == "organization"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}
    </a>
</th>
