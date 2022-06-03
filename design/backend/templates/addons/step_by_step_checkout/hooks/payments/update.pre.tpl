<div class="control-group" data-ca-form-group="category">
    <label class="control-label" for="elm_payment_category_{$id}">{__("payment_category")}:</label>
    <div class="controls">
        <select id="elm_payment_category_{$id}" name="payment_data[payment_category]">
            <option value="tab1" {if $payment.payment_category == "tab1"}selected="selected"{/if}>{__("payments_tab1")}</option>
            <option value="tab2" {if $payment.payment_category == "tab2"}selected="selected"{/if}>{__("payments_tab2")}</option>
            <option value="tab3" {if $payment.payment_category == "tab3"}selected="selected"{/if}>{__("payments_tab3")}</option>
        </select>
        <p class="description">
            <small>{__("payment_category_note")}</small>
        </p>
    </div>
</div>