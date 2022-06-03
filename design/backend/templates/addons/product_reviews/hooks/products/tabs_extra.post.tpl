{*
    $product_data                       array                           Product data
    $product_reviews_rating_stats       array                           Product reviews rating stats
    $product_review                     array                           Product review
    $is_allowed_to_view_product_reviews bool                            Is allowed to view product reviews
*}

{if $is_allowed_to_view_product_reviews}

    <div class="{if $selected_section !== "product_reviews"}hidden{/if}" id="content_product_reviews">
        {include file="addons/product_reviews/views/product_reviews/components/rating/product_rating_overview.tpl"
            ratings_stats=$product_reviews_rating_stats.ratings
            total_product_reviews=$product_reviews_rating_stats.total
            average_rating=$product_data.average_rating
        }

        {include file="addons/product_reviews/views/product_reviews/components/manage/reviews_table.tpl"
            product_reviews=$product_reviews
            object_company_id=$product_data.company_id
            show_product=false
        }
    </div>
{/if}

