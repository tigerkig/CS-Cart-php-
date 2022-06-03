{if $item_ids && !is_array($item_ids)}
    {$item_ids = "."|explode:$item_ids}
{/if}

{if $multiple}
    {$input_name = "{$input_name}[]"}
{/if}

{include file="addons/organizations/views/organizations/components/picker/picker.tpl"}