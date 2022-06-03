{if $addons.google_analytics.track_ecommerce === "YesNo::YES"|enum}
    <script>
        {foreach $orders_info as $gtag_order_info}
            {$product_items = []}

            {foreach $gtag_order_info.products as $product}
                {$product_items[] = [
                    "item_id"       => $product.product_code,
                    "item_name"     => $product.product,
                    "item_category" => $product.ga_category_name,
                    "price"         => $product.price,
                    "quantity"      => $product.amount
                ]}
            {/foreach}

            {$order_info = [
                "transaction_id" => $gtag_order_info.order_id,
                "affiliation"    => $gtag_order_info.ga_company_name,
                "value"          => $gtag_order_info.total,
                "currency"       => $gtag_order_info.secondary_currency,
                "tax"            => $gtag_order_info.tax_subtotal,
                "shipping"       => $gtag_order_info.shipping_cost,
                "items"          => $product_items
            ]}

            var order_info = {$order_info|json_encode nofilter};

            if (typeof(gtag) !== 'undefined') {
                // Sending a purchase event with the products in the transaction
                gtag('event', 'purchase', order_info);
            }
        {/foreach}
    </script>
{/if}
