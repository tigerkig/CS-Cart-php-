{** block-description:product_reviews.title **}
{*
    $product
*}

{include file="addons/product_reviews/views/product_reviews/view.tpl"
    product_reviews=$product.product_reviews
    product_id=$product.product_id
    title=__("product_reviews.title")
    quicklink="product_review_link"
    container_id="content_product_reviews_block"
    locate_to_product_review_tab=true
}
