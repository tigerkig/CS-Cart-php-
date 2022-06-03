{*
    $product array Product data
*}

<a href="{"products.view?product_id=`$product.product_id`"|fn_url}">
    {include file="common/image.tpl" image=$product.main_pair.detailed
        image_width=$settings.Thumbnails.product_admin_mini_icon_width 
        image_height=$settings.Thumbnails.product_admin_mini_icon_height
        href="products.update?product_id=`$product.product_id`"|fn_url
    }
</a>
