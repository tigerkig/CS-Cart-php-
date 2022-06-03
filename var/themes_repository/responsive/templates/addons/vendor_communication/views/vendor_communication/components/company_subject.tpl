{*
    $company array Company data
*}

<a href="{"companies.products?company_id=`$company.logos.theme.company_id`"|fn_url}" title="{$company.company}">
    {$company.company|truncate:60:"...":true}
</a>
