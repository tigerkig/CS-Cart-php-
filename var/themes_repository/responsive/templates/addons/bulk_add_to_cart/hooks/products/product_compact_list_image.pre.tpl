{if $settings.Checkout.allow_anonymous_shopping === "allow_shopping" || $auth.user_id}
    <div class="ty-compact-list__check">
        <input class="ty-checkbox__input"
            id="bulk_{$obj_id}"
            value="{$product.product_id}"
            type="checkbox"
            data-ca-bulk-add-to-cart-name="product_data[{$product.product_id}][product_id]"
            data-ca-bulk-add-to-cart="checkbox"
        />
        <label class="ty-checkbox__label ty-checkbox__label--sr-only" for="bulk_{$obj_id}">
            {__("select_product")}
        </label>
        <input class="ty-checkbox__input"
            value="1"
            type="hidden"
            data-ca-bulk-add-to-cart-name="product_data[{$product.product_id}][amount]"
            data-ca-bulk-add-to-cart="input"
        />
    </div>
{/if}