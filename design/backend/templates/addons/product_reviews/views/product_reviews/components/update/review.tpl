{*
    $product_review                 array                               Product review
    $available_message_types        array                               Available message types
    $MESSAGE_CHARACTERS_THRESHOLD   int                                 Message characters threshold
    $is_allowed_update_reply        bool                                Is allowed update reply
*}

{$MESSAGE_CHARACTERS_THRESHOLD = 400}

<section class="cs-product-reviews-update-review">
    <section>
        <header>

            {* Stars *}
            <div class="control-group">
                <label class="control-label">
                    {__("product_reviews.rating")}:
                </label>
                <div class="controls">
                    {include file="addons/product_reviews/views/product_reviews/components/rating/stars.tpl"
                        rating=$product_review.rating_value
                        size="xlarge"
                    }
                </div>
            </div>

            {* Review date *}
            <div class="control-group">
                <label class="control-label">
                    {__("product_reviews.date")}:
                </label>
                <div class="controls">
                    <p>
                        {$product_review.product_review_timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                    </p>
                </div>
            </div>

            {* Helpfulness *}
            <div class="control-group">
                <label class="control-label">
                    {__("product_reviews.helpfulness")}:
                </label>
                <div class="controls">
                    <p>
                        {include file="addons/product_reviews/views/product_reviews/components/reviews/helpfulness.tpl"
                            helpfulness=$product_review.helpfulness
                        }
                    </p>
                </div>
            </div>

        </header>

        {* Message *}
        {foreach $available_message_types as $message_type}

            <div class="control-group">
                <label for="product_review_data_{$message_type}" class="control-label">
                    {__("product_reviews.$message_type")}:
                </label>
                <div class="controls">
                    <textarea name="product_review_data[{$message_type}]"
                        id="product_review_data_{$message_type}"
                        class="input-full cs-textarea-adaptive cs-textarea-adaptive--with-sidebar"
                        style="--text-length: {$product_review.message.$message_type|count_characters:true};"
                    >{$product_review.message.$message_type}</textarea>
                </div>
            </div>
        {/foreach}

        {* Review images *}
        {if $product_review.images}
            <div class="control-group">
                <label class="control-label">
                    {__("product_reviews.customer_photos")}:
                </label>
                <div class="controls">
                    {include file="addons/product_reviews/views/product_reviews/components/reviews/review_images.tpl"
                        product_review_images=$product_review.images
                        show_delete=$is_allowed_to_update_product_reviews && $smarty.const.ACCOUNT_TYPE === "admin"
                        size="large"
                    }
                </div>
            </div>
        {/if}

    </section>

    {* Vendor reply *}
    {include file="addons/product_reviews/views/product_reviews/components/update/post_vendor_reply.tpl"
        product_review_reply=$product_review.reply
        is_allowed_update_reply=$is_allowed_update_reply
    }

</section>
