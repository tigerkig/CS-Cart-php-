<div class="control-group">
    <label for="elm_name" class="cm-required control-label">{__("warehouses.store_type")}:</label>
    <div class="controls">
        <select name="store_location_data[store_type]">
            {$store_type = $store_location.store_type|default:$smarty.request.store_type}
            {foreach $store_types as $type_code => $type_name}
                <option value="{$type_code}"{if $type_code == $store_type} selected{/if}>{$type_name}</option>
            {/foreach}
        </select>
    </div>
</div>

