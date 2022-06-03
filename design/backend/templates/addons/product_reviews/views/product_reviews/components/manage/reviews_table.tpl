{*
    $product_reviews                array                               Product reviews
    $product_review                 array                               Product review
    $show_product                   bool                                Show product
    $product_reviews_search         array                               Product reviews search
    $sorting_status_types           array                               Sorting status types
    $sort_order_rev                 string                              Sort order rev
    $c_url                          string                              Current URL
    $rev                            string                              Rev
    $sorting_status_icons           array                               Sorting status icons
*}

{$show_product = $show_product|default:true}
{$sort_order_rev = $product_reviews_search.sort_order_rev}
{$c_url=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{$rev=$smarty.request.content_id|default:"pagination_product_reviews"}
{foreach $sorting_status_types as $sorting_status_type}
    {$sorting_status_icons.$sorting_status_type = ($product_reviews_search.sort_by === $sorting_status_type)
        ? "<i class=\"icon-`$sort_order_rev`\"></i>"
        : "<i class=\"icon-dummy\"></i>"
    }
{/foreach}

<form action="{""|fn_url}" method="post" name="product_reviews_form" id="product_reviews_form">

{include file="common/pagination.tpl"
    save_current_page=true
    save_current_url=true
    div_id=$rev
    search=$product_reviews_search
}

    {if $product_reviews}
        {capture name="product_reviews_table"}
            <table width="100%" class="table table-middle table--relative table-responsive longtap-selection">
                <thead
                        data-ca-bulkedit-default-object="true"
                        data-ca-bulkedit-component="defaultObject"
                >
                    <tr>
                        <th width="6%">
                            <input type="checkbox"
                                   class="bulkedit-toggler hide"
                                   data-ca-bulkedit-disable="[data-ca-bulkedit-default-object=true]"
                                   data-ca-bulkedit-enable="[data-ca-bulkedit-expanded-object=true]"
                            />
                        </th>
                        {if $show_product}
                            <th width="10%"></th>
                        {/if}
                        <th>
                            {__("id")}
                            / <a class="cm-ajax"
                                href="{"`$c_url`&sort_by=rating_value&sort_order=`$sort_order_rev`"|fn_url}"
                                data-ca-target-id={$rev}
                            >
                                {__("product_reviews.rating")}
                                {$sorting_status_icons.rating_value nofilter}
                            </a>
                            / {__("message")}
                            {if $show_product}
                                / {__("product")}
                            {/if}
                            / {__("customer")}
                        </th>
                        <th width="13%">
                            <a class="cm-ajax"
                                href="{"`$c_url`&sort_by=helpfulness&sort_order=`$sort_order_rev`"|fn_url}"
                                data-ca-target-id={$rev}
                            >
                                {__("product_reviews.helpfulness")}
                                {$sorting_status_icons.helpfulness nofilter}
                            </a>
                        </th>
                        <th width="10%">
                            {__("status")}
                        </th>
                        <th width="9%" class="mobile-hide">&nbsp;</th>
                        <th width="15%">
                            <a class="cm-ajax"
                                href="{"`$c_url`&sort_by=product_review_timestamp&sort_order=`$sort_order_rev`"|fn_url}"
                                data-ca-target-id={$rev}
                            >
                                {__("date")}
                                {$sorting_status_icons.product_review_timestamp nofilter}
                            </a>
                        </th>
                    </tr>
                </thead>
                {foreach $product_reviews as $product_review}
                    {include file="addons/product_reviews/views/product_reviews/components/manage/review_row.tpl"
                        product_review=$product_review
                        show_product=$show_product
                        rev=$rev
                    }
                {/foreach}
            </table>
        {/capture}

        {include file="common/context_menu_wrapper.tpl"
            form='product_reviews_form'
            object="product_reviews"
            items=$smarty.capture.product_reviews_table
        }
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

{include file="common/pagination.tpl"
    save_current_page=true
    save_current_url=true
    div_id=$rev
    search=$product_reviews_search
}
</form>
