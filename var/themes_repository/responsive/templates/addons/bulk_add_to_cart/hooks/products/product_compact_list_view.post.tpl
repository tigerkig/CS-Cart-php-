{if $settings.Checkout.allow_anonymous_shopping === "allow_shopping" || $auth.user_id}
    {script src="js/addons/bulk_add_to_cart/categories.js"}

    <div class="ty-bulk-add-to-cart__panel"
        data-ca-bulk-add-to-cart="panel"
        data-ca-bulk-add-to-cart-show-class="ty-bulk-add-to-cart__panel--show">
        <button
            class="ty-btn ty-btn__primary"
            type="button"
            data-ca-bulk-add-to-cart="button"
            data-ca-bulk-add-to-cart-url="{"checkout.add"|fn_url}"
            data-ca-bulk-add-to-cart-notification-products-simple="true">
                {__("bulk_add_to_cart.add_selected_products_to_cart",
                    ["[attr]" => "data-ca-bulk-add-to-cart='textBtnPanel'",
                    "[number]" => ""])
                }
        </button>
    </div>
{/if}