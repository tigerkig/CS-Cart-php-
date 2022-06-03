
<table width="100%" class="table table-middle table--relative">
    <tr>
       <td>
            <label for="organization_customer" class="ty-control-group__title">{__("addons.rus_payments.organization_customer")}</label>
            <input type="text" name="payment_info[organization_customer]" id="organization_customer" {if $account_params.organization_customer}value="{$account_params.organization_customer}"{else}value=""{/if} size="20" />
        </td>

        <td colspan="2">
            <label for="inn_customer" class="ty-control-group__title">{__("inn_customer")}</label>
            <input type="text" name="payment_info[inn_customer]" id="inn_customer" {if $account_params.inn_customer}value="{$account_params.inn_customer}"{else}value=""{/if} size="20" maxlength="12" />
        </td>
    </tr>

    <tr>
       <td>
            <label for="address_customer" class="ty-control-group__title">{__("address")}</label>
            <input type="text" name="payment_info[address]" id="address_customer" {if $account_params.address}value="{$account_params.address}"{else}value=""{/if} size="20" />
        </td>

        <td>
            <label for="zip_postal_code" class="ty-control-group__title">{__("zip_postal_code")}</label>
            <input type="text" name="payment_info[zip_postal_code]" id="zip_postal_code" {if $account_params.zip_postal_code}value="{$account_params.zip_postal_code}"{else}value=""{/if} size="6" />
        </td>

        <td>
            <label for="phone_customer" class="ty-control-group__title cm-mask-phone-label">{__("phone")}</label>
            <input type="text" class="cm-mask-phone" name="payment_info[phone]" id="phone_customer" {if $account_params.phone}value="{$account_params.phone}"{else}value=""{/if} size="20" />
        </td>
    </tr>

    <tr>
       <td colspan="3">
            <label for="bank_details" class="ty-control-group__title">{__("addons.rus_payments.bank_details")}</label>
            <textarea id="bank_details" size="35"  cols="30" rows="5" name="payment_info[bank_details]" value="" class="ty-input-textarea cm-autocomplete-off">{if $account_params.bank_details}{$account_params.bank_details}{/if}</textarea>
        </td>
    </tr>
</table>
