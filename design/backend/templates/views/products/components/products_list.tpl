<div id="add_product">
{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="rev" value="pagination_`$smarty.request.data_id`"|default:"pagination_contents"}

{assign var="c_icon" value="<i class=\"icon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"icon-dummy\"></i>"}

{script src="js/tygh/exceptions.js"}

{* add-new *}
{if $products}
<input type="hidden" id="add_product_id" name="product_id" value=""/>
<div class="table-responsive-wrapper">
    <table width="100%" class="table table--relative table-responsive">
    <thead>
    <tr>
        {hook name="product_list:table_head"}
        {if $hide_amount}
            <th class="center" width="1%">
                {if $show_radio}&nbsp;{else}{include file="common/check_items.tpl"}{/if}
            </th>
        {/if}
        <th width="80%"><a class="cm-ajax" href="{"`$c_url`&sort_by=product&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("product_name")}{if $search.sort_by == "product"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        {if $show_price}
            <th class="right" width="10%"><a class="cm-ajax" href="{"`$c_url`&sort_by=price&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("price")}{if $search.sort_by == "price"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        {/if}
        {if !$hide_amount}
            <th class="center" width="5%">{__("quantity")}</th>
        {/if}
        {if $is_order_management}
            <th class="center" width="5%"></th>
        {/if}
        {/hook}
    </tr>
    </thead>
    {if !$checkbox_name}{assign var="checkbox_name" value="add_products_ids"}{/if}
    {foreach from=$products item=product}
    <tr id="picker_product_row_{$product.product_id}">
        {hook name="product_list:table_content"}
        {if $hide_amount}
            <td class="center" width="1%" data-th=""><input type="{if $show_radio}radio{else}checkbox{/if}" name="{$checkbox_name}[]" value="{$product.product_id}" class="cm-item mrg-check" id="checkbox_id_{$product.product_id}" /></td>
        {/if}
        <td data-th="{__("product_name")}">
            {hook name="product_list:product_data"}
            <input type="hidden" id="product_{$product.product_id}" value="{$product.product}" />

            {if $hide_amount}
                <label for="checkbox_id_{$product.product_id}">{$product.product nofilter}</label>
            {else}
                <div>{$product.product nofilter}</div>
            {/if}
            <div class="product-list__labels">
                {hook name="products:product_additional_info"}
                    {if $product.product_code}
                        <div class="product-code">
                            <span class="product-code__label">{$product.product_code}</span>
                        </div>
                    {/if}
                {/hook}
                {include file="views/companies/components/company_name.tpl" object=$product show_hidden_input=true}
            </div>


            {if !$hide_options}
                {include file="views/products/components/select_product_options.tpl" id=$product.product_id product_options=$product.product_options name="product_data" show_aoc=$show_aoc additional_class=$additional_class}
            {/if}
            {/hook}
        </td>
        {if $show_price}
            <td class="cm-picker-product-options right" data-th="{__("price")}">{if !$product.price|floatval && $product.zero_price_action == "A"}<input class="input-medium" id="product_price_{$product.product_id}" type="text" size="3" name="product_data[{$product.product_id}][price]" value="" />{else}{include file="common/price.tpl" value=$product.price}{/if}</td>
        {/if}
        {if !$hide_amount}
            <td class="center nowrap cm-value-changer" width="5%">
                <div class="input-prepend input-append">
                    <a class="btn no-underline strong increase-font cm-decrease"><i class="icon-minus"></i></a>
                    <input id="product_id_{$product.product_id}" type="text" value="{$default_product_amount|default:"0"}" name="product_data[{$product.product_id}][amount]" size="3" class="input-micro cm-amount"{if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} />
                    <a class="btn no-underline strong increase-font cm-increase"><i class="icon-plus"></i></a>
                </div>
            </td>
        {/if}
        {if $is_order_management}
            <td class="center nowrap" width="5%">
                <div>
                    <a class="btn cm-process-items cm-submit cm-ajax cm-add-product" id="{$product.product_id}" title="{__("add_product")}" data-ca-dispatch="dispatch[order_management.add]" data-ca-check-filter="#picker_product_row_{$product.product_id}" data-ca-target-form="add_products"><i class="icon-share-alt" data-ca-check-filter="#picker_product_row_{$product.product_id}"></i></a>
                </div>
            </td>
        {/if}
        {/hook}

        {hook name="product_list:table_columns"}{/hook}
    </tr>
    {/foreach}
    </table>
</div>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

<script>
(function(_, $) {
    function _switchAOC(id, disable, $row)
    {
        var aoc = $row.find('#sw_option_' + id + '_AOC');
        if (aoc.length) {
            aoc.addClass('cm-skip-avail-switch');
            aoc.prop('disabled', disable);
            disable = aoc.prop('checked') ? true : disable;
        }

        $row.find('.cm-picker-product-options').switchAvailability(disable, false);
    }

    function init(context)
    {
        if (context.find('tr[id^=picker_product_row_]').length) {
            if (!$('.cm-add-product').length) {
                context.find('.cm-picker-product-options').switchAvailability(true, false);
            } else {
                context.find('.cm-picker-product-options').switchAvailability(false, false);
            }
        }
    }

    $(document).ready(function() {
        init($(_.doc));

        $.ceEvent('on', 'ce.commoninit', function(context) {
            init(context);
        });

        $(_.doc).on('click', '.cm-increase,.cm-decrease', function() {
            var inp = $('input', $(this).closest('.cm-value-changer'));
            var new_val = parseInt(inp.val()) + ($(this).is('a.cm-increase') ? 1 : -1);
            var disable = new_val > 0 ? false : true;
            var _id = inp.prop('id').replace('product_id_', '');

            _switchAOC(_id, disable, $(this).closest('tr'));
        });

        $.ceEvent('on', 'ce.formajaxpost_add_products', function(response, params) {
            if ($('.cm-add-product').length && response.current_url) {
                var url = response.current_url;

                $.ceAjax('request', url, {
                    method: 'get',
                    result_ids: 'button_trash_products,om_ajax_update_totals,om_ajax_update_payment,om_ajax_update_shipping',
                    full_render: true
                });
            }
        });

        $(_.doc).on('click', '.cm-add-product', function() {
            if ($(this).prop('id')) {
                var _id = $(this).prop('id');
                $('#add_product_id').val(_id);
            }
        });

        $(_.doc).on('change', '.cm-amount', function() {
            var new_val = parseInt($(this).val());
            var disable = new_val > 0 ? false : true;
            var _id = $(this).prop('id').replace('product_id_', '');

            _switchAOC(_id, disable, $(this).closest('tr'));
        });

        $(_.doc).on('click', '.cm-item', function() {
            var disable = (this.checked) ? false : true;
            var _id = $(this).prop('id').replace('checkbox_id_', '');

            _switchAOC(_id, disable, $(this).closest('tr'));
        });

        $(_.doc).on('click', '.cm-check-items', function() {
            var form = $(this).parents('form:first');
            var _checked = this.checked;
            $('.cm-item', form).each(function () {
                if (_checked && !this.checked || !_checked && this.checked) {
                    $(this).click();
                }
            });
        });
    });
}(Tygh, Tygh.$));
</script>

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}
